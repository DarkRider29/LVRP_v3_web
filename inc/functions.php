<?php
function secu($var)
{
	return mysql_real_escape_string(htmlspecialchars($var));
}
/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */


//
// Return current timestamp (with microseconds) as a float
//

function get_microtime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

//
// Cookie stuff!
//
include 'sampquery.class.php';
function check_cookie(&$pun_user)
{
	global $db, $db_type, $pun_config, $cookie_name, $cookie_seed;

	$now = time();

	// If the cookie is set and it matches the correct pattern, then read the values from it
	if (isset($_COOKIE[$cookie_name]) && preg_match('%^(\d+)\|([0-9a-fA-F]+)\|(\d+)\|([0-9a-fA-F]+)$%', $_COOKIE[$cookie_name], $matches))
	{
		$cookie = array(
			'user_id'			=> intval($matches[1]),
			'password_hash' 	=> $matches[2],
			'expiration_time'	=> intval($matches[3]),
			'cookie_hash'		=> $matches[4],
		);
	}

	// If it has a non-guest user, and hasn't expired
	if (isset($cookie) && $cookie['user_id'] > 1 && $cookie['expiration_time'] > $now)
	{
		// If the cookie has been tampered with
		if (forum_hmac($cookie['user_id'].'|'.$cookie['expiration_time'], $cookie_seed.'_cookie_hash') != $cookie['cookie_hash'])
		{
			$expire = $now + 31536000; // The cookie expires after a year
			pun_setcookie(1, pun_hash(uniqid(rand(), true)), $expire);
			set_default_user();

			return;
		}

		// Check if there's a user with the user ID and password hash from the cookie
		$result = $db->query('SELECT u.*, g.*, o.logged, o.idle FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE u.id='.intval($cookie['user_id'])) or error('Unable to fetch user information', __FILE__, __LINE__, $db->error());
		$pun_user = $db->fetch_assoc($result);
		$_SESSION['Login']=$pun_user['username'];

		// If user authorisation failed
		if (!isset($pun_user['id']) || forum_hmac($pun_user['password'], $cookie_seed.'_password_hash') !== $cookie['password_hash'])
		{
			$expire = $now + 31536000; // The cookie expires after a year
			pun_setcookie(1, pun_hash(uniqid(rand(), true)), $expire);
			set_default_user();

			return;
		}

		// Send a new, updated cookie with a new expiration timestamp
		$expire = ($cookie['expiration_time'] > $now + $pun_config['o_timeout_visit']) ? $now + 1209600 : $now + $pun_config['o_timeout_visit'];
		pun_setcookie($pun_user['id'], $pun_user['password'], $expire);

		// Set a default language if the user selected language no longer exists
		if (!file_exists(PUN_ROOT.'lang/'.$pun_user['language']))
			$pun_user['language'] = $pun_config['o_default_lang'];

		// Set a default style if the user selected style no longer exists
		if (!file_exists(PUN_ROOT.'style/'.$pun_user['style'].'.css'))
			$pun_user['style'] = $pun_config['o_default_style'];

		if (!$pun_user['disp_topics'])
			$pun_user['disp_topics'] = $pun_config['o_disp_topics_default'];
		if (!$pun_user['disp_posts'])
			$pun_user['disp_posts'] = $pun_config['o_disp_posts_default'];

		// Define this if you want this visit to affect the online list and the users last visit data
		if (!defined('PUN_QUIET_VISIT'))
		{
			// Update the online list
			if (!$pun_user['logged'])
			{
				$pun_user['logged'] = $now;

				// With MySQL/MySQLi/SQLite, REPLACE INTO avoids a user having two rows in the online table
				switch ($db_type)
				{
					case 'mysql':
					case 'mysqli':
					case 'mysql_innodb':
					case 'mysqli_innodb':
					case 'sqlite':
						$db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES('.$pun_user['id'].', \''.$db->escape($pun_user['username']).'\', '.$pun_user['logged'].')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
						break;

					default:
						$db->query('INSERT INTO '.$db->prefix.'online (user_id, ident, logged) SELECT '.$pun_user['id'].', \''.$db->escape($pun_user['username']).'\', '.$pun_user['logged'].' WHERE NOT EXISTS (SELECT 1 FROM '.$db->prefix.'online WHERE user_id='.$pun_user['id'].')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
						break;
				}

				// Reset tracked topics
				set_tracked_topics(null);
			}
			else
			{
				// Special case: We've timed out, but no other user has browsed the forums since we timed out
				if ($pun_user['logged'] < ($now-$pun_config['o_timeout_visit']))
				{
					$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$pun_user['logged'].' WHERE id='.$pun_user['id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
					$pun_user['last_visit'] = $pun_user['logged'];
				}

				$idle_sql = ($pun_user['idle'] == '1') ? ', idle=0' : '';
				$db->query('UPDATE '.$db->prefix.'online SET logged='.$now.$idle_sql.' WHERE user_id='.$pun_user['id']) or error('Unable to update online list', __FILE__, __LINE__, $db->error());

				// Update tracked topics with the current expire time
				if (isset($_COOKIE[$cookie_name.'_track']))
					forum_setcookie($cookie_name.'_track', $_COOKIE[$cookie_name.'_track'], $now + $pun_config['o_timeout_visit']);
			}
		}
		else
		{
			if (!$pun_user['logged'])
				$pun_user['logged'] = $pun_user['last_visit'];
		}

		$pun_user['is_guest'] = false;
		$pun_user['is_admmod'] = $pun_user['g_id'] == PUN_ADMIN || $pun_user['g_id'] == '5' || $pun_user['g_moderator'] == '1';
	}
	else
		set_default_user();
}


//
// Converts the CDATA end sequence ]]> into ]]&gt;
//
function escape_cdata($str)
{
	return str_replace(']]>', ']]&gt;', $str);
}


//
// Authenticates the provided username and password against the user database
// $user can be either a user ID (integer) or a username (string)
// $password can be either a plaintext password or a password hash including salt ($password_is_hash must be set accordingly)
//
function authenticate_user($user, $password, $password_is_hash = false)
{
	global $db, $pun_user;

	// Check if there's a user matching $user and $password
	$result = $db->query('SELECT u.*, g.*, o.logged, o.idle FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON o.user_id=u.id WHERE '.(is_int($user) ? 'u.id='.intval($user) : 'u.username=\''.$db->escape($user).'\'')) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	$pun_user = $db->fetch_assoc($result);

	if (!isset($pun_user['id']) ||
		($password_is_hash && $password != $pun_user['password']) ||
		(!$password_is_hash && pun_hash($password) != $pun_user['password']))
		set_default_user();
	else
		$pun_user['is_guest'] = false;
}


//
// Try to determine the current URL
//
function get_current_url($max_length = 0)
{
	$protocol = get_current_protocol();
	$port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':'.$_SERVER['SERVER_PORT'] : '';

	$url = urldecode($protocol.'://'.$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI']);

	if (strlen($url) <= $max_length || $max_length == 0)
		return $url;

	// We can't find a short enough url
	return null;
}


//
// Fetch the current protocol in use - http or https
//
function get_current_protocol()
{
	$protocol = 'http';

	// Check if the server is claiming to using HTTPS
	if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
		$protocol = 'https';

	// If we are behind a reverse proxy try to decide which protocol it is using
	if (defined('FORUM_BEHIND_REVERSE_PROXY'))
	{
		// Check if we are behind a Microsoft based reverse proxy
		if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) != 'off')
			$protocol = 'https';

		// Check if we're behind a "proper" reverse proxy, and what protocol it's using
		if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
			$protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
	}

	return $protocol;
}

//
// Fetch the base_url, optionally support HTTPS and HTTP
//
function get_base_url($support_https = false)
{
	global $pun_config;
	static $base_url;

	if (!$support_https)
		return $pun_config['o_base_url'];

	if (!isset($base_url))
	{
		// Make sure we are using the correct protocol
		$base_url = str_replace(array('http://', 'https://'), get_current_protocol().'://', $pun_config['o_base_url']);
	}

	return $base_url;
}


//
// Fill $pun_user with default values (for guests)
//
function set_default_user()
{
	global $db, $db_type, $pun_user, $pun_config;

	$remote_addr = get_remote_address();

	// Fetch guest user
	$result = $db->query('SELECT u.*, g.*, o.logged, o.last_post, o.last_search FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON u.group_id=g.g_id LEFT JOIN '.$db->prefix.'online AS o ON o.ident=\''.$remote_addr.'\' WHERE u.id=1') or error('Unable to fetch guest information', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		exit('Unable to fetch guest information. Your database must contain both a guest user and a guest user group.');

	$pun_user = $db->fetch_assoc($result);

	// Update online list
	if (!$pun_user['logged'])
	{
		$pun_user['logged'] = time();

		// With MySQL/MySQLi/SQLite, REPLACE INTO avoids a user having two rows in the online table
		switch ($db_type)
		{
			case 'mysql':
			case 'mysqli':
			case 'mysql_innodb':
			case 'mysqli_innodb':
			case 'sqlite':
				$db->query('REPLACE INTO '.$db->prefix.'online (user_id, ident, logged) VALUES(1, \''.$db->escape($remote_addr).'\', '.$pun_user['logged'].')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
				break;

			default:
				$db->query('INSERT INTO '.$db->prefix.'online (user_id, ident, logged) SELECT 1, \''.$db->escape($remote_addr).'\', '.$pun_user['logged'].' WHERE NOT EXISTS (SELECT 1 FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($remote_addr).'\')') or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
				break;
		}
	}
	else
		$db->query('UPDATE '.$db->prefix.'online SET logged='.time().' WHERE ident=\''.$db->escape($remote_addr).'\'') or error('Unable to update online list', __FILE__, __LINE__, $db->error());

	$pun_user['disp_topics'] = $pun_config['o_disp_topics_default'];
	$pun_user['disp_posts'] = $pun_config['o_disp_posts_default'];
	$pun_user['timezone'] = $pun_config['o_default_timezone'];
	$pun_user['dst'] = $pun_config['o_default_dst'];
	$pun_user['language'] = $pun_config['o_default_lang'];
	$pun_user['style'] = $pun_config['o_default_style'];
	$pun_user['is_guest'] = true;
	$pun_user['is_admmod'] = false;
}


//
// SHA1 HMAC with PHP 4 fallback
//
function forum_hmac($data, $key, $raw_output = false)
{
	if (function_exists('hash_hmac'))
		return hash_hmac('sha1', $data, $key, $raw_output);

	// If key size more than blocksize then we hash it once
	if (strlen($key) > 64)
		$key = pack('H*', sha1($key)); // we have to use raw output here to match the standard

	// Ensure we're padded to exactly one block boundary
	$key = str_pad($key, 64, chr(0x00));

	$hmac_opad = str_repeat(chr(0x5C), 64);
	$hmac_ipad = str_repeat(chr(0x36), 64);

	// Do inner and outer padding
	for ($i = 0;$i < 64;$i++) {
		$hmac_opad[$i] = $hmac_opad[$i] ^ $key[$i];
		$hmac_ipad[$i] = $hmac_ipad[$i] ^ $key[$i];
	}

	// Finally, calculate the HMAC
	$hash = sha1($hmac_opad.pack('H*', sha1($hmac_ipad.$data)));

	// If we want raw output then we need to pack the final result
	if ($raw_output)
		$hash = pack('H*', $hash);

	return $hash;
}


//
// Set a cookie, FluxBB style!
// Wrapper for forum_setcookie
//
function pun_setcookie($user_id, $password_hash, $expire)
{
	global $cookie_name, $cookie_seed;

	forum_setcookie($cookie_name, $user_id.'|'.forum_hmac($password_hash, $cookie_seed.'_password_hash').'|'.$expire.'|'.forum_hmac($user_id.'|'.$expire, $cookie_seed.'_cookie_hash'), $expire);
}


//
// Set a cookie, FluxBB style!
//
function forum_setcookie($name, $value, $expire)
{
	global $cookie_path, $cookie_domain, $cookie_secure;

	// Enable sending of a P3P header
	header('P3P: CP="CUR ADM"');

	if (version_compare(PHP_VERSION, '5.2.0', '>='))
		setcookie($name, $value, $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
	else
		setcookie($name, $value, $expire, $cookie_path.'; HttpOnly', $cookie_domain, $cookie_secure);
}


//
// Check whether the connecting user is banned (and delete any expired bans while we're at it)
//
function check_bans()
{
	global $db, $pun_config, $lang_common, $pun_user, $pun_bans;

	// Admins and moderators aren't affected
	if ($pun_user['is_admmod'] || !$pun_bans)
		return;

	// Add a dot or a colon (depending on IPv4/IPv6) at the end of the IP address to prevent banned address
	// 192.168.0.5 from matching e.g. 192.168.0.50
	$user_ip = get_remote_address();
	$user_ip .= (strpos($user_ip, '.') !== false) ? '.' : ':';

	$bans_altered = false;
	$is_banned = false;

	foreach ($pun_bans as $cur_ban)
	{
		// Has this ban expired?
		if ($cur_ban['expire'] != '' && $cur_ban['expire'] <= time())
		{
			$db->query('DELETE FROM '.$db->prefix.'bans WHERE id='.$cur_ban['id']) or error('Unable to delete expired ban', __FILE__, __LINE__, $db->error());
			$bans_altered = true;
			continue;
		}

		if ($cur_ban['username'] != '' && utf8_strtolower($pun_user['username']) == utf8_strtolower($cur_ban['username']))
			$is_banned = true;

		if ($cur_ban['ip'] != '')
		{
			$cur_ban_ips = explode(' ', $cur_ban['ip']);

			$num_ips = count($cur_ban_ips);
			for ($i = 0; $i < $num_ips; ++$i)
			{
				// Add the proper ending to the ban
				if (strpos($user_ip, '.') !== false)
					$cur_ban_ips[$i] = $cur_ban_ips[$i].'.';
				else
					$cur_ban_ips[$i] = $cur_ban_ips[$i].':';

				if (substr($user_ip, 0, strlen($cur_ban_ips[$i])) == $cur_ban_ips[$i])
				{
					$is_banned = true;
					break;
				}
			}
		}

		if ($is_banned)
		{
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($pun_user['username']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			message($lang_common['Ban message'].' '.(($cur_ban['expire'] != '') ? $lang_common['Ban message 2'].' '.strtolower(format_time($cur_ban['expire'], true)).'. ' : '').(($cur_ban['message'] != '') ? $lang_common['Ban message 3'].'<br /><br /><strong>'.pun_htmlspecialchars($cur_ban['message']).'</strong><br /><br />' : '<br /><br />').$lang_common['Ban message 4'].' <a href="mailto:'.$pun_config['o_admin_email'].'">'.$pun_config['o_admin_email'].'</a>.', true);
		}
	}

	// If we removed any expired bans during our run-through, we need to regenerate the bans cache
	if ($bans_altered)
	{
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';

		generate_bans_cache();
	}
}


//
// Check username
//
function check_username($username, $exclude_id = null)
{
	global $db, $pun_config, $errors, $lang_prof_reg, $lang_register, $lang_common, $pun_bans;

	// Convert multiple whitespace characters into one (to prevent people from registering with indistinguishable usernames)
	$username = preg_replace('%\s+%s', ' ', $username);

	// Validate username
	if (pun_strlen($username) < 10)
		$errors[] = $lang_prof_reg['Username too short'];
	else if (pun_strlen($username) > 25) // This usually doesn't happen since the form element only accepts 25 characters
		$errors[] = $lang_prof_reg['Username too long'];
	else if (!strcasecmp($username, 'Guest') || !strcasecmp($username, $lang_common['Guest']))
		$errors[] = $lang_prof_reg['Username guest'];
	else if (preg_match('%[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}%', $username) || preg_match('%((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))%', $username))
		$errors[] = $lang_prof_reg['Username IP'];
	else if (!check_Name($username))
		$errors[] = $lang_prof_reg['Username reserved chars'];
	else if (preg_match('%(?:\[/?(?:b|u|s|ins|del|em|i|h|colou?r|quote|code|img|url|email|list|\*|topic|post|forum|user)\]|\[(?:img|url|quote|list)=)%i', $username))
		$errors[] = $lang_prof_reg['Username BBCode'];
	/* FluxToolBar */
	if (file_exists(FORUM_CACHE_DIR.'cache_fluxtoolbar_tag_check.php'))
		include FORUM_CACHE_DIR.'cache_fluxtoolbar_tag_check.php';
	else
	{
		require_once PUN_ROOT.'include/cache_fluxtoolbar.php';
		generate_ftb_cache('tags');
		require FORUM_CACHE_DIR.'cache_fluxtoolbar_tag_check.php';
	}

	// Check username for any censored words
	if ($pun_config['o_censoring'] == '1' && censor_words($username) != $username)
		$errors[] = $lang_register['Username censor'];

	// Check that the username (or a too similar username) is not already registered
	$query = ($exclude_id) ? ' AND id!='.$exclude_id : '';

	$result = $db->query('SELECT username FROM '.$db->prefix.'users WHERE (UPPER(username)=UPPER(\''.$db->escape($username).'\') OR UPPER(username)=UPPER(\''.$db->escape(ucp_preg_replace('%[^\p{L}\p{N}]%u', '', $username)).'\')) AND id>1'.$query) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

	if ($db->num_rows($result))
	{
		$busy = $db->result($result);
		$errors[] = $lang_register['Username dupe 1'].' '.pun_htmlspecialchars($busy).'. '.$lang_register['Username dupe 2'];
	}

	// Check username for any banned usernames
	foreach ($pun_bans as $cur_ban)
	{
		if ($cur_ban['username'] != '' && utf8_strtolower($username) == utf8_strtolower($cur_ban['username']))
		{
			$errors[] = $lang_prof_reg['Banned username'];
			break;
		}
	}
}


//
// Update "Users online"
//
function update_users_online()
{
	global $db, $pun_config;

	$now = time();

	// Fetch all online list entries that are older than "o_timeout_online"
	$result = $db->query('SELECT user_id, ident, logged, idle FROM '.$db->prefix.'online WHERE logged<'.($now-$pun_config['o_timeout_online'])) or error('Unable to fetch old entries from online list', __FILE__, __LINE__, $db->error());
	while ($cur_user = $db->fetch_assoc($result))
	{
		// If the entry is a guest, delete it
		if ($cur_user['user_id'] == '1')
			$db->query('DELETE FROM '.$db->prefix.'online WHERE ident=\''.$db->escape($cur_user['ident']).'\'') or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
		else
		{
			// If the entry is older than "o_timeout_visit", update last_visit for the user in question, then delete him/her from the online list
			if ($cur_user['logged'] < ($now-$pun_config['o_timeout_visit']))
			{
				$db->query('UPDATE '.$db->prefix.'users SET last_visit='.$cur_user['logged'].' WHERE id='.$cur_user['user_id']) or error('Unable to update user visit data', __FILE__, __LINE__, $db->error());
				$db->query('DELETE FROM '.$db->prefix.'online WHERE user_id='.$cur_user['user_id']) or error('Unable to delete from online list', __FILE__, __LINE__, $db->error());
			}
			else if ($cur_user['idle'] == '0')
				$db->query('UPDATE '.$db->prefix.'online SET idle=1 WHERE user_id='.$cur_user['user_id']) or error('Unable to insert into online list', __FILE__, __LINE__, $db->error());
		}
	}
}


//
// Display the profile navigation menu
//
function generate_profile_menu($page = '')
{
	global $lang_profile, $pun_config, $pun_user, $id;

?>
<div id="profile" class="block2col">
	<div class="blockmenu">
		<h2><span><?php echo $lang_profile['Profile menu'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
					<li<?php if ($page == 'essentials') echo ' class="isactive"'; ?>><a href="profile.php?section=essentials&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section essentials'] ?></a></li>
					<li<?php if ($page == 'personal') echo ' class="isactive"'; ?>><a href="profile.php?section=personal&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personal'] ?></a></li>
					<li<?php if ($page == 'messaging') echo ' class="isactive"'; ?>><a href="profile.php?section=messaging&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section messaging'] ?></a></li>
<?php if ($pun_config['o_avatars'] == '1' || $pun_config['o_signatures'] == '1'): ?>					<li<?php if ($page == 'personality') echo ' class="isactive"'; ?>><a href="profile.php?section=personality&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section personality'] ?></a></li>
<?php endif; ?>					<li<?php if ($page == 'display') echo ' class="isactive"'; ?>><a href="profile.php?section=display&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section display'] ?></a></li>
					<li<?php if ($page == 'privacy') echo ' class="isactive"'; ?>><a href="profile.php?section=privacy&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section privacy'] ?></a></li>
<?php if ($pun_user['g_id'] == PUN_ADMIN || $pun_user['g_id'] == '5' || ($pun_user['g_moderator'] == '1' && $pun_user['g_mod_ban_users'] == '1')): ?>					<li<?php if ($page == 'admin') echo ' class="isactive"'; ?>><a href="profile.php?section=admin&amp;id=<?php echo $id ?>"><?php echo $lang_profile['Section admin'] ?></a></li>
<?php endif; ?>				</ul>
			</div>
		</div>
	</div>
<?php

}


//
// Outputs markup to display a user's avatar
//
function generate_avatar_markup($user_id)
{
	global $pun_config;

	$filetypes = array('jpg', 'gif', 'png');
	$avatar_markup = '';

	foreach ($filetypes as $cur_type)
	{
		$path = $pun_config['o_avatars_dir'].'/'.$user_id.'.'.$cur_type;

		if (file_exists(PUN_ROOT.$path) && $img_size = getimagesize(PUN_ROOT.$path))
		{
			$avatar_markup = '<img src="'.pun_htmlspecialchars(get_base_url(true).'/'.$path.'?m='.filemtime(PUN_ROOT.$path)).'" '.$img_size[3].' alt="" />';
			break;
		}
		else if ($pun_config['o_avatars_default'] != 0)
		{
			$path = $pun_config['o_avatars_dir'].'/0.'.$cur_type;
			if (file_exists(PUN_ROOT.$path) && $img_size = getimagesize(PUN_ROOT.$path))
			{
				$avatar_markup = '<img src="'.pun_htmlspecialchars(get_base_url(true).'/'.$path.'?m='.filemtime(PUN_ROOT.$path)).'" '.$img_size[3].' alt="" />';
				break;
			}
		}
	}

	return $avatar_markup;
}


//
// Generate browser's title
//
function generate_page_title($page_title, $p = null)
{
	global $pun_config, $lang_common;

	$page_title = array_reverse($page_title);

	if ($p != null)
		$page_title[0] .= ' ('.sprintf($lang_common['Page'], forum_number_format($p)).')';

	$crumbs = implode($lang_common['Title separator'], $page_title);

	return $crumbs;
}


//
// Save array of tracked topics in cookie
//
function set_tracked_topics($tracked_topics)
{
	global $cookie_name, $cookie_path, $cookie_domain, $cookie_secure, $pun_config;

	$cookie_data = '';
	if (!empty($tracked_topics))
	{
		// Sort the arrays (latest read first)
		arsort($tracked_topics['topics'], SORT_NUMERIC);
		arsort($tracked_topics['forums'], SORT_NUMERIC);

		// Homebrew serialization (to avoid having to run unserialize() on cookie data)
		foreach ($tracked_topics['topics'] as $id => $timestamp)
			$cookie_data .= 't'.$id.'='.$timestamp.';';
		foreach ($tracked_topics['forums'] as $id => $timestamp)
			$cookie_data .= 'f'.$id.'='.$timestamp.';';

		// Enforce a byte size limit (4096 minus some space for the cookie name - defaults to 4048)
		if (strlen($cookie_data) > FORUM_MAX_COOKIE_SIZE)
		{
			$cookie_data = substr($cookie_data, 0, FORUM_MAX_COOKIE_SIZE);
			$cookie_data = substr($cookie_data, 0, strrpos($cookie_data, ';')).';';
		}
	}

	forum_setcookie($cookie_name.'_track', $cookie_data, time() + $pun_config['o_timeout_visit']);
	$_COOKIE[$cookie_name.'_track'] = $cookie_data; // Set it directly in $_COOKIE as well
}


//
// Extract array of tracked topics from cookie
//
function get_tracked_topics()
{
	global $cookie_name;

	$cookie_data = isset($_COOKIE[$cookie_name.'_track']) ? $_COOKIE[$cookie_name.'_track'] : false;
	if (!$cookie_data)
		return array('topics' => array(), 'forums' => array());

	if (strlen($cookie_data) > 4048)
		return array('topics' => array(), 'forums' => array());

	// Unserialize data from cookie
	$tracked_topics = array('topics' => array(), 'forums' => array());
	$temp = explode(';', $cookie_data);
	foreach ($temp as $t)
	{
		$type = substr($t, 0, 1) == 'f' ? 'forums' : 'topics';
		$id = intval(substr($t, 1));
		$timestamp = intval(substr($t, strpos($t, '=') + 1));
		if ($id > 0 && $timestamp > 0)
			$tracked_topics[$type][$id] = $timestamp;
	}

	return $tracked_topics;
}


//
// Update posts, topics, last_post, last_post_id and last_poster for a forum
//
function update_forum($forum_id)
{
	global $db;

	$result = $db->query('SELECT COUNT(id), SUM(num_replies) FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id) or error('Unable to fetch forum topic count', __FILE__, __LINE__, $db->error());
	list($num_topics, $num_posts) = $db->fetch_row($result);

	$num_posts = $num_posts + $num_topics; // $num_posts is only the sum of all replies (we have to add the topic posts)

	$result = $db->query('SELECT last_post, last_post_id, last_poster FROM '.$db->prefix.'topics WHERE forum_id='.$forum_id.' AND moved_to IS NULL ORDER BY last_post DESC LIMIT 1') or error('Unable to fetch last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result)) // There are topics in the forum
	{
		list($last_post, $last_post_id, $last_poster) = $db->fetch_row($result);

		$db->query('UPDATE '.$db->prefix.'forums SET num_topics='.$num_topics.', num_posts='.$num_posts.', last_post='.$last_post.', last_post_id='.$last_post_id.', last_poster=\''.$db->escape($last_poster).'\' WHERE id='.$forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
	}
	else // There are no topics
		$db->query('UPDATE '.$db->prefix.'forums SET num_topics='.$num_topics.', num_posts='.$num_posts.', last_post=NULL, last_post_id=NULL, last_poster=NULL WHERE id='.$forum_id) or error('Unable to update last_post/last_post_id/last_poster', __FILE__, __LINE__, $db->error());
}


//
// Deletes any avatars owned by the specified user ID
//
function delete_avatar($user_id)
{
	global $pun_config;

	$filetypes = array('jpg', 'gif', 'png');

	// Delete user avatar
	foreach ($filetypes as $cur_type)
	{
		if (file_exists(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$user_id.'.'.$cur_type))
			@unlink(PUN_ROOT.$pun_config['o_avatars_dir'].'/'.$user_id.'.'.$cur_type);
	}
}


//
// Delete a topic and all of it's posts
//
function delete_topic($topic_id)
{
	global $db;

	
	require_once PUN_ROOT.'include/attach/attach_incl.php'; //Attachment Mod row, loads variables, functions and lang file
	attach_delete_thread($topic_id);	// Attachment Mod , delete the attachments in the whole thread (orphan check is checked in this function)

	// Delete the topic and any redirect topics
	$db->query('DELETE FROM '.$db->prefix.'topics WHERE id='.$topic_id.' OR moved_to='.$topic_id) or error('Unable to delete topic', __FILE__, __LINE__, $db->error());

	// Create a list of the post IDs in this topic
	$post_ids = '';
	$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to fetch posts', __FILE__, __LINE__, $db->error());
	while ($row = $db->fetch_row($result))
		$post_ids .= ($post_ids != '') ? ','.$row[0] : $row[0];

	// Make sure we have a list of post IDs
	if ($post_ids != '')
	{
		strip_search_index($post_ids);

		// Delete posts in topic
		$db->query('DELETE FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to delete posts', __FILE__, __LINE__, $db->error());
	}

	// Delete any subscriptions for this topic
	$db->query('DELETE FROM '.$db->prefix.'topic_subscriptions WHERE topic_id='.$topic_id) or error('Unable to delete subscriptions', __FILE__, __LINE__, $db->error());
	// Delete any thanks for this post
	$db->query('DELETE FROM '.$db->prefix.'thanks WHERE topic_id='.$topic_id) or error('Unable to delete thanks', __FILE__, __LINE__, $db->error());
	// Delete any thanks for this post
	$db->query('DELETE FROM '.$db->prefix.'thanks2 WHERE topic_id='.$topic_id) or error('Unable to delete thanks', __FILE__, __LINE__, $db->error());

}


//
// Delete a single post
//
function delete_post($post_id, $topic_id)
{
	global $db;

	
	require_once PUN_ROOT.'include/attach/attach_incl.php'; //Attachment Mod row, loads variables, functions and lang file
	attach_delete_post($post_id);	// Attachment Mod , delete the attachments in this post (orphan check is checked in this function)

	$result = $db->query('SELECT id, poster, posted FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id.' ORDER BY id DESC LIMIT 2') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	list($last_id, ,) = $db->fetch_row($result);
	list($second_last_id, $second_poster, $second_posted) = $db->fetch_row($result);

	// Delete the post
	$db->query('DELETE FROM '.$db->prefix.'posts WHERE id='.$post_id) or error('Unable to delete post', __FILE__, __LINE__, $db->error());
	// Delete any thanks for this post
	$db->query('DELETE FROM '.$db->prefix.'thanks WHERE post_id='.$post_id) or error('Unable to delete thanks', __FILE__, __LINE__, $db->error());
	// Delete any thanks for this post
	$db->query('DELETE FROM '.$db->prefix.'thanks2 WHERE post_id='.$post_id) or error('Unable to delete thanks', __FILE__, __LINE__, $db->error());


	strip_search_index($post_id);

	// Count number of replies in the topic
	$result = $db->query('SELECT COUNT(id) FROM '.$db->prefix.'posts WHERE topic_id='.$topic_id) or error('Unable to fetch post count for topic', __FILE__, __LINE__, $db->error());
	$num_replies = $db->result($result, 0) - 1;

	// If the message we deleted is the most recent in the topic (at the end of the topic)
	if ($last_id == $post_id)
	{
		// If there is a $second_last_id there is more than 1 reply to the topic
		if (!empty($second_last_id))
			$db->query('UPDATE '.$db->prefix.'topics SET last_post='.$second_posted.', last_post_id='.$second_last_id.', last_poster=\''.$db->escape($second_poster).'\', num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
		else
			// We deleted the only reply, so now last_post/last_post_id/last_poster is posted/id/poster from the topic itself
			$db->query('UPDATE '.$db->prefix.'topics SET last_post=posted, last_post_id=id, last_poster=poster, num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
	}
	else
		// Otherwise we just decrement the reply counter
		$db->query('UPDATE '.$db->prefix.'topics SET num_replies='.$num_replies.' WHERE id='.$topic_id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());
}


//
// Delete every .php file in the forum's cache directory
//
function forum_clear_cache()
{
	$d = dir(FORUM_CACHE_DIR);
	while (($entry = $d->read()) !== false)
	{
		if (substr($entry, -4) == '.php')
			@unlink(FORUM_CACHE_DIR.$entry);
	}
	$d->close();
}


//
// Replace censored words in $text
//
function censor_words($text)
{
	global $db;
	static $search_for, $replace_with;

	// If not already built in a previous call, build an array of censor words and their replacement text
	if (!isset($search_for))
	{
		if (file_exists(FORUM_CACHE_DIR.'cache_censoring.php'))
			include FORUM_CACHE_DIR.'cache_censoring.php';

		if (!defined('PUN_CENSOR_LOADED'))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PUN_ROOT.'include/cache.php';

			generate_censoring_cache();
			require FORUM_CACHE_DIR.'cache_censoring.php';
		}
	}

	if (!empty($search_for))
		$text = substr(ucp_preg_replace($search_for, $replace_with, ' '.$text.' '), 1, -1);

	return $text;
}


//
// Determines the correct title for $user
// $user must contain the elements 'username', 'title', 'posts', 'g_id' and 'g_user_title'
//
function get_title($user)
{
	global $db, $pun_config, $pun_bans, $lang_common;
	static $ban_list, $pun_ranks;

	// If not already built in a previous call, build an array of lowercase banned usernames
	if (empty($ban_list))
	{
		$ban_list = array();

		foreach ($pun_bans as $cur_ban)
			$ban_list[] = strtolower($cur_ban['username']);
	}

	// If not already loaded in a previous call, load the cached ranks
	if ($pun_config['o_ranks'] == '1' && !defined('PUN_RANKS_LOADED'))
	{
		if (file_exists(FORUM_CACHE_DIR.'cache_ranks.php'))
			include FORUM_CACHE_DIR.'cache_ranks.php';

		if (!defined('PUN_RANKS_LOADED'))
		{
			if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
				require PUN_ROOT.'include/cache.php';

			generate_ranks_cache();
			require FORUM_CACHE_DIR.'cache_ranks.php';
		}
	}

	// If the user has a custom title
	if ($user['title'] != '')
		$user_title = pun_htmlspecialchars($user['title']);
	// If the user is banned
	else if (in_array(strtolower($user['username']), $ban_list))
		$user_title = $lang_common['Banned'];
	// If the user group has a default user title
	else if ($user['g_user_title'] != '')
		$user_title = pun_htmlspecialchars($user['g_user_title']);
	// If the user is a guest
	else if ($user['g_id'] == PUN_GUEST)
		$user_title = $lang_common['Guest'];
	else
	{
		// Are there any ranks?
		if ($pun_config['o_ranks'] == '1' && !empty($pun_ranks))
		{
			foreach ($pun_ranks as $cur_rank)
			{
				if ($user['num_posts'] >= $cur_rank['min_posts'])
					$user_title = pun_htmlspecialchars($cur_rank['rank']);
			}
		}

		// If the user didn't "reach" any rank (or if ranks are disabled), we assign the default
		if (!isset($user_title))
			$user_title = $lang_common['Member'];
	}

	return $user_title;
}


//
// Generate a string with numbered links (for multipage scripts)
//
function paginate($num_pages, $cur_page, $link)
{
	global $lang_common;

	$pages = array();
	$link_to_all = false;

	// If $cur_page == -1, we link to all pages (used in viewforum.php)
	if ($cur_page == -1)
	{
		$cur_page = 1;
		$link_to_all = true;
	}

	if ($num_pages <= 1)
		$pages = array('<strong class="item1">1</strong>');
	else
	{
		// Add a previous page link
		if ($num_pages > 1 && $cur_page > 1)
			$pages[] = '<a'.(empty($pages) ? ' class="item1"' : '').' href="'.$link.'&amp;p='.($cur_page - 1).'">'.$lang_common['Previous'].'</a>';

		if ($cur_page > 3)
		{
			$pages[] = '<a'.(empty($pages) ? ' class="item1"' : '').' href="'.$link.'&amp;p=1">1</a>';

			if ($cur_page > 5)
				$pages[] = '<span class="spacer">'.$lang_common['Spacer'].'</span>';
		}

		// Don't ask me how the following works. It just does, OK? :-)
		for ($current = ($cur_page == 5) ? $cur_page - 3 : $cur_page - 2, $stop = ($cur_page + 4 == $num_pages) ? $cur_page + 4 : $cur_page + 3; $current < $stop; ++$current)
		{
			if ($current < 1 || $current > $num_pages)
				continue;
			else if ($current != $cur_page || $link_to_all)
				$pages[] = '<a'.(empty($pages) ? ' class="item1"' : '').' href="'.$link.'&amp;p='.$current.'">'.forum_number_format($current).'</a>';
			else
				$pages[] = '<strong'.(empty($pages) ? ' class="item1"' : '').'>'.forum_number_format($current).'</strong>';
		}

		if ($cur_page <= ($num_pages-3))
		{
			if ($cur_page != ($num_pages-3) && $cur_page != ($num_pages-4))
				$pages[] = '<span class="spacer">'.$lang_common['Spacer'].'</span>';

			$pages[] = '<a'.(empty($pages) ? ' class="item1"' : '').' href="'.$link.'&amp;p='.$num_pages.'">'.forum_number_format($num_pages).'</a>';
		}

		// Add a next page link
		if ($num_pages > 1 && !$link_to_all && $cur_page < $num_pages)
			$pages[] = '<a'.(empty($pages) ? ' class="item1"' : '').' href="'.$link.'&amp;p='.($cur_page +1).'">'.$lang_common['Next'].'</a>';
	}

	return implode(' ', $pages);
}


//
// Display a message
//
function message($message, $no_back_link = false)
{
	global $db, $lang_common, $pun_config, $pun_start, $tpl_main, $pun_user;

	if (!defined('PUN_HEADER'))
	{
		$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_common['Info']);
		define('PUN_ACTIVE_PAGE', 'index');
		require PUN_ROOT.'header.php';
	}

?>

<div id="msg" class="block">
	<h2><span><?php echo $lang_common['Info'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $message ?></p>
<?php if (!$no_back_link): ?>			<p><a href="javascript: history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
<?php endif; ?>		</div>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


//
// Format a time string according to $time_format and time zones
//
function format_time($timestamp, $date_only = false, $date_format = null, $time_format = null, $time_only = false, $no_text = false)
{
	global $pun_config, $lang_common, $pun_user, $forum_date_formats, $forum_time_formats;

	if ($timestamp == '')
		return $lang_common['Never'];

	$diff = ($pun_user['timezone'] + $pun_user['dst']) * 3600;
	$timestamp += $diff;
	$now = time();

	if($date_format == null)
		$date_format = $forum_date_formats[$pun_user['date_format']];

	if($time_format == null)
		$time_format = $forum_time_formats[$pun_user['time_format']];

	$date = gmdate($date_format, $timestamp);
	$today = gmdate($date_format, $now+$diff);
	$yesterday = gmdate($date_format, $now+$diff-86400);

	if(!$no_text)
	{
		if ($date == $today)
			$date = $lang_common['Today'];
		else if ($date == $yesterday)
			$date = $lang_common['Yesterday'];
	}

	if ($date_only)
		return $date;
	else if ($time_only)
		return gmdate($time_format, $timestamp);
	else
		return $date.' '.gmdate($time_format, $timestamp);
}


//
// A wrapper for PHP's number_format function
//
function forum_number_format($number, $decimals = 0)
{
	global $lang_common;

	return is_numeric($number) ? number_format($number, $decimals, $lang_common['lang_decimal_point'], $lang_common['lang_thousands_sep']) : $number;
}


//
// Generate a random key of length $len
//
function random_key($len, $readable = false, $hash = false)
{
	$key = '';

	if ($hash)
		$key = substr(pun_hash(uniqid(rand(), true)), 0, $len);
	else if ($readable)
	{
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		for ($i = 0; $i < $len; ++$i)
			$key .= substr($chars, (mt_rand() % strlen($chars)), 1);
	}
	else
	{
		for ($i = 0; $i < $len; ++$i)
			$key .= chr(mt_rand(33, 126));
	}

	return $key;
}


//
// Make sure that HTTP_REFERER matches base_url/script
//
function confirm_referrer($script, $error_msg = false)
{
	global $pun_config, $lang_common;

	// There is no referrer
	if (empty($_SERVER['HTTP_REFERER']))
		message($error_msg ? $error_msg : $lang_common['Bad referrer']);

	$referrer = parse_url(strtolower($_SERVER['HTTP_REFERER']));
	// Remove www subdomain if it exists
	if (strpos($referrer['host'], 'www.') === 0)
		$referrer['host'] = substr($referrer['host'], 4);

	$valid = parse_url(strtolower(get_base_url().'/'.$script));
	// Remove www subdomain if it exists
	if (strpos($valid['host'], 'www.') === 0)
		$valid['host'] = substr($valid['host'], 4);

	// Check the host and path match. Ignore the scheme, port, etc.
	if ($referrer['host'] != $valid['host'] || $referrer['path'] != $valid['path'])
		message($error_msg ? $error_msg : $lang_common['Bad referrer']);
}


//
// Generate a random password of length $len
// Compatibility wrapper for random_key
//
function random_pass($len)
{
	return random_key($len, true);
}


//
// Compute a hash of $str
//
function pun_hash($str)
{
	return sha1($str);
}


//
// Try to determine the correct remote IP-address
//
function get_remote_address()
{
	$remote_addr = $_SERVER['REMOTE_ADDR'];

	// If we are behind a reverse proxy try to find the real users IP
	if (defined('FORUM_BEHIND_REVERSE_PROXY'))
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			// The general format of the field is:
			// X-Forwarded-For: client1, proxy1, proxy2
			// where the value is a comma+space separated list of IP addresses, the left-most being the farthest downstream client,
			// and each successive proxy that passed the request adding the IP address where it received the request from.
			$forwarded_for = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$forwarded_for = trim($forwarded_for[0]);

			if (@preg_match('%^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$%', $forwarded_for) || @preg_match('%^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$%', $forwarded_for))
				$remote_addr = $forwarded_for;
		}
	}

	return $remote_addr;
}


//
// Calls htmlspecialchars with a few options already set
//
function pun_htmlspecialchars($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


//
// Calls htmlspecialchars_decode with a few options already set
//
function pun_htmlspecialchars_decode($str)
{
	if (function_exists('htmlspecialchars_decode'))
		return htmlspecialchars_decode($str, ENT_QUOTES);

	static $translations;
	if (!isset($translations))
	{
		$translations = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
		$translations['&#039;'] = '\''; // get_html_translation_table doesn't include &#039; which is what htmlspecialchars translates ' to, but apparently that is okay?! http://bugs.php.net/bug.php?id=25927
		$translations = array_flip($translations);
	}

	return strtr($str, $translations);
}


//
// A wrapper for utf8_strlen for compatibility
//
function pun_strlen($str)
{
	return utf8_strlen($str);
}


//
// Convert \r\n and \r to \n
//
function pun_linebreaks($str)
{
	return str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
}


//
// A wrapper for utf8_trim for compatibility
//
function pun_trim($str, $charlist = false)
{
	return utf8_trim($str, $charlist);
}

//
// Checks if a string is in all uppercase
//
function is_all_uppercase($string)
{
	return utf8_strtoupper($string) == $string && utf8_strtolower($string) != $string;
}


//
// Inserts $element into $input at $offset
// $offset can be either a numerical offset to insert at (eg: 0 inserts at the beginning of the array)
// or a string, which is the key that the new element should be inserted before
// $key is optional: it's used when inserting a new key/value pair into an associative array
//
function array_insert(&$input, $offset, $element, $key = null)
{
	if ($key == null)
		$key = $offset;

	// Determine the proper offset if we're using a string
	if (!is_int($offset))
		$offset = array_search($offset, array_keys($input), true);

	// Out of bounds checks
	if ($offset > count($input))
		$offset = count($input);
	else if ($offset < 0)
		$offset = 0;

	$input = array_merge(array_slice($input, 0, $offset), array($key => $element), array_slice($input, $offset));
}


//
// Display a message when board is in maintenance mode
//
function maintenance_message()
{
	global $db, $pun_config, $lang_common, $pun_user;

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	// Deal with newlines, tabs and multiple spaces
	$pattern = array("\t", '  ', '  ');
	$replace = array('&#160; &#160; ', '&#160; ', ' &#160;');
	$message = str_replace($pattern, $replace, $pun_config['o_maintenance_message']);

	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/maintenance.tpl'))
	{
		$tpl_file = PUN_ROOT.'style/'.$pun_user['style'].'/maintenance.tpl';
		$tpl_inc_dir = PUN_ROOT.'style/'.$pun_user['style'].'/';
	}
	else
	{
		$tpl_file = PUN_ROOT.'include/template/maintenance.tpl';
		$tpl_inc_dir = PUN_ROOT.'include/user/';
	}

	$tpl_maint = file_get_contents($tpl_file);

	// START SUBST - <pun_include "*">
	preg_match_all('%<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">%i', $tpl_maint, $pun_includes, PREG_SET_ORDER);

	foreach ($pun_includes as $cur_include)
	{
		ob_start();

		// Allow for overriding user includes, too.
		if (file_exists($tpl_inc_dir.$cur_include[1].'.'.$cur_include[2]))
			require $tpl_inc_dir.$cur_include[1].'.'.$cur_include[2];
		else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2]))
			require PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
		else
			error(sprintf($lang_common['Pun include error'], htmlspecialchars($cur_include[0]), basename($tpl_file)));

		$tpl_temp = ob_get_contents();
		$tpl_maint = str_replace($cur_include[0], $tpl_temp, $tpl_maint);
		ob_end_clean();
	}
	// END SUBST - <pun_include "*">


	// START SUBST - <pun_language>
	$tpl_maint = str_replace('<pun_language>', $lang_common['lang_identifier'], $tpl_maint);
	// END SUBST - <pun_language>


	// START SUBST - <pun_content_direction>
	$tpl_maint = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_maint);
	// END SUBST - <pun_content_direction>


	// START SUBST - <pun_head>
	ob_start();

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_common['Maintenance']);

?>
<title><?php echo generate_page_title($page_title) ?></title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />
<?php

	$tpl_temp = trim(ob_get_contents());
	$tpl_maint = str_replace('<pun_head>', $tpl_temp, $tpl_maint);
	ob_end_clean();
	// END SUBST - <pun_head>


	// START SUBST - <pun_maint_main>
	ob_start();

?>
<div class="block">
	<h2><?php echo $lang_common['Maintenance'] ?></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $message ?></p>
		</div>
	</div>
</div>
<?php

	$tpl_temp = trim(ob_get_contents());
	$tpl_maint = str_replace('<pun_maint_main>', $tpl_temp, $tpl_maint);
	ob_end_clean();
	// END SUBST - <pun_maint_main>


	// End the transaction
	$db->end_transaction();


	// Close the db connection (and free up any result data)
	$db->close();

	exit($tpl_maint);
}


//
// Display $message and redirect user to $destination_url
//
function redirect($destination_url, $message)
{
	global $db, $pun_config, $lang_common, $pun_user;

	// Prefix with base_url (unless there's already a valid URI)
	if (strpos($destination_url, 'http://') !== 0 && strpos($destination_url, 'https://') !== 0 && strpos($destination_url, '/') !== 0)
		$destination_url = get_base_url(true).'/'.$destination_url;

	// Do a little spring cleaning
	$destination_url = preg_replace('%([\r\n])|(\%0[ad])|(;\s*data\s*:)%i', '', $destination_url);

	// If the delay is 0 seconds, we might as well skip the redirect all together
	if ($pun_config['o_redirect_delay'] == '0')
		header('Location: '.str_replace('&amp;', '&', $destination_url));

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

	if (file_exists(PUN_ROOT.'style/'.$pun_user['style'].'/redirect.tpl'))
	{
		$tpl_file = PUN_ROOT.'style/'.$pun_user['style'].'/redirect.tpl';
		$tpl_inc_dir = PUN_ROOT.'style/'.$pun_user['style'].'/';
	}
	else
	{
		$tpl_file = PUN_ROOT.'include/template/redirect.tpl';
		$tpl_inc_dir = PUN_ROOT.'include/user/';
	}

	$tpl_redir = file_get_contents($tpl_file);

	// START SUBST - <pun_include "*">
	preg_match_all('%<pun_include "([^/\\\\]*?)\.(php[45]?|inc|html?|txt)">%i', $tpl_redir, $pun_includes, PREG_SET_ORDER);

	foreach ($pun_includes as $cur_include)
	{
		ob_start();

		// Allow for overriding user includes, too.
		if (file_exists($tpl_inc_dir.$cur_include[1].'.'.$cur_include[2]))
			require $tpl_inc_dir.$cur_include[1].'.'.$cur_include[2];
		else if (file_exists(PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2]))
			require PUN_ROOT.'include/user/'.$cur_include[1].'.'.$cur_include[2];
		else
			error(sprintf($lang_common['Pun include error'], htmlspecialchars($cur_include[0]), basename($tpl_file)));

		$tpl_temp = ob_get_contents();
		$tpl_redir = str_replace($cur_include[0], $tpl_temp, $tpl_redir);
		ob_end_clean();
	}
	// END SUBST - <pun_include "*">


	// START SUBST - <pun_language>
	$tpl_redir = str_replace('<pun_language>', $lang_common['lang_identifier'], $tpl_redir);
	// END SUBST - <pun_language>


	// START SUBST - <pun_content_direction>
	$tpl_redir = str_replace('<pun_content_direction>', $lang_common['lang_direction'], $tpl_redir);
	// END SUBST - <pun_content_direction>


	// START SUBST - <pun_head>
	ob_start();

	$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_common['Redirecting']);

?>
<meta http-equiv="refresh" content="<?php echo $pun_config['o_redirect_delay'] ?>;URL=<?php echo str_replace(array('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $destination_url) ?>" />
<title><?php echo generate_page_title($page_title) ?></title>
<link rel="stylesheet" type="text/css" href="style/<?php echo $pun_user['style'].'.css' ?>" />
<?php

	$tpl_temp = trim(ob_get_contents());
	$tpl_redir = str_replace('<pun_head>', $tpl_temp, $tpl_redir);
	ob_end_clean();
	// END SUBST - <pun_head>


	// START SUBST - <pun_redir_main>
	ob_start();

?>
<div class="block">
	<h2><?php echo $lang_common['Redirecting'] ?></h2>
	<div class="box">
		<div class="inbox">
			<p><?php echo $message.'<br /><br /><a href="'.$destination_url.'">'.$lang_common['Click redirect'].'</a>' ?></p>
		</div>
	</div>
</div>
<?php

	$tpl_temp = trim(ob_get_contents());
	$tpl_redir = str_replace('<pun_redir_main>', $tpl_temp, $tpl_redir);
	ob_end_clean();
	// END SUBST - <pun_redir_main>


	// START SUBST - <pun_footer>
	ob_start();

	// End the transaction
	$db->end_transaction();

	// Display executed queries (if enabled)
	if (defined('PUN_SHOW_QUERIES'))
		display_saved_queries();

	$tpl_temp = trim(ob_get_contents());
	$tpl_redir = str_replace('<pun_footer>', $tpl_temp, $tpl_redir);
	ob_end_clean();
	// END SUBST - <pun_footer>


	// Close the db connection (and free up any result data)
	$db->close();

	exit($tpl_redir);
}


//
// Display a simple error message
//
function error($message, $file = null, $line = null, $db_error = false)
{
	global $pun_config, $lang_common;

	// Set some default settings if the script failed before $pun_config could be populated
	if (empty($pun_config))
	{
		$pun_config = array(
			'o_board_title'	=> 'FluxBB',
			'o_gzip'		=> '0'
		);
	}

	// Set some default translations if the script failed before $lang_common could be populated
	if (empty($lang_common))
	{
		$lang_common = array(
			'Title separator'	=> ' / ',
			'Page'				=> 'Page %s'
		);
	}

	// Empty all output buffers and stop buffering
	while (@ob_end_clean());

	// "Restart" output buffering if we are using ob_gzhandler (since the gzip header is already sent)
	if ($pun_config['o_gzip'] && extension_loaded('zlib'))
		ob_start('ob_gzhandler');

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT'); // When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compatibility

	// Send the Content-type header in case the web server is setup to send something else
	header('Content-type: text/html; charset=utf-8');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php $page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), 'Error') ?>
<title><?php echo generate_page_title($page_title) ?></title>
<style type="text/css">
<!--
BODY {MARGIN: 10% 20% auto 20%; font: 10px Verdana, Arial, Helvetica, sans-serif}
#errorbox {BORDER: 1px solid #B84623}
H2 {MARGIN: 0; COLOR: #FFFFFF; BACKGROUND-COLOR: #B84623; FONT-SIZE: 1.1em; PADDING: 5px 4px}
#errorbox DIV {PADDING: 6px 5px; BACKGROUND-COLOR: #F1F1F1}
-->
</style>
</head>
<body>

<div id="errorbox">
	<h2>An error was encountered</h2>
	<div>
<?php

	if (defined('PUN_DEBUG') && $file !== null && $line !== null)
	{
		echo "\t\t".'<strong>File:</strong> '.$file.'<br />'."\n\t\t".'<strong>Line:</strong> '.$line.'<br /><br />'."\n\t\t".'<strong>FluxBB reported</strong>: '.$message."\n";

		if ($db_error)
		{
			echo "\t\t".'<br /><br /><strong>Database reported:</strong> '.pun_htmlspecialchars($db_error['error_msg']).(($db_error['error_no']) ? ' (Errno: '.$db_error['error_no'].')' : '')."\n";

			if ($db_error['error_sql'] != '')
				echo "\t\t".'<br /><br /><strong>Failed query:</strong> '.pun_htmlspecialchars($db_error['error_sql'])."\n";
		}
	}
	else
		echo "\t\t".'Error: <strong>'.$message.'.</strong>'."\n";

?>
	</div>
</div>

</body>
</html>
<?php

	// If a database connection was established (before this error) we close it
	if ($db_error)
		$GLOBALS['db']->close();

	exit;
}


//
// Unset any variables instantiated as a result of register_globals being enabled
//
function forum_unregister_globals()
{
	$register_globals = ini_get('register_globals');
	if ($register_globals === '' || $register_globals === '0' || strtolower($register_globals) === 'off')
		return;

	// Prevent script.php?GLOBALS[foo]=bar
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
		exit('I\'ll have a steak sandwich and... a steak sandwich.');

	// Variables that shouldn't be unset
	$no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

	// Remove elements in $GLOBALS that are present in any of the superglobals
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ($input as $k => $v)
	{
		if (!in_array($k, $no_unset) && isset($GLOBALS[$k]))
		{
			unset($GLOBALS[$k]);
			unset($GLOBALS[$k]); // Double unset to circumvent the zend_hash_del_key_or_index hole in PHP <4.4.3 and <5.1.4
		}
	}
}


//
// Removes any "bad" characters (characters which mess with the display of a page, are invisible, etc) from user input
//
function forum_remove_bad_characters()
{
	$_GET = remove_bad_characters($_GET);
	$_POST = remove_bad_characters($_POST);
	$_COOKIE = remove_bad_characters($_COOKIE);
	$_REQUEST = remove_bad_characters($_REQUEST);
}

//
// Removes any "bad" characters (characters which mess with the display of a page, are invisible, etc) from the given string
// See: http://kb.mozillazine.org/Network.IDN.blacklist_chars
//
function remove_bad_characters($array)
{
	static $bad_utf8_chars;

	if (!isset($bad_utf8_chars))
	{
		$bad_utf8_chars = array(
			"\xcc\xb7"		=> '',		// COMBINING SHORT SOLIDUS OVERLAY		0337	*
			"\xcc\xb8"		=> '',		// COMBINING LONG SOLIDUS OVERLAY		0338	*
			"\xe1\x85\x9F"	=> '',		// HANGUL CHOSEONG FILLER				115F	*
			"\xe1\x85\xA0"	=> '',		// HANGUL JUNGSEONG FILLER				1160	*
			"\xe2\x80\x8b"	=> '',		// ZERO WIDTH SPACE						200B	*
			"\xe2\x80\x8c"	=> '',		// ZERO WIDTH NON-JOINER				200C
			"\xe2\x80\x8d"	=> '',		// ZERO WIDTH JOINER					200D
			"\xe2\x80\x8e"	=> '',		// LEFT-TO-RIGHT MARK					200E
			"\xe2\x80\x8f"	=> '',		// RIGHT-TO-LEFT MARK					200F
			"\xe2\x80\xaa"	=> '',		// LEFT-TO-RIGHT EMBEDDING				202A
			"\xe2\x80\xab"	=> '',		// RIGHT-TO-LEFT EMBEDDING				202B
			"\xe2\x80\xac"	=> '', 		// POP DIRECTIONAL FORMATTING			202C
			"\xe2\x80\xad"	=> '',		// LEFT-TO-RIGHT OVERRIDE				202D
			"\xe2\x80\xae"	=> '',		// RIGHT-TO-LEFT OVERRIDE				202E
			"\xe2\x80\xaf"	=> '',		// NARROW NO-BREAK SPACE				202F	*
			"\xe2\x81\x9f"	=> '',		// MEDIUM MATHEMATICAL SPACE			205F	*
			"\xe2\x81\xa0"	=> '',		// WORD JOINER							2060
			"\xe3\x85\xa4"	=> '',		// HANGUL FILLER						3164	*
			"\xef\xbb\xbf"	=> '',		// ZERO WIDTH NO-BREAK SPACE			FEFF
			"\xef\xbe\xa0"	=> '',		// HALFWIDTH HANGUL FILLER				FFA0	*
			"\xef\xbf\xb9"	=> '',		// INTERLINEAR ANNOTATION ANCHOR		FFF9	*
			"\xef\xbf\xba"	=> '',		// INTERLINEAR ANNOTATION SEPARATOR		FFFA	*
			"\xef\xbf\xbb"	=> '',		// INTERLINEAR ANNOTATION TERMINATOR	FFFB	*
			"\xef\xbf\xbc"	=> '',		// OBJECT REPLACEMENT CHARACTER			FFFC	*
			"\xef\xbf\xbd"	=> '',		// REPLACEMENT CHARACTER				FFFD	*
			"\xe2\x80\x80"	=> ' ',		// EN QUAD								2000	*
			"\xe2\x80\x81"	=> ' ',		// EM QUAD								2001	*
			"\xe2\x80\x82"	=> ' ',		// EN SPACE								2002	*
			"\xe2\x80\x83"	=> ' ',		// EM SPACE								2003	*
			"\xe2\x80\x84"	=> ' ',		// THREE-PER-EM SPACE					2004	*
			"\xe2\x80\x85"	=> ' ',		// FOUR-PER-EM SPACE					2005	*
			"\xe2\x80\x86"	=> ' ',		// SIX-PER-EM SPACE						2006	*
			"\xe2\x80\x87"	=> ' ',		// FIGURE SPACE							2007	*
			"\xe2\x80\x88"	=> ' ',		// PUNCTUATION SPACE					2008	*
			"\xe2\x80\x89"	=> ' ',		// THIN SPACE							2009	*
			"\xe2\x80\x8a"	=> ' ',		// HAIR SPACE							200A	*
			"\xE3\x80\x80"	=> ' ',		// IDEOGRAPHIC SPACE					3000	*
		);
	}

	if (is_array($array))
		return array_map('remove_bad_characters', $array);

	// Strip out any invalid characters
	$array = utf8_bad_strip($array);

	// Remove control characters
	$array = preg_replace('%[\x00-\x08\x0b-\x0c\x0e-\x1f]%', '', $array);

	// Replace some "bad" characters
	$array = str_replace(array_keys($bad_utf8_chars), array_values($bad_utf8_chars), $array);

	return $array;
}


//
// Converts the file size in bytes to a human readable file size
//
function file_size($size)
{
	global $lang_common;

	$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB');

	for ($i = 0; $size > 1024; $i++)
		$size /= 1024;

	return sprintf($lang_common['Size unit '.$units[$i]], round($size, 2));;
}


//
// Fetch a list of available styles
//
function forum_list_styles()
{
	$styles = array();

	$d = dir(PUN_ROOT.'style');
	while (($entry = $d->read()) !== false)
	{
		if ($entry{0} == '.')
			continue;

		if (substr($entry, -4) == '.css')
			$styles[] = substr($entry, 0, -4);
	}
	$d->close();

	natcasesort($styles);

	return $styles;
}


//
// Fetch a list of available language packs
//
function forum_list_langs()
{
	$languages = array();

	$d = dir(PUN_ROOT.'lang');
	while (($entry = $d->read()) !== false)
	{
		if ($entry{0} == '.')
			continue;

		if (is_dir(PUN_ROOT.'lang/'.$entry) && file_exists(PUN_ROOT.'lang/'.$entry.'/common.php'))
			$languages[] = $entry;
	}
	$d->close();

	natcasesort($languages);

	return $languages;
}


//
// Generate a cache ID based on the last modification time for all stopwords files
//
function generate_stopwords_cache_id()
{
	$files = glob(PUN_ROOT.'lang/*/stopwords.txt');
	if ($files === false)
		return 'cache_id_error';

	$hash = array();

	foreach ($files as $file)
	{
		$hash[] = $file;
		$hash[] = filemtime($file);
	}

	return sha1(implode('|', $hash));
}


//
// Fetch a list of available admin plugins
//
function forum_list_plugins($is_admin)
{
	$plugins = array();

	$d = dir(PUN_ROOT.'plugins');
	while (($entry = $d->read()) !== false)
	{
		if ($entry{0} == '.')
			continue;

		$prefix = substr($entry, 0, strpos($entry, '_'));
		$suffix = substr($entry, strlen($entry) - 4);

		if ($suffix == '.php' && ((!$is_admin && $prefix == 'AMP') || ($is_admin && ($prefix == 'AP' || $prefix == 'AMP'))))
			$plugins[$entry] = substr($entry, strpos($entry, '_') + 1, -4);
	}
	$d->close();

	natcasesort($plugins);

	return $plugins;
}


//
// Split text into chunks ($inside contains all text inside $start and $end, and $outside contains all text outside)
//
function split_text($text, $start, $end, $retab = true)
{
	global $pun_config, $lang_common;

	$result = array(0 => array(), 1 => array()); // 0 = inside, 1 = outside

	// split the text into parts
	$parts = preg_split('%'.preg_quote($start, '%').'(.*)'.preg_quote($end, '%').'%Us', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
	$num_parts = count($parts);

	// preg_split results in outside parts having even indices, inside parts having odd
	for ($i = 0;$i < $num_parts;$i++)
		$result[1 - ($i % 2)][] = $parts[$i];

	if ($pun_config['o_indent_num_spaces'] != 8 && $retab)
	{
		$spaces = str_repeat(' ', $pun_config['o_indent_num_spaces']);
		$result[1] = str_replace("\t", $spaces, $result[1]);
	}

	return $result;
}


//
// Extract blocks from a text with a starting and ending string
// This function always matches the most outer block so nesting is possible
//
function extract_blocks($text, $start, $end, $retab = true)
{
	global $pun_config;

	$code = array();
	$start_len = strlen($start);
	$end_len = strlen($end);
	$regex = '%(?:'.preg_quote($start, '%').'|'.preg_quote($end, '%').')%';
	$matches = array();

	if (preg_match_all($regex, $text, $matches))
	{
		$counter = $offset = 0;
		$start_pos = $end_pos = false;

		foreach ($matches[0] as $match)
		{
			if ($match == $start)
			{
				if ($counter == 0)
					$start_pos = strpos($text, $start);
				$counter++;
			}
			elseif ($match == $end)
			{
				$counter--;
				if ($counter == 0)
					$end_pos = strpos($text, $end, $offset + 1);
				$offset = strpos($text, $end, $offset + 1);
			}

			if ($start_pos !== false && $end_pos !== false)
			{
				$code[] = substr($text, $start_pos + $start_len,
					$end_pos - $start_pos - $start_len);
				$text = substr_replace($text, "\1", $start_pos,
					$end_pos - $start_pos + $end_len);
				$start_pos = $end_pos = false;
				$offset = 0;
			}
		}
	}

	if ($pun_config['o_indent_num_spaces'] != 8 && $retab)
	{
		$spaces = str_repeat(' ', $pun_config['o_indent_num_spaces']);
		$text = str_replace("\t", $spaces, $text);
	}

	return array($code, $text);
}


//
// function url_valid($url) {
//
// Return associative array of valid URI components, or FALSE if $url is not
// RFC-3986 compliant. If the passed URL begins with: "www." or "ftp.", then
// "http://" or "ftp://" is prepended and the corrected full-url is stored in
// the return array with a key name "url". This value should be used by the caller.
//
// Return value: FALSE if $url is not valid, otherwise array of URI components:
// e.g.
// Given: "http://www.jmrware.com:80/articles?height=10&width=75#fragone"
// Array(
//	  [scheme] => http
//	  [authority] => www.jmrware.com:80
//	  [userinfo] =>
//	  [host] => www.jmrware.com
//	  [IP_literal] =>
//	  [IPV6address] =>
//	  [ls32] =>
//	  [IPvFuture] =>
//	  [IPv4address] =>
//	  [regname] => www.jmrware.com
//	  [port] => 80
//	  [path_abempty] => /articles
//	  [query] => height=10&width=75
//	  [fragment] => fragone
//	  [url] => http://www.jmrware.com:80/articles?height=10&width=75#fragone
// )
function url_valid($url)
{
	if (strpos($url, 'www.') === 0) $url = 'http://'. $url;
	if (strpos($url, 'ftp.') === 0) $url = 'ftp://'. $url;
	if (!preg_match('/# Valid absolute URI having a non-empty, valid DNS host.
		^
		(?P<scheme>[A-Za-z][A-Za-z0-9+\-.]*):\/\/
		(?P<authority>
		  (?:(?P<userinfo>(?:[A-Za-z0-9\-._~!$&\'()*+,;=:]|%[0-9A-Fa-f]{2})*)@)?
		  (?P<host>
			(?P<IP_literal>
			  \[
			  (?:
				(?P<IPV6address>
				  (?:												 (?:[0-9A-Fa-f]{1,4}:){6}
				  |												   ::(?:[0-9A-Fa-f]{1,4}:){5}
				  | (?:							 [0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){4}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,1}[0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){3}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})?::(?:[0-9A-Fa-f]{1,4}:){2}
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})?::	[0-9A-Fa-f]{1,4}:
				  | (?:(?:[0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})?::
				  )
				  (?P<ls32>[0-9A-Fa-f]{1,4}:[0-9A-Fa-f]{1,4}
				  | (?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
					   (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)
				  )
				|	(?:(?:[0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})?::	[0-9A-Fa-f]{1,4}
				|	(?:(?:[0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})?::
				)
			  | (?P<IPvFuture>[Vv][0-9A-Fa-f]+\.[A-Za-z0-9\-._~!$&\'()*+,;=:]+)
			  )
			  \]
			)
		  | (?P<IPv4address>(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}
							   (?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))
		  | (?P<regname>(?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-Fa-f]{2})+)
		  )
		  (?::(?P<port>[0-9]*))?
		)
		(?P<path_abempty>(?:\/(?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})*)*)
		(?:\?(?P<query>		  (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\\/?]|%[0-9A-Fa-f]{2})*))?
		(?:\#(?P<fragment>	  (?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\\/?]|%[0-9A-Fa-f]{2})*))?
		$
		/mx', $url, $m)) return FALSE;
	switch ($m['scheme'])
	{
	case 'https':
	case 'http':
		if ($m['userinfo']) return FALSE; // HTTP scheme does not allow userinfo.
		break;
	case 'ftps':
	case 'ftp':
		break;
	default:
		return FALSE;	// Unrecognised URI scheme. Default to FALSE.
	}
	// Validate host name conforms to DNS "dot-separated-parts".
	if ($m{'regname'}) // If host regname specified, check for DNS conformance.
	{
		if (!preg_match('/# HTTP DNS host name.
			^					   # Anchor to beginning of string.
			(?!.{256})			   # Overall host length is less than 256 chars.
			(?:					   # Group dot separated host part alternatives.
			  [0-9A-Za-z]\.		   # Either a single alphanum followed by dot
			|					   # or... part has more than one char (63 chars max).
			  [0-9A-Za-z]		   # Part first char is alphanum (no dash).
			  [\-0-9A-Za-z]{0,61}  # Internal chars are alphanum plus dash.
			  [0-9A-Za-z]		   # Part last char is alphanum (no dash).
			  \.				   # Each part followed by literal dot.
			)*					   # One or more parts before top level domain.
			(?:					   # Explicitly specify top level domains.
			  com|edu|gov|int|mil|net|org|biz|
			  info|name|pro|aero|coop|museum|
			  asia|cat|jobs|mobi|tel|travel|
			  [A-Za-z]{2})		   # Country codes are exqactly two alpha chars.
			$					   # Anchor to end of string.
			/ix', $m['host'])) return FALSE;
	}
	$m['url'] = $url;
	for ($i = 0; isset($m[$i]); ++$i) unset($m[$i]);
	return $m; // return TRUE == array of useful named $matches plus the valid $url.
}

//
// Replace string matching regular expression
//
// This function takes care of possibly disabled unicode properties in PCRE builds
//
function ucp_preg_replace($pattern, $replace, $subject)
{
	$replaced = preg_replace($pattern, $replace, $subject);

	// If preg_replace() returns false, this probably means unicode support is not built-in, so we need to modify the pattern a little
	if ($replaced === false)
	{
		if (is_array($pattern))
		{
			foreach ($pattern as $cur_key => $cur_pattern)
				$pattern[$cur_key] = str_replace('\p{L}\p{N}', '\w', $cur_pattern);

			$replaced = preg_replace($pattern, $replace, $subject);
		}
		else
			$replaced = preg_replace(str_replace('\p{L}\p{N}', '\w', $pattern), $replace, $subject);
	}

	return $replaced;
}

//
// Replace four-byte characters with a question mark
//
// As MySQL cannot properly handle four-byte characters with the default utf-8
// charset up until version 5.5.3 (where a special charset has to be used), they
// need to be replaced, by question marks in this case. 
//
function strip_bad_multibyte_chars($str)
{
	$result = '';
	$length = strlen($str);
	
	for ($i = 0; $i < $length; $i++)
	{
		// Replace four-byte characters (11110www 10zzzzzz 10yyyyyy 10xxxxxx)
		$ord = ord($str[$i]);
		if ($ord >= 240 && $ord <= 244)
		{
			$result .= '?';
			$i += 3;
		}
		else
		{
			$result .= $str[$i];
		}
	}
	
	return $result;
}

//
// Check whether a file/folder is writable.
//
// This function also works on Windows Server where ACLs seem to be ignored.
//
function forum_is_writable($path)
{
	if (is_dir($path))
	{
		$path = rtrim($path, '/').'/';
		return forum_is_writable($path.uniqid(mt_rand()).'.tmp');
	}

	// Check temporary file for read/write capabilities
	$rm = file_exists($path);
	$f = @fopen($path, 'a');

	if ($f === false)
		return false;

	fclose($f);

	if (!$rm)
		@unlink($path);

	return true;
}


// DEBUG FUNCTIONS BELOW

//
// Display executed queries (if enabled)
//
function display_saved_queries()
{
	global $db, $lang_common;

	// Get the queries so that we can print them out
	$saved_queries = $db->get_saved_queries();

?>

<div id="debug" class="blocktable">
	<h2><span><?php echo $lang_common['Debug table'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col"><?php echo $lang_common['Query times'] ?></th>
					<th class="tcr" scope="col"><?php echo $lang_common['Query'] ?></th>
				</tr>
			</thead>
			<tbody>
<?php

	$query_time_total = 0.0;
	foreach ($saved_queries as $cur_query)
	{
		$query_time_total += $cur_query[1];

?>
				<tr>
					<td class="tcl"><?php echo ($cur_query[1] != 0) ? $cur_query[1] : '&#160;' ?></td>
					<td class="tcr"><?php echo pun_htmlspecialchars($cur_query[0]) ?></td>
				</tr>
<?php

	}

?>
				<tr>
					<td class="tcl" colspan="2"><?php printf($lang_common['Total query time'], $query_time_total.' s') ?></td>
				</tr>
			</tbody>
			</table>
		</div>
	</div>
</div>
<?php

}


//
// Dump contents of variable(s)
//
function dump()
{
	echo '<pre>';

	$num_args = func_num_args();

	for ($i = 0; $i < $num_args; ++$i)
	{
		print_r(func_get_arg($i));
		echo "\n\n";
	}

	echo '</pre>';
	exit;
}

//
//	SITE WEB LVRP
//

function slider()
	{
		?>
		<html>
		<head>
			<link href="functions/slider/themes/1/js-image-slider.css" rel="stylesheet" type="text/css" />
			<script src="functions/slider/themes/1/js-image-slider.js" type="text/javascript"></script>
		</head>
		<body>
		<br/>
		<div id="sliderFrame">
				<div id="ribbon"></div>
				<div id="slider">
					<img src="include/slider/images/image-slider-2.jpg" alt="" />
					<img src="include/slider/images/image-slider-3.jpg" alt="" />
					<img src="include/slider/images/image-slider-4.jpg" alt="" />
					<img src="include/slider/images/image-slider-5.jpg" />
				</div>
			</div>
			<br/>
		</body>
		</html>
		<?php
	}
	
	function isconnected()
	{
		if (!$pun_user['is_guest'])
			{return true;}
		else
			{return false;}
	}
	
	function isconnectedIG($username)
	{
		global $db;
		$result = $db->query('SELECT * FROM lvrp_users WHERE Name="'.$db->escape($username).'" ') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
		$dStats = $db->fetch_assoc($result);
		if($dStats['Connected'] >= 1)
			{return true;}
	}
	function isAdmin()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query('SELECT * FROM `lvrp_users` WHERE `Name`="'.$_SESSION['Login'].'"') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
			$dStats = $db->fetch_assoc($result);
			if($dStats['AdminLevel'] >= 4)
				{return true;}
		}
	}
	function admin_Index()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			echo '<article><div class="title">Adminitration</div><div class="new">';
			echo '<br/><h3>
			. <a href="index.php?do=admin&amp;cd=news_index">Gestion des News</a><br/>
			. <a href="index.php?do=admin&amp;cd=log_a">Logs Admin</a><br/>
			. <a href="index.php?do=admin&amp;cd=log_p">Logs Payes</a><br/>
			. <a href="index.php?do=admin&amp;cd=log_k">Logs Kick</a><br/>
			. <a href="index.php?do=admin&amp;cd=log_b">Lock Ban</a><br/>
			. <a href="index.php?do=admin&amp;cd=log_c">Logs Connexions</a><br/>
			</h3><br/>';
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function log_Admin()
	{
		global $db;
		if(!$pun_user['is_guest'] && isAdmin())
		{
			echo '<article><div class="title">Logs Admins</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_log_admins`");				
	
			while($dLog = $db->fetch_assoc($result))
				{echo '['.date('d/m/Y',$dLog['Date']).' à '.date('H:i:s',$dLog['Date']).'] '.$dLog['Value'].'<br/>';}
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function log_Kick()
	{
		global $db;
		if(!$pun_user['is_guest'] && isAdmin())
		{
			echo '<article><div class="title">Logs Kick</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_log_kick`");				
	
			while($dLog = $db->fetch_assoc($result))
				{echo '['.date('d/m/Y',$dLog['Date']).' à '.date('H:i:s',$dLog['Date']).'] '.$dLog['Name'].', kické par '.$dLog['KickedBy'].', raison : '.$dLog['Reason'].' (IP : '.$dLog['Ip'].')<br/>';}
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function log_Pay()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			echo '<article><div class="title">Logs Payes</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_log_pay`");				
	
			while($dLog = $db->fetch_assoc($result))
			{
				$result = $db->query("SELECT Name FROM `lvrp_users` WHERE id='".$dLog['SQLid']."' LIMIT 1");
				$dPlayer = $db->fetch_assoc($result);
				echo '['.date('d/m/Y',$dLog['Date']).' à '.date('H:i:s',$dLog['Date']).'] '.$dPlayer['Name'].', Somme : $'.$dLog['Somme'].', Raison : '.$dLog['Reason'].' (IP : '.$dLog['Ip'].')<br/>';
			}
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function log_Connect()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			echo '<article><div class="title">Logs Connexions</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_log_connect`");				
	
			while($dLog = $db->fetch_assoc($result))
			{
				$result = $db->query("SELECT Name FROM `lvrp_users` WHERE id='".$dLog['SQLid']."' LIMIT 1");
				$dPlayer = $db->fetch_assoc($result);
				echo '['.date('d/m/Y',$dLog['Date']).' à '.date('H:i:s',$dLog['Date']).'] '.$dPlayer['Name'].' (IP : '.$dLog['Ip'].')<br/>';
			}
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function log_Ban()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			echo '<article><div class="title">Logs Bans</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_users_bans`");				
	
			while($dLog = $db->fetch_assoc($result))
			{
				$result = $db->query("SELECT Name FROM `lvrp_users` WHERE id='".$dLog['SQLid']."' LIMIT 1");
				$dPlayer = $db->fetch_assoc($result);
				echo '['.date('d/m/Y',$dLog['Date']).' à '.date('H:i:s',$dLog['Date']).'] '.$dPlayer['Name'].' banni par '.$dLog['BannedBy'].', raison : '.$dLog['Reason'].' (IP : '.$dLog['Ip'].')<br/>';
			}
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function news_Index()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			echo '<article><div class="title">Gestion News</div><div class="new"><br/>';
			$result = $db->query("SELECT * FROM `lvrp_site_news` ORDER BY `id`") or error('Impossible de charger les news', __FILE__, __LINE__, $db->error());				
			$news = '<center>';
			while($dNews = $db->fetch_assoc($result))
			{
				$news .= '<tr>
					<td>'.$dNews['Title'].'</td>';
				if(strlen($dNews['Contenu']) > 32)
					{$subnew = substr($dNews['Contenu'], 0 , 32);}
				else
					{$subnew = $dNews['Contenu'];}
				$news .= '<td>'.$subnew.' [...]<br /></td>
					<td>'.$dNews['Autor'].'</td>
					<td>'.date('d/m/Y',$dNews['Date']).'</td>
					<td><a href="index.php?do=admin&amp;cd=news_edit&amp;id='.$dNews['id'].'">Editer</a></td>
					<td><a href="index.php?do=admin&amp;cd=news_sup&amp;id='.$dNews['id'].'">Supr.</a></td>
				</tr></center>';
				
			}
			echo '
				<center>
				<table id="container" BORDER=1 CELLPADDING=0 CELLSPACING=0>
					<tr><b>
						<td>Titre :</td>
						<td>Contenue :</td>
						<td>Auteur :</td>
						<td>Date :</td>
						<td></td>
						<td></td>
					</b></tr>
				
					'.$news.'
				</table><div class="date"><a href="index.php?do=admin&amp;cd=news_create">Créer une nouvelle news ...</a></div><br></center></center>';
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function news_Delete($id)
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			$result = $db->query("DELETE FROM `lvrp_site_news` WHERE `id` = '".$id."'")or error('Impossible de supprimer la news', __FILE__, __LINE__, $db->error());	
			header("Location: index.php?do=admin&cd=news_index");
		}
		else
			{header("Location: index.php");}
	}
	function news_Edit($id)
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_site_news` WHERE `id` = '".$id."'");
			$dNews = $db->fetch_assoc($result);
			echo '<article><div class="title">News Edition</div><div class="new"><br/>';
			echo '
			<form name="form1" method="post" action="index.php?do=admin&cd=news_edit_2&id='.$id.'">
			<center>
				Title : <br/><input type="text" name="title" value="'.$dNews['Title'].'" size="32" maxlength="64" /><br/>
				Contenue : <br/><textarea id="contenu" name="contenu" rows="12" cols="60">'.$dNews['Contenu'].'</textarea> <br/>
				* L\'utilisation de balise HTML est activé.  
				<div class="buton">
					<input class="buton_g" type="submit" name="submit" id="send" value="Enregistrer" />
				</div>
				<br/>
			</center>
			</form>';
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function news_EditSave($id)
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			$result = $db->query("UPDATE `lvrp_site_news` SET Title='".$_POST['title']."', Contenu='".$_POST['contenu']."' WHERE `id` = '".$id."'");
			header("Location: index.php?do=admin&cd=news_index");
		}
		else
			{header("Location: index.php");}
	}
	function news_Create()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			echo '<article><div class="title">News Edition</div><div class="new"><br/>';
			echo '
			<form name="form1" method="post" action="index.php?do=admin&cd=news_create_2">
			<center>
				Title : <br/><input type="text" name="title" size="32" maxlength="64" /><br/>
				Contenue : <br/><textarea id="contenu" name="contenu" rows="12" cols="60"></textarea> <br/>
				* L\'utilisation de balise HTML est activé.  
				<div class="buton">
					<input class="buton_g" type="submit" name="submit" id="send" value="Enregistrer" />
				</div>
				<br/>
			</center>
			</form>';
			echo '</div></article>';
		}
		else
			{header("Location: index.php");}
	}
	function news_CreateSave()
	{
		if(!$pun_user['is_guest'] && isAdmin())
		{
			global $db;
			$result = $db->query("INSERT INTO `lvrp_site_news` SET Title='".$db->escape($_POST['title'])."', Contenu='".$db->escape($_POST['contenu'])."', Date=UNIX_TIMESTAMP(), Autor='".$db->escape($_SESSION['Login'])."' ");
			header("Location: index.php?do=admin&cd=news_index");
		}
		else
			{header("Location: index.php");}
	}
	function get_CarName($model)
	{
		if($model==400) return 'Landstalker';
		elseif($model==401) return 'Bravura';
		elseif($model==402) return 'Buffalo';
		elseif($model==403) return 'Linerunner';
		elseif($model==404) return 'Perenniel';
		elseif($model==405) return 'Sentinel';
		elseif($model==406) return 'Dumper';
		elseif($model==407) return 'Firetruck';
		elseif($model==408) return 'Trashmaster';
		elseif($model==409) return 'Stretch';
		elseif($model==410) return 'Manana';
		elseif($model==411) return 'Infernus';
		elseif($model==412) return 'Voodoo';
		elseif($model==413) return 'Pony';
		elseif($model==414) return 'Mule';
		elseif($model==415) return 'Cheetah';
		elseif($model==416) return 'Ambulance';
		elseif($model==417) return 'Leviathan';
		elseif($model==418) return 'Moonbeam';
		elseif($model==419) return 'Esperanto';
		elseif($model==420) return 'Taxi';
		elseif($model==421) return 'Washington';
		elseif($model==422) return 'Bobcat';
		elseif($model==423) return 'Mr Whoopee';
		elseif($model==424) return 'BF Injection';
		elseif($model==425) return 'Hunter';
		elseif($model==426) return 'Premier';
		elseif($model==427) return 'Enforcer';
		elseif($model==428) return 'Securicar';
		elseif($model==429) return 'Banshee';
		elseif($model==430) return 'Predator';
		elseif($model==431) return 'Bus';
		elseif($model==432) return 'Rhino';
		elseif($model==433) return 'Barracks';
		elseif($model==434) return 'Hotknife';
		elseif($model==435) return 'Trailers';
		elseif($model==436) return 'Previon';
		elseif($model==437) return 'Coach';
		elseif($model==438) return 'Cabbie';
		elseif($model==439) return 'Stallion';
		elseif($model==440) return 'Rumpo';
		elseif($model==441) return 'RC Bandit';
		elseif($model==442) return 'Romero';
		elseif($model==443) return 'Packer';
		elseif($model==444) return 'Monster';
		elseif($model==445) return 'Admiral';
		elseif($model==446) return 'Squallo';
		elseif($model==447) return 'Seasparrow';
		elseif($model==448) return 'Pizzaboy';
		elseif($model==449) return 'Tram';
		elseif($model==450) return 'Trailers';
		elseif($model==451) return 'Turismo';
		elseif($model==452) return 'Speeder';
		elseif($model==453) return 'Reefer';
		elseif($model==454) return 'Tropic';
		elseif($model==455) return 'Flatbed';
		elseif($model==456) return 'Yankee';
		elseif($model==457) return 'Caddy';
		elseif($model==458) return 'Solair';
		elseif($model==459) return 'Topfun Van';
		elseif($model==460) return 'Skimmer';
		elseif($model==461) return 'PCJ-600';
		elseif($model==462) return 'Faggio';
		elseif($model==463) return 'Freeway';
		elseif($model==464) return 'RC Baron';
		elseif($model==465) return 'RC Raider';
		elseif($model==466) return 'Glendale';
		elseif($model==467) return 'Oceanic';
		elseif($model==468) return 'Sanchez';
		elseif($model==469) return 'Sparrow';
		elseif($model==470) return 'Patriot';
		elseif($model==471) return 'Quad';
		elseif($model==472) return 'Coastguard';
		elseif($model==473) return 'Dinghy';
		elseif($model==474) return 'Hermes';
		elseif($model==475) return 'Sabre';
		elseif($model==476) return 'Rustler';
		elseif($model==477) return 'ZR-350';
		elseif($model==478) return 'Walton';
		elseif($model==479) return 'Regina';
		elseif($model==480) return 'Comet';
		elseif($model==481) return 'BMX';
		elseif($model==482) return 'Burrito';
		elseif($model==483) return 'Camper';
		elseif($model==484) return 'Marquis';
		elseif($model==485) return 'Baggage';
		elseif($model==486) return 'Dozer';
		elseif($model==487) return 'Maverick';
		elseif($model==488) return 'SAN News Maverick';
		elseif($model==489) return 'Rancher';
		elseif($model==490) return 'FBI Rancher';
		elseif($model==412) return 'Virgo';
		elseif($model==492) return 'Greenwood';
		elseif($model==493) return 'Jetmax';
		elseif($model==494) return 'Hotring Racer';
		elseif($model==495) return 'Sandking';
		elseif($model==496) return 'Blista';
		elseif($model==497) return 'Police Maverick';
		elseif($model==498) return 'Boxville';
		elseif($model==499) return 'Benson';
		elseif($model==500) return 'Mesa';
		elseif($model==501) return 'RC Goblin';
		elseif($model==502) return 'Hotring Racer';
		elseif($model==503) return 'Hotring Racer';
		elseif($model==504) return 'Bloodring Banger';
		elseif($model==505) return 'Rancher';
		elseif($model==506) return 'Super GT';
		elseif($model==507) return 'Elegant';
		elseif($model==508) return 'Journey';
		elseif($model==509) return 'Bike';
		elseif($model==510) return 'Mountain Bike';
		elseif($model==511) return 'Beagle';
		elseif($model==512) return 'Cropduster';
		elseif($model==513) return 'Stuntplane';
		elseif($model==514) return 'Tanker';
		elseif($model==515) return 'Roadtrain';
		elseif($model==516) return 'Nebula';
		elseif($model==517) return 'Majestic';
		elseif($model==518) return 'Buccaneer';
		elseif($model==519) return 'Shamal';
		elseif($model==520) return 'Hydra';
		elseif($model==521) return 'FCR-900';
		elseif($model==522) return 'NRG-500';
		elseif($model==523) return 'HPV1000';
		elseif($model==524) return 'Cement Truck';
		elseif($model==525) return 'Towtruck';
		elseif($model==526) return 'Fortune';
		elseif($model==527) return 'Cadrona';
		elseif($model==528) return 'FBI Truck';
		elseif($model==529) return 'Willard';
		elseif($model==530) return 'Forklift';
		elseif($model==531) return 'Tractor';
		elseif($model==532) return 'Combine Harvester';
		elseif($model==533) return 'Feltzer';
		elseif($model==534) return 'Remington';
		elseif($model==535) return 'Slamvan';
		elseif($model==536) return 'Blade';
		elseif($model==537) return 'Freight';
		elseif($model==538) return 'Brownstreak';
		elseif($model==539) return 'Vortex';
		elseif($model==540) return 'Vincent';
		elseif($model==541) return 'Bullet';
		elseif($model==542) return 'Clover';
		elseif($model==543) return 'Sadler';
		elseif($model==544) return 'Firetruck';
		elseif($model==545) return 'Hustler';
		elseif($model==546) return 'Intruder';
		elseif($model==547) return 'Primo';
		elseif($model==548) return 'Cargobob';
		elseif($model==549) return 'Tampa';
		elseif($model==550) return 'Sunrise';
		elseif($model==551) return 'Merit';
		elseif($model==552) return 'Utility Van';
		elseif($model==553) return 'Nevada';
		elseif($model==554) return 'Yosemite';
		elseif($model==555) return 'Windsor';
		elseif($model==556) return 'Monster';
		elseif($model==557) return 'Monster';
		elseif($model==558) return 'Uranus';
		elseif($model==559) return 'Jester';
		elseif($model==560) return 'Sultan';
		elseif($model==561) return 'Stratum';
		elseif($model==562) return 'Elegy';
		elseif($model==563) return 'Raindance';
		elseif($model==564) return 'RC Tiger';
		elseif($model==565) return 'Flash';
		elseif($model==566) return 'Tahoma';
		elseif($model==567) return 'Savanna';
		elseif($model==568) return 'Bandito';
		elseif($model==569) return 'Trailers';
		elseif($model==570) return 'Trailers';
		elseif($model==571) return 'Kart';
		elseif($model==572) return 'Mower';
		elseif($model==573) return 'Dune';
		elseif($model==574) return 'Sweeper';
		elseif($model==575) return 'Broadway';
		elseif($model==576) return 'Tornado';
		elseif($model==577) return 'AT400';
		elseif($model==578) return 'DFT-30';
		elseif($model==579) return 'Huntley';
		elseif($model==580) return 'Stafford';
		elseif($model==581) return 'BF-400';
		elseif($model==582) return 'Newsvan';
		elseif($model==583) return 'Tug';
		elseif($model==584) return 'Trailer';
		elseif($model==585) return 'Emperor';
		elseif($model==586) return 'Wayfarer';
		elseif($model==587) return 'Euros';
		elseif($model==588) return 'Hotdog';
		elseif($model==589) return 'Club';
		elseif($model==590) return 'Trailer';
		elseif($model==591) return 'Trailer';
		elseif($model==592) return 'Andromada';
		elseif($model==593) return 'Dodo';
		elseif($model==594) return 'RC Cam';
		elseif($model==595) return 'Launch';
		elseif($model==596) return 'Police Car';
		elseif($model==597) return 'Police Car';
		elseif($model==598) return 'Police Car';
		elseif($model==599) return 'Police Ranger';
		elseif($model==601) return 'Picador';
		elseif($model==601) return 'S.W.A.T';
		elseif($model==602) return 'Alpha';
		elseif($model==603) return 'Phoenix';
		elseif($model==604) return 'Glendale Shit';
		elseif($model==605) return 'Sadler Shit';
		elseif($model==606) return 'Baggage';
		elseif($model==607) return 'Baggage';
		elseif($model==608) return 'Tug Stairs';
		elseif($model==609) return 'Boxville';
		elseif($model==610) return 'Farm Trailer';
		elseif($model==611) return 'Utility Trailer';
	}
	
	function get_FacName($id)
	{
		if($id==1) return 'LSPD';
		elseif($id==2) return 'F.B.I';
		elseif($id==3) return 'LSMD';
		elseif($id==4) return 'Gouvernement';
		elseif($id==5) return 'LSFD';
		elseif($id==6) return 'San News SA';
		elseif($id==7) return 'Biker\'s';
		elseif($id==8) return 'Racer\'Z';
		elseif($id==9) return 'Mecanicien';
		elseif($id==10) return 'Trans 4 SA';
		elseif($id>=200)
		{
			$sqlid = $id-199;
			$result = mysql_query("SELECT * FROM `lvrp_factions_illegals` WHERE `id`=".$sqlid."");
			$dFac = mysql_fetch_array($result);
			return $dFac['Name'];
		}
	}
	
	function get_FacRank($id,$rank)
	{
		if($id==1)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_police` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			elseif($rank==7) return $dFac['Rank7'];
			else return $dFac['Rank1'];
		}
		if($id==2)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_fbi` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			elseif($rank==7) return $dFac['Rank7'];
			else return $dFac['Rank1'];
		}
		if($id==3)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_medic` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			else return $dFac['Rank1'];
		}
		if($id==4)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_gouvernement` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			else return $dFac['Rank1'];
		}
		if($id==5)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_pompier` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			else return $dFac['Rank1'];
		}
		if($id==9)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_mecano` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			else return $dFac['Rank1'];
		}
		if($id>=200)
		{
			$sqlid = $id-199;
			$result = mysql_query("SELECT * FROM `lvrp_factions_illegals` WHERE `id`=".$sqlid."");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Rank2'];
			elseif($rank==3) return $dFac['Rank3'];
			elseif($rank==4) return $dFac['Rank4'];
			elseif($rank==5) return $dFac['Rank5'];
			elseif($rank==6) return $dFac['Rank6'];
			else return $dFac['Rank1'];
		}
	}
	function get_FacSkin($id,$rank)
	{
		global $db;
		if($id==1)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_police` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			elseif($rank==7) return $dFac['Skin7'];
			else return $dFac['Skin1'];
		}
		if($id==2)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_fbi` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			elseif($rank==7) return $dFac['Skin7'];
			else return $dFac['Skin1'];
		}
		if($id==3)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_medic` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			else return $dFac['Skin1'];
		}
		if($id==4)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_gouvernement` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			else return $dFac['Skin1'];
		}
		if($id==5)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_pompier` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			else return $dFac['Skin1'];
		}
		if($id==9)
		{
			$result = mysql_query("SELECT * FROM `lvrp_factions_mecano` WHERE `id`=1");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Skin1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			else return $dFac['Skin1'];
		}
		if($id>=200)
		{
			$sqlid = $id-199;
			$result = mysql_query("SELECT * FROM `lvrp_factions_illegals` WHERE `id`=".$sqlid."");
			$dFac = mysql_fetch_array($result);
			if($rank==1) return $dFac['Rank1'];
			elseif($rank==2) return $dFac['Skin2'];
			elseif($rank==3) return $dFac['Skin3'];
			elseif($rank==4) return $dFac['Skin4'];
			elseif($rank==5) return $dFac['Skin5'];
			elseif($rank==6) return $dFac['Skin6'];
			else return $dFac['Rank1'];
		}
	}
	
	function get_JobName($id)
	{
		if($id==1) return 'Livreur de pizza';
		elseif($id==2) return 'Fermier';
		elseif($id==3) return 'Balayeur de rues';
		elseif($id==4) return 'Eboueur';
		elseif($id==5) return 'Ouvrier';
		elseif($id==6) return 'Pilote de Ligne';
		elseif($id==7) return 'Facteur';
		elseif($id==8) return 'Pêcheur';
		elseif($id==9) return 'Transporteur de fond';
		elseif($id==10) return 'Livreur marchandises';
		elseif($id==11) return 'Pilote de train';
		elseif($id==12) return 'Chauffeur de bus';
		elseif($id==13) return 'Chauffeur de taxi';
		else return 'Aucun';
	}
	
	function get_LangName($id)
	{
		if($id==1) return 'Japonais';
		elseif($id==2) return 'Espagnol';
		elseif($id==3) return 'Russe';
		elseif($id==4) return 'Arabe';
		elseif($id==5) return 'Italien';
		elseif($id==6) return 'Allemand';
		elseif($id==7) return 'Anglais';
		elseif($id==8) return 'Chinois';
		elseif($id==9) return 'Portugais';
		elseif($id==10) return 'Turc';
		elseif($id==11) return 'Antillais';
		elseif($id==12) return 'Mexiquain';
		elseif($id==13) return 'Créole';
		elseif($id==14) return 'Jamaicain';
		elseif($id==15) return 'Coréen';
		elseif($id==16) return 'Cantonais';
		elseif($id==17) return 'Ukrainien';
		else return 'Aucune';
		
	}
	
	function get_WepName($id)
	{
		if($id==1) return 'Point Américain';
		elseif($id==2) return 'Club de Golf';
		elseif($id==3) return 'Matraque';
		elseif($id==4) return 'Couteau';
		elseif($id==5) return 'Batte';
		elseif($id==6) return 'Pelle';
		elseif($id==7) return 'Katana';
		elseif($id==9) return 'Pelle';
		elseif($id==10 || $id==11 || $id==12 || $id==13) return 'God';
		elseif($id==14) return 'Bouqette de fleures';
		elseif($id==15) return 'Pied de biche';
		elseif($id==17) return 'Grenade Lacrymogène';
		elseif($id==18) return 'Cocktail Molotov';
		elseif($id==22) return 'Colt .45';
		elseif($id==23) return 'Colt .45 Silencieux';
		elseif($id==25) return 'ShotGun';
		elseif($id==27) return 'Spa 12';
		elseif($id==28) return 'Uzi';
		elseif($id==29) return 'Mp5';
		elseif($id==30) return 'Ak47';
		elseif($id==31) return 'M41';
		elseif($id==32) return 'Tec 9';
		elseif($id==33) return 'Rifle';
		elseif($id==34) return 'Sniper';
		elseif($id==41) return 'Bombe Lacrymogène';
		elseif($id==42) return 'Extincteur';
		elseif($id==43) return 'Appareil Photo';
		elseif($id==46) return 'Parachute';
	}
	function check_Name($name)
	{
		$ValideName=true;
		if(strlen($name) < 6 || strlen($name) > 24) $ValideName=false;
		if(strpos($name,'_') == false || strpos($name,' ') == true) $ValideName=false;
		if(strpos($name,'²') == true || strpos($name,',') == true) $ValideName=false;
		if(strpos($name,';') == true || strpos($name,'!') == true) $ValideName=false;
		if(strpos($name,'?') == true || strpos($name,'.') == true) $ValideName=false;
		if(strpos($name,'§') == true || strpos($name,'+') == true) $ValideName=false;
		if(strpos($name,'=') == true || strpos($name,')') == true) $ValideName=false;
		if(strpos($name,'(') == true || strpos($name,'/') == true) $ValideName=false;
		if(strpos($name,'&') == true || strpos($name,'@') == true) $ValideName=false;
		if(strpos($name,'~') == true || strpos($name,'ç') == true) $ValideName=false;
		if(strpos($name,'}') == true || strpos($name,'{') == true) $ValideName=false;
		if(strpos($name,'ô') == true || strpos($name,'^') == true) $ValideName=false;
		if(strpos($name,'€') == true || strpos($name,'$') == true) $ValideName=false;
		if(strpos($name,'%') == true || strpos($name,'¤') == true) $ValideName=false;
		if(strpos($name,'*') == true || strpos($name,'0') == true) $ValideName=false;
		if(strpos($name,'1') == true || strpos($name,'2') == true) $ValideName=false;
		if(strpos($name,'3') == true || strpos($name,'4') == true) $ValideName=false;
		if(strpos($name,'5') == true || strpos($name,'6') == true) $ValideName=false;
		if(strpos($name,'7') == true || strpos($name,'8') == true) $ValideName=false;
		if(strpos($name,'9') == true || strpos($name,'#') == true) $ValideName=false;
		if(strpos($name,'[') == true || strpos($name,']') == true) $ValideName=false;
		if(strpos($name,'|') == true || strpos($name,'-') == true) $ValideName=false;
		if(strpos($name,'`') == true || strpos($name,'è') == true) $ValideName=false;
		if(strpos($name,'à') == true || strpos($name,'°') == true) $ValideName=false;
		if(strpos($name,'µ') == true || strpos($name,'£') == true) $ValideName=false;
		if(strpos($name,'ù') == true || strpos($name,':') == true) $ValideName=false;
		if(strpos($name,'<') == true || strpos($name,'>') == true) $ValideName=false;
		if(strpos($name,'é') == true || strpos($name,'ê') == true) $ValideName=false;
		if(strpos($name,'â') == true || strpos($name,'û') == true) $ValideName=false;
		if(strpos($name,'î') == true || strpos($name,'ä') == true) $ValideName=false;
		if(strpos($name,'ë') == true || strpos($name,'ï') == true) $ValideName=false;
		if(strpos($name,'ü') == true || strpos($name,'ö') == true) $ValideName=false;
		if(strpos($name,'ÿ') == true || strpos($name,'ã') == true) $ValideName=false;
		if(strpos($name,'ñ') == true || strpos($name,'õ') == true) $ValideName=false;
		if(strpos($name,'tamere') == true || strpos($name,'û') == true) $ValideName=false;
		if(strpos($name,'â') == true || strpos($name,'û') == true) $ValideName=false;
		
		if($ValideName){return true;}
		return false;
	}
	function new_Show()
	{
		//echo '<article><embed src="http://www.youtube.com/v/AfaghFE0M_U?version=3&amp;hl=fr_FR" type="application/x-shockwave-flash" width="650" height="400" allowscriptaccess="always" allowfullscreen="true"></embed></article>';
		global $db;
		$result = $db->query('SELECT * FROM lvrp_site_news ORDER BY `id` DESC LIMIT 0,3') or error('Unable to fetch news informations', __FILE__, __LINE__, $db->error());
		while($dNews = $db->fetch_assoc($result))
		{ 
			echo '<article><div class="title">'.$dNews['Title'].'</div>
			<div class="new">
			<b>'.$dNews['Contenu'].'
			<div class="date"><br/>le '.date('d/m/Y',$dNews['Date']).', par '.$dNews['Autor'].'</b></div>
			</div><br/></article>';
		}
	}
	
	function profil_Biens()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			
			echo '<article><div class="title">Profil</div><div class="new">';
			
			echo '<div style="text-align: center"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=3"> Faction/Job </a> | ',"\n"
                . '<b> Biens </b> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6""> Inventaire</a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></b></div><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Véhicule(s)</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				
				if($dStats['Car1'] == -1 && $dStats['Car2'] == -1 & $dStats['Car3'] == -1 & $dStats['Car4'] == -1 & $dStats['Car5'] == -1 & $dStats['Car6'] == -1)
					{echo '<li>Vous n\'avez pas de voiture</li>';}
					
				if($dStats['Car1'] != -1) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car1']."");
					$dCar1 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 1 :</b> ID :'.$dStats['Car1'].' - Model : '.get_CarName($dCar1['Model']).'</li>';
				}
				if($dStats['Car2'] != -1) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car2']."");
					$dCar2 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 2 :</b> ID :'.$dStats['Car2'].' - Model : '.get_CarName($dCar2['Model']).'</li>';
				}
				if($dStats['Car3'] != -1) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car3']."");
					$dCar3 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 3 :</b> ID :'.$dStats['Car3'].' - Model : '.get_CarName($dCar3['Model']).'</li>';
				}
				if($dStats['Car4'] != -1 && $dStats['CarUnLock4']) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car4']."");
					$dCar4 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 4 :</b> ID :'.$dStats['Car4'].' - Model : '.get_CarName($dCar4['Model']).'</li>';
				}
				if($dStats['Car5'] != -1 && $dStats['CarUnLock5']) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car5']."");
					$dCar5 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 5 :</b> ID :'.$dStats['Car5'].' - Model : '.get_CarName($dCar5['Model']).'</li>';
				}
				if($dStats['Car6'] != -1 && $dStats['CarUnLock6']) 
				{
					$result = $db->query("SELECT * FROM `lvrp_server_cars` WHERE `id`=".$dStats['Car6']."");
					$dCar6 = $db->fetch_assoc($result);
					echo '<li><b>Véhicule slot 6 :</b> ID :'.$dStats['Car6'].' - Model : '.get_CarName($dCar6['Model']).'</li>';
				}
            echo '</ul>',"\n"
                . '</td></table><br />',"\n";
			
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Biz</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats['Bizz1'] == -1 && $dStats['Bizz2'] == -1 & $dStats['Bizz3'] == -1)
					echo ('<li><i>Vous n\'avez pas de biz</i></li>');
				if($dStats['Bizz1'] != -1) 
				{
					if($dStats['Bizz1'] >= 1000)
					{
						$biz1=$dStats['Bizz1']-999;
						$result = $db->query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz1."");
						$dbizz1 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 1 :</b> ID : '.$dStats['Bizz1'].' - Nom : '.$dbizz1['Message'].'</li>');
					}
					else
					{
						$biz1=$dStats['Bizz1']+1;
						$result = $db->query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz1."");
						$dbizz1 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 1 :</b> ID : '.$dStats['Bizz1'].' - Nom : '.$dbizz1['Message'].'</li>');
					}
				}
				if($dStats['Bizz2'] != -1) 
				{
					if($dStats['Bizz2'] >= 1000)
					{
						$biz2=$dStats['Bizz2']-999;
						$result = $db->query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz2."");
						$dbizz2 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 2 :</b> ID : '.$dStats['Bizz2'].' - Nom : '.$dbizz2['Message'].'</li>');
					}
					else
					{
						$biz2=$dStats['Bizz2']+1;
						$result = $db->query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz2."");
						$dbizz2 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 2 :</b> ID : '.$dStats['Bizz2'].' - Nom : '.$dbizz2['Message'].'</li>');
					}
				}
				if($dStats['Bizz3'] != -1) 
				{
					if($dStats['Bizz3'] >= 1000)
					{
						$biz3=$dStats['Bizz3']-999;
						$result = $db->query("SELECT * FROM `lvrp_server_uniquebizz` WHERE `id`=".$biz3."");
						$dbizz3 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 3 :</b> ID : '.$dStats['Bizz3'].' - Nom : '.$dbizz3['Message'].'</li>');
					}
					else
					{
						$biz3=$dStats['Bizz3']+1;
						$result = $db->query("SELECT * FROM `lvrp_server_bizz` WHERE `id`=".$biz3."");
						$dbizz3 = $db->fetch_assoc($result);
						echo ('<li><b>Bizz slot 3 :</b> ID : '.$dStats['Bizz3'].' - Nom : '.$dbizz3['Message'].'</li>');
					}
				}
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Maison(s)</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats['House1'] == -1 && $dStats['House2'] == -1 & $dStats['House3'] == -1)
					echo ('<li><i>Vous n\'avez pas de maison</i></li>');
				if($dStats['House1'] != -1)
				{
					$house1=$dStats['House1']+1;
					$result = $db->query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house1."");
					$dhouse1 = $db->fetch_assoc($result);
					echo ('<li><b>Maison slot 1 :</b> ID : '.$dStats['House1'].' - Info : '.$dhouse1['Message'].'</li>');
				}
				if($dStats['House2'] != -1)
				{
					$house2=$dStats['House2']+1;
					$result = $db->query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house2."");
					$dhouse2 = $db->fetch_assoc($result);
					echo ('<li><b>Maison slot 2 :</b> ID : '.$dStats['House2'].' - Info : '.$dhouse2['Message'].'</li>');
				}
				if($dStats['House3'] != -1)
				{
					$house3=$dStats['House3']+1;
					$result = $db->query("SELECT * FROM `lvrp_server_houses` WHERE `id`=".$house3."");
					$dhouse3 = $db->fetch_assoc($result);
					echo ('<li><b>Maison slot 3 :</b> ID : '.$dStats['House3'].' - Info : '.$dhouse3['Message'].'</li>');
				}
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Garage(s)</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats['Garage1'] == -1 && $dStats['Garage2'] == -1 & $dStats['Garage3'] == -1)
					echo ('<li><i>Vous n\'avez pas de garage</i></li>');
				if($dStats['Garage1'] != -1) echo ('<li><b>Garage slot 1 : ID : '.$dStats['Garage1'].'</li>');
				if($dStats['Garage2'] != -1) echo ('<li><b>Garage slot 2 : ID : '.$dStats['Garage2'].'</li>');
				if($dStats['Garage3'] != -1) echo ('<li><b>Garage slot 3 : ID : '.$dStats['Garage3'].'</li>');
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	
	function profil_Casier()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$slqid = $db->fetch_assoc($result);
			
			$result = $db->query("SELECT * FROM `lvrp_users_casiers` WHERE `SQLid`='".$slqid['id']."'");
			$dStats = $db->fetch_assoc($result);
			
			echo '<article><div class="title">Profil</div><div class="new">';
			
			echo '<div style="text-align: center"><br /><big><b>' . $slqid['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=3"> Faction/Job </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<b> Casier </b> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6""> Inventaire</a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></b></div><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Crime actuelle</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats)
				{
					echo '<li><b>Nom du crime :</b> '.$dStats['Crime1'].'</li>
						  <li><b>Victime :</b> '.$dStats['Victim'].'</li>
						  <li><b>Témoin :</b> '.$dStats['Witness'].'</li>';
				}
				else
					{echo '<li><i>Vous n\'avez aucun crime en ce moment.</i></li>';}
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Casier judiciaire</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats)
				{
					echo '<li><b>Crime(s) comis au total :</b> '.$dStats['Crimes'].'</li>
					      <li><b>Nombre de fois arrété :</b> '.$dStats['Arrested'].'</li>
					      <li><b>Ancien(s) crime(s) :</b></li>
						  <li>'.$dStats['Crime2'].'</b></li>
						  <li>'.$dStats['Crime3'].'</b></li>
						  <li>'.$dStats['Crime4'].'</b></li>
						  <li>'.$dStats['Crime5'].'</b></li>';
				}
				else
					{echo '<li><i>Vous n\'avez pas de casier.</i></li>';}
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
			
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	
	function profil_Fac()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);

			if($dStats['Leader'] == $dStats['Member']) $dStats['Leader'] = 'Oui (<a href="index.php?do=faction&id='.$dStats['Leader'].'">Gestion</a>)';
			else $dStats['Leader']="Non";
			
			echo '<article><div class="title">Profil</div><div class="new">';
			
			echo '<div style="text-align: center"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<b>Faction/Job </b> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6""> Inventaire</a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></b></div><br />',"\n";
				
			if($dStats['Member'] > 0)
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Faction</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><b>Faction :</b> ' . get_FacName($dStats['Member']).'</li>',"\n"
					. '<li><b>Leader :</b> ' . $dStats['Leader'] . '</li>',"\n"
					. '<li><b>Rang :</b> ' .get_FacRank($dStats['Member'],$dStats['Rank']). ' ('.$dStats['Rank'].')</li>',"\n"
					. '<li><b>Temps de travail : </b> ' . $dStats['DutyTime'] . ' minute(s)</li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			else
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Faction</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><i>Vous ne faites parti d\'aucunes factions.</i></li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			
			if($dStats['Job'] > 0)
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Job</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><b>Job :</b> ' . get_JobName($dStats['Job']).'</li>',"\n"
					. '<li><b>Niveau :</b> ' . $dStats['JobLvl'] . '</li>',"\n"
					. '<li><b>Expérience :</b> ' .$dStats['JobExp'].'</li>',"\n"
					. '<li><b>Bonus : $</b> ' .$dStats['JobBonnus'].'</li>',"\n"
					. '<li><b>Temps de travail : </b> ' . $dStats['JobTime'] . ' minute(s)</li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			else
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Job</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><i>Vous ne faites parti d\'aucun jobs.</i></li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	
	function profil_Inv()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			echo '<article><div class="title">Profil</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<div style="text-align: center"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=3"> Faction/Job </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<b> Inventaire</a></b> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></div><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Arme(s)</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats['InvWeapon1'] == 0 && $dStats['InvWeapon2'] == 0 && $dStats['InvWeapon3'] == 0 && $dStats['InvWeapon4'] == 0 && ($dStats['InvWeapon5'] == 0 && $dStats['InvDev5'] == 1) && ($dStats['InvWeapon6'] == 0 && $dStats['InvDev6'] == 1))
					{echo '<li><i>Vous n\'avez pas d\'armes dans votre inventaire.</i></li>';}
				if($dStats['InvWeapon1'] != 0 && $dStats['InvAmmo1'] != 0) echo ('<li><b>Arme slot 1 :</b> '.get_WepName($dStats['InvWeapon1']).' ('.$dStats['InvAmmo1'].' balle(s)) </li>');
				if($dStats['InvWeapon2'] != 0 && $dStats['InvAmmo2'] != 0) echo ('<li><b>Arme slot 2 :</b> '.get_WepName($dStats['InvWeapon2']).' ('.$dStats['InvAmmo2'].' balle(s)) </li>');
				if($dStats['InvWeapon3'] != 0 && $dStats['InvAmmo3'] != 0) echo ('<li><b>Arme slot 3 :</b> '.get_WepName($dStats['InvWeapon3']).' ('.$dStats['InvAmmo3'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo4'] != 0) echo ('<li><b>Arme slot 4 :</b> '.get_WepName($dStats['InvWeapon4']).' ('.$dStats['InvAmmo4'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo5'] != 0) echo ('<li><b>Arme slot 5 :</b> '.get_WepName($dStats['InvWeapon5']).' ('.$dStats['InvAmmo5'].' balle(s)) </li>');
				if($dStats['InvWeapon4'] != 0 && $dStats['InvAmmo6'] != 0) echo ('<li><b>Arme slot 6 :</b> '.get_WepName($dStats['InvWeapon6']).' ('.$dStats['InvAmmo6'].' balle(s)) </li>');

			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Divers</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				if($dStats['Weed'] > 0) echo ('<li><b>Weed :</b> '.$dStats['Weed'].' gramme(s)</li>');
				if($dStats['SeedWeed'] > 0) echo ('<li><b>Graine(s) de weed :</b> '.$dStats['SeedWeed'].' </li>');
				if($dStats['Heroine'] > 0) echo ('<li><b>Heroïne :</b> '.$dStats['Heroine'].' gramme(s)</li>');
				if($dStats['Cocaine'] > 0) echo ('<li><b>Cocaïne :</b> '.$dStats['Cocaine'].' gramme(s)</li>');
				if($dStats['Ecstasie'] > 0) echo ('<li><b>Ecstasie :</b> '.$dStats['Ecstasie'].' gramme(s)</li>');
				if($dStats['Tabac'] > 0) echo ('<li><b>Tabac :</b> '.$dStats['Tabac'].' </li>');
				if($dStats['Leaf'] > 0) echo ('<li><b>Feuilles :</b> '.$dStats['Leaf'].' </li>');
				if($dStats['Materials'] > 0) echo ('<li><b>Matériaux :</b> '.$dStats['Materials'].' </li>');
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	
	function profil_Vip()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			echo '<article><div class="title">Profil</div><div class="new">';
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<div style="text-align: center"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=3"> Faction/Job </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6"> Inventaire</a> | ',"\n"
				. '<b> V.I.P</b></div><br />',"\n";
				
			if($dStats['DonateRank']==1) $dStats['DonateRank']="VIP Fer";
			elseif($dStats['DonateRank']==2) $dStats['DonateRank']="VIP Argent";
			elseif($dStats['DonateRank']==3) $dStats['DonateRank']="VIP Or";
			elseif($dStats['DonateRank']==4) $dStats['DonateRank']="VIP Diamant";
			else $dStats['DonateRank']="Aucun";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Informations</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				echo '<li><b>Rang V.I.P : </b> '.$dStats['DonateRank'].'</li>';
				echo '<li><b>Temps restant V.I.P : </b> '.$dStats['VipTime'].' min(s)</li>';

			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Divers</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				echo ('<li><b>Renames :</b> '.$dStats['PointsRename'].'</li>');
				echo ('<li><b>ChangeNum:</b> '.$dStats['ChangeNum'].' </li>');
				echo ('<li><b>ChangePlaque :</b> '.$dStats['ChangePlaque'].'</li>');
				if($dStats['CarUnLock4'] == 1) echo ('<li><b>Slot Véhicule 1 déverrouillé :</b> Oui</li>');
				else echo ('<li><b>Slot Véhicule 1 déverrouillé :</b> Non</li>');
				if($dStats['CarUnLock5'] == 1) echo ('<li><b>Slot Véhicule 2 déverrouillé :</b> Oui</li>');
				else echo ('<li><b>Slot Véhicule 2 déverrouillé :</b> Non</li>');
				if($dStats['CarUnLock6'] == 1) echo ('<li><b>Slot Véhicule 3 déverrouillé :</b> Oui</li>');
				else echo ('<li><b>Slot Véhicule 3 déverrouillé :</b> Non</li>');
				if($dStats['InvDev5'] == 1) echo ('<li><b>Slot Arme 1 déverrouillé :</b> Oui</li>');
				else echo ('<li><b>Slot Arme 1 déverrouillé :</b> Non</li>');
				if($dStats['InvDev6'] == 1) echo ('<li><b>Slot Arme 2 déverrouillé :</b> Oui</li>');
				else echo ('<li><b>Slot Arme 2 déverrouillé :</b> Non</li>');
			echo '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	
	function profil_Stats()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);

			if($dStats['Origin'] == '1') $dStats['Origin'] = 'Vice City';
			elseif($dStats['Origin'] == '2') $dStats['Origin'] = 'Liberty City';
			elseif($dStats['Origin'] == '3') $dStats['Origin'] = 'Chinatown';
			elseif($dStats['Origin'] == '4') $dStats['Origin'] = 'San Fierro';
			elseif($dStats['Origin'] == '5') $dStats['Origin'] = 'Las Venturas';
			
			if($dStats['CarLic'] == '0') $dStats['CarLic'] = 'Non acquis';
			elseif($dStats['CarLic'] == '1') $dStats['CarLic'] = 'Acquis';
			
			if($dStats['FlyLic'] == '0') $dStats['FlyLic'] = 'Non acquis';
			elseif($dStats['FlyLic'] == '1') $dStats['FlyLic'] = 'Acquis';
			
			if($dStats['BoatLic'] == '0') $dStats['BoatLic'] = 'Non acquis';
			elseif($dStats['BoatLic'] == '1') $dStats['BoatLic'] = 'Acquis';
			
			if($dStats['MotoLic'] == '0') $dStats['MotoLic'] = 'Non acquis';
			elseif($dStats['MotoLic'] == '1') $dStats['MotoLic'] = 'Acquis';
			
			if($dStats['LourdLic'] == '0') $dStats['LourdLic'] = 'Non acquis';
			elseif($dStats['LourdLic'] == '1') $dStats['LourdLic'] = 'Acquis';
			
			if($dStats['FishLic'] == '0') $dStats['FishLic'] = 'Non acquis';
			elseif($dStats['FishLic'] == '1') $dStats['FishLic'] = 'Acquis';
			
			if($dStats['TrainLic'] == '0') $dStats['TrainLic'] = 'Non acquis';
			elseif($dStats['TrainLic'] == '1') $dStats['TrainLic'] = 'Acquis';
			
			if($dStats['Sex'] == '1') $dStats['Sex'] = 'Homme';
			elseif($dStats['Sex'] == '2') $dStats['Sex'] = 'Femme';
			
			if($dStats['PhoneNr'] == '0') $dStats['PhoneNr'] = 'Aucun';
			
			if($dStats['Connected'] == '0') $dStats['Connected'] = 'Non';
			else $dStats['Connected'] = '<font color="red">Oui</font>';
			
			if($dStats['Locked'] == '0') $dStats['Locked'] = 'Non';
			else $dStats['Locked'] = '<font color="red">Oui</font>';
			
			if($dStats['CombatStyle'] == '0') $dStats['CombatStyle'] = 'Elbow';
			elseif($dStats['CombatStyle'] == '1') $dStats['CombatStyle'] = 'Boxing';
			elseif($dStats['CombatStyle'] == '2') $dStats['CombatStyle'] = 'Grabkick';
			elseif($dStats['CombatStyle'] == '3') $dStats['CombatStyle'] = 'Kneehead';
			elseif($dStats['CombatStyle'] == '4') $dStats['CombatStyle'] = 'Kungfu';
			elseif($dStats['CombatStyle'] == '5') $dStats['CombatStyle'] = 'Normal';
			
			$age = $dStats['Level']+16;
			
			echo '<article><div class="title">Profil</div><div class="new">';
			
			echo '<div style="text-align: center;"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<b> Profil </b> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=3"> Faction/Job </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6""> Inventaire</a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></b></div><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Compte</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n"
                . '<li><b>Actuellement connecté IG :</b> ' . $dStats['Connected'] .'</li>',"\n"
                . '<li><b>Temps de jeu :</b> ' . $dStats['ConnectedTime'] . ' heure(s)</li>',"\n"
                . '<li><b>Avertissement(s) :</b> ' . $dStats['Warnings'] . '</li>',"\n"
                . '<li><b>Email : </b> ' . $dStats['Email'] . '</li>',"\n"
				. '<li><b>Dernière connexion : </b> ' . date("d-m-Y à H:i:s",$dStats['LastLog']) . '</li>',"\n"
				. '<li><b>Banni : </b> ' . $dStats['Locked'] . '</li>',"\n"
                . '</ul>',"\n"
                . '</td></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Le personnage</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n"
                . '<li><b>Identité :</b> ' . $dStats['Name'] .'</li>',"\n"
                . '<li><b>Age :</b> ' .$age. ' ans</li>',"\n"
                . '<li><b>Level :</b> ' . $dStats['Level'] . '</li>',"\n"
                . '<li><b>Origine : </b> ' . $dStats['Origin'] . '</li>',"\n"
                . '<li><b>Sexe : </b> ' . $dStats['Sex'] . '</li>',"\n"
				. '<li><b>Numéro de téléphone : </b> ' . $dStats['PhoneNr'] . '</li>',"\n"
				. '<li><b>Cash : </b> $' . $dStats['Cash'] . '</li>',"\n"
				. '<li><b>Compte en banque : </b> $' . $dStats['Bank'] . '</li>',"\n"
				. '<li><b>Seconde langue : </b> '. get_LangName($dStats['Lang1']) .' ('.$dStats['LangState1'].' %)</li>',"\n"
				. '<li><b>Troisième langue : </b> ' . get_LangName($dStats['Lang2']) . ' ('.$dStats['LangState2'].' %)</li>',"\n"
				. '<li><b>Style de combat : </b> ' . $dStats['CombatStyle'] . '</li>',"\n"
                . '</ul>',"\n"
                . '</td>',"\n"
                . '<td align="right" valign="middle" style="padding:5px; background:#ffffff">',"\n"
                . '<img style="border: 0; overflow: auto; max-width: 100px; width: expression(this.scrollWidth >= 100? \'100px\' : \'auto\');" src="../images/SKINS/' . $dStats['Skin'] . '.jpg" alt="" />',"\n"
                . '</td></tr></table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Les permis</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n"
                . '<li><b>Permis conduire :</b> ' . $dStats['CarLic'] .'</li>',"\n"
                . '<li><b>Permis de vol :</b> ' . $dStats['FlyLic'] . '</li>',"\n"
                . '<li><b>Permis de navigation :</b> ' . $dStats['BoatLic'] . '</li>',"\n"
                . '<li><b>Permis moto : </b> ' . $dStats['MotoLic'] . '</li>',"\n"
                . '<li><b>Permis poids lourd : </b> ' . $dStats['LourdLic'] . '</li>',"\n"
				. '<li><b>Permis de pêche : </b> ' . $dStats['FishLic'] . '</li>',"\n"
				. '<li><b>Permis de train : </b> ' . $dStats['TrainLic'] . '</li>',"\n"
                . '</ul>',"\n"
                . '</td></table><br />',"\n";
				
				
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	function help_Def()
	{
		echo '<article>
			<div class="title">Definitions</div>
			<div class="new">
				<center>
					<h3>Le RolePlay</h3>
					Signifie RolPlay : Jeu de rôle.<br/>
					Le RolePlay est le fait de jouer et faire évoluter un personnage comme dans la réalité.
					<h3>IC</h3>
					Signifie In Caractere : Dans votre personnage.<br/>
					Toutes les actions se font par lui. (Chat Normal)
					<h3>OOC</h3>
					Signifie Out Of Caractere : En dehors de votre personnage.<br/>
					C\'est à dire vous derière votre écran. (Cannaux OOC (/b - /o ..))
					<h3>IG</h3>
					Signifie In Game : Dans le jeu.
					<h3>IRL</h3>
					Signifie In Real Life : Dans la vie réel.
					<h3>Le Carkill</h3>
					Tuer une personnes en l\'écrasant et restant dessus depuis son véhicule. <font color="red">(INTERDIT)</font>
					<h3>Le PowerGame</h3>
					Le PowerGame a deux définitions :<br/>
					1. Faire une action impossible dans la réalité. <font color="red">(INTERDIT)</font><br/>
					2. Forcer le RP.<font color="red">(INTERDIT)</font>
					<h3>Le Metagame</h3>
					Utiliser des informations OOC en IC. <font color="red">(INTERDIT)</font>
					<h3>Revenge Kill</h3>
					C\'est le fait de se venger de sa mort en tuant la personne qui vous a tué au par avant. <font color="red">(INTERDIT)</font>
					<h3>Bunny Hopping</h3>
					Sautez pour aller plus vite. <font color="red">(INTERDIT)</font>
					<h3>Chiken Run</h3>
					Courir en slaloment pour éviter les balles <font color="red">(INTERDIT)</font>
					<h3>Le Rush</h3>
					Foncer dans le tas. <font color="red">(INTERDIT)</font>
					<h3>Le Mixe</h3>
					Melanger les canaux OOC (/b /o) et IC (Chat normal) <font color="red">(INTERDIT)</font>
					<h3>Le CarJack</h3>
					Ejecter quelqu\'un de son véhicule sans /me <font color="red">(INTERDIT)</font>
					<h3>Le DeathMatch</h3>
					Tuer une ou plusieurs personnes sans raison <font color="red">(INTERDIT)</font>
					<br/><br/>
				</center>
			</div>
			<br/>
			</article>';
	}
	function help_Rules()
	{
		echo '<article>
			<div class="title">Reglement Serveur</div>
			<div class="new">
				<center>
					<h3>Les comptes</h3>
					Vous avez le droit à un compte. Le double compte est interdit et passible d\'un ban.<br/>
					Il vous est interdit de partager/vendre votre compte. En cas de piratage de celui çi nous n\'en sommes en aucuns cas responsable. <br/>
					<h3>Le stunt</h3>
					Le stunt est strinctement interdit dans Los Santos. Mais vous avez la possiblité d\'en faire lors des events ou dans les stadiums.
					En cas de non respect de cette règles des sanctions s\'y appliqueront. Nous vous invitons à lire la définition du stunt.
					<h3>Conduite Rôleplay</h3>
					Il vous ait obligatoire de rouler de façon RôlePlay. Peu importe où vous êtes, votre conduite doit le rester.
					Des limitations de vitesse ont été mis en ville de sorte a vous obliger à le faire.
					<h3>Deconnexion en scène</h3>
					La deconnexion en scène est interdit et sèverement punnie. Assurez-vous que la/les personne(s) soi(en)t d\'accords pour
					remettre la scène à un autre moment.
					<h3>Les insultes</h3>
					Les insultes InGame sont totalement tolérées, serte, les insulte OOC sont interdites et passives d\'une lourde sanction.
					<h3>Les Drive By</h3>
					Le Drive est autorisé qu\'en passager et seulement passager.Toute personne prise en flât grand délit sera punnie.<br/>
					Armes autorisées : MP5 - Colt 45 - Silencieux - Uzi - Tec 9
				</center>
			</div>
			<br/>
			</article>';
	}
	function help_Staff()
	{
		$rJoueur = mysql_query("SELECT * FROM `lvrp_users` ORDER BY `id`");	
		echo '<article>
			<div class="title">Staff</div>
			<div class="new"><br/>';
		$membres = '<center>';	
		while($dJoueur = mysql_fetch_array($rJoueur))
		{
			if($dJoueur['AdminLevel']>=1)
			{
					if($dJoueur['AdminLevel'] == '1')
						$dJoueur['AdminLevel']= 'Modérateur Test';
					elseif ($dJoueur['AdminLevel'] == '2')
						$dJoueur['AdminLevel']= 'Modérateur';
					elseif ($dJoueur['AdminLevel'] == '3')
						$dJoueur['AdminLevel']= 'Admin';
					elseif ($dJoueur['AdminLevel'] == '4')
						$dJoueur['AdminLevel']= 'Admin Général';
					elseif ($dJoueur['AdminLevel'] == '5')
						$dJoueur['AdminLevel']= 'Gestionnaire';
					elseif ($dJoueur['AdminLevel'] == '6')
						$dJoueur['AdminLevel'] = 'Co-Fondateur';
					elseif ($dJoueur['AdminLevel'] == '7')
						$dJoueur['AdminLevel']= 'Fondateur';
						
					if($dJoueur['Connected']==1) $dJoueur['Connected'] ='Connecté';
					else  $dJoueur['Connected']='Déconnecté';
						
				$membres .= '<tr>
				<td>'.$dJoueur['Name'].'</td>
				<td>'.$dJoueur['AdminLevel'].'<br /></td>
				<td>'.$dJoueur['Connected'].'<br /></td>
				</tr></center>';
			}
			else if($dJoueur['Helper'] == 1)
			{
				if($dJoueur['Connected']==1) $dJoueur['Connected'] ='Connecté';
				else  $dJoueur['Connected']='Déconnecté';
					
				$membres .= '<tr>
				<td>'.$dJoueur['Name'].'</td>
				<td>Helpeur<br /></td>
				<td>'.$dJoueur['Connected'].'<br /></td>
				</tr></center>';
			}
		}
		echo '<center>
		<table id="container" BORDER=1 CELLPADDING=12 CELLSPACING=0>
			<tr><b>
				<td><u>Joueur</u></td>
				<td><u>Rang Admin</u></td>
				<td><u>Statue IG</u></td>
			</b></tr>
		
			'.$membres.'
		</table><br></center></center>';
		echo'
			</div>
			<br/>
			</article>';
	}
	function getServerUserInfo($name)
	{
		global $db;
		$statss = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$name."'");
		$dStats = $db->fetch_assoc($statss);
		return $dStats;
	}
	function boutique_Tokens()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			
			if($_GET['check']=="1")
			{
							// Déclaration des variables
							$ident=$idp=$ids=$idd=$codes=$code1=$code2=$code3=$code4=$code5=$datas='';
							$idp = 70188;
							// $ids n'est plus utilisé, mais il faut conserver la variable pour une question de compatibilité
							$idd = 146281;
							$ident=$idp.";".$ids.";".$idd;
							// On récupère le(s) code(s) sous la forme 'xxxxxxxx;xxxxxxxx'
							if(isset($_POST['code1'])) $code1 = $_POST['code1'];
							if(isset($_POST['code2'])) $code2 = ";".$_POST['code2'];
							if(isset($_POST['code3'])) $code3 = ";".$_POST['code3'];
							if(isset($_POST['code4'])) $code4 = ";".$_POST['code4'];
							if(isset($_POST['code5'])) $code5 = ";".$_POST['code5'];
							$codes=$code1.$code2.$code3.$code4.$code5;
							// On récupère le champ DATAS
							if(isset($_POST['DATAS'])) $datas = $_POST['DATAS'];
							// On encode les trois chaines en URL
							$ident=urlencode($ident);
							$codes=urlencode($codes);
							$datas=urlencode($datas);
							
							/* Envoi de la requête vers le serveur StarPass
							Dans la variable tab[0] on récupère la réponse du serveur
							Dans la variable tab[1] on récupère l'URL d'accès ou d'erreur suivant la réponse du serveur */
							$get_f=@file("http://script.starpass.fr/check_php.php?ident=$ident&codes=$codes&DATAS=$datas");
							if(!$get_f)
							{
								exit("Votre serveur n'a pas accès au serveur de StarPass, merci de contacter votre hébergeur.");
							}
							$tab = explode("|",$get_f[0]);
							
							if(!$tab[1]) $url = "http://lv-rp.fr/index.php?do=boutique&type=token&error=1";
							else $url = $tab[1];
							
							// dans $pays on a le pays de l'offre. exemple "fr"
							$pays = $tab[2];
							// dans $palier on a le palier de l'offre. exemple "Plus A"
							$palier = urldecode($tab[3]);
							// dans $id_palier on a l'identifiant de l'offre
							$id_palier = urldecode($tab[4]);
							// dans $type on a le type de l'offre. exemple "sms", "audiotel, "cb", etc.
							$type = urldecode($tab[5]);
							// vous pouvez à tout moment consulter la liste des paliers à l'adresse : http://script.starpass.fr/palier.php
							
							// Si $tab[0] ne répond pas "OUI" l'accès est refusé
							// On redirige sur l'URL d'erreur
							if(substr($tab[0],0,3) != "OUI")
							{
								  header("Location: $url");
								  exit;
							}
							else
							{
									$player = $db->fetch_assoc($db->query("SELECT * FROM `lvrp_users` WHERE Name='".$_SESSION['Login']."'"));
									$jeton = $player['Tokens']+100;
									$db->query("UPDATE lvrp_users SET Tokens=$jeton WHERE Name='".$_SESSION['Login']."'");
									$date = date("d-m-Y");
									$heure = date("H:i");
									$ip = $_SERVER["REMOTE_ADDR"];
									$db->query("INSERT INTO `lvrp_site_tokens` SET Name='".$_SESSION['Login']."', Date='".$date." a ".$heure."', Reson='+ 100', Ip='".$ip."'");
							}
			}
			
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			
			echo '<article><div class="title">Tokens</div><div class="new">';
			if($_GET['error']=="1")
				{echo '</br><center><u><b><font color="red">Code invalide !</font></u></center></b>';}
			$token = round($dStats['Tokens'], 2);
			echo '<br/><center><b>Vous disposez de <font color="red"> '.$token.'</font> token(s).<br/><br/>
			Commande de 100 tokens ci-dessous :</b></center>';
			echo '
			<div id="starpass_146281"></div>
			<script type="text/javascript" src="http://script.starpass.fr/script.php?idd=146281&amp;verif_en_php=1&amp;datas=">
			</script>
			<noscript>Veuillez activer le Javascript de votre navigateur s\'il vous pla&icirc;t.<br />
			<a href="http://www.starpass.fr/">Micro Paiement StarPass</a>
			</noscript>
			<i><b> * Nous ne sommes en aucuns cas responsable de la perte de votre code ou d\'un code incorrect. Ce service est sécurisé par STARPASS.
			<br/>Nous vous invitons à lire le <a href="http://www.starpass.fr/include/StarPass-CGU.pdf">CGU STARPASS</a></i>
			<br/></b>';
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	function boutique_Buy()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			echo '<br/><center><b>Vous disposez de <font color="red"> '.$token.'</font> token(s).<br/><br/></b></center>';
			
			if(isconnectedIG($_SESSION['Login']))
				{echo '<b><center><font color="red">Attention, vous devez être déconnecté du serveur !</font></center></b><br/>';}
			
			/*echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Informations Générales</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '</ul>',"\n"
                . '<b>
				La boutique a été désactivée.</b>
				</td></table><br />',"\n";*/
			
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Informations Générales</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '</ul>',"\n"
                . '<b>
				Les packs VIP servent à payer les hébergements pour la continuité du serveur, en aucuns cas
				ils sont bénéfiques à des fins personnelles.<br/>
				<font color="green">Livraison immédiate après achat !</font></b>
				</td></table><br />',"\n";
			
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Informations Pack VIP</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>Canal VIP</li>';
			echo '<li>Possibilité de mettre son armure à 50 toutes les 60 mns</li>';
			echo '<li>Possibilité de changer de skin</li>';
			echo '<li>Accès aux PM + Chat VIP</li>';
			echo '<li>Titre \'VIP\' sur les canaux IG et sur le forum</li></b>';
			echo '</ul>',"\n"
                . '<center><b><font color="green">Valable pour tout les pack VIP.</font></b></center></td>
				</table><br />',"\n";
			
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Pack VIP Fer</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ 1 Slot de véhicule en plus</li>';
			echo '<li>+ 1 Rename</li>';
			echo '<li>+ 1 Changement de numéro personnalisé</li>';
			echo '<li>+ 2 Points de respect</li></b>';
			echo '<br/><b><font color="blue"> Coût : 100 tokens</font></b>';
			echo '<br/><b><font color="red"> Durée 48 heures de jeu</font></b>';
			echo '</ul>',"\n"
                . '<center><form  name="form1" method="post" action="index.php?do=boutique&type=achat1">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></center></td>
				</table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Pack VIP Argent</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ 1 Slot de véhicule en plus</li>';
			echo '<li>+ 2 Renames</li>';
			echo '<li>+ 1 Changement de numéro personnalisé</li>';
			echo '<li>+ 1 Changement de plaque</li>';
			echo '<li>+ 4 Points de respect</li>';
			echo '<li>+ Intéret 5 % aux payes</li></b>';
			echo '<br/><b><font color="blue"> Coût : 300 tokens</font></b>';
			echo '<br/><b><font color="red"> Durée 96 heures de jeu</font></b>';
			echo '</ul>',"\n"
                . '<center><form  name="form1" method="post" action="index.php?do=boutique&type=achat2">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></center></td>
				</table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Pack VIP Or</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ 2 Slots de véhicule en plus</li>';
			echo '<li>+ 3 Renames</li>';
			echo '<li>+ 1 Changement de numéro personnalisé</li>';
			echo '<li>+ 2 Changements de plaque</li>';
			echo '<li>+ 8 Points de respect</li>';
			echo '<li>+ Intéret 10 % aux payes</li></b>';
			echo '<br/><b><font color="blue"> Coût : 500 tokens</font>';
			echo '<br/><b><font color="red"> Durée 192 heures de jeu</font></b>';
			echo '</ul>',"\n"
                . '<center><form  name="form1" method="post" action="index.php?do=boutique&type=achat3">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></center></td>
				</table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Pack VIP Diamant</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ 3 Slots de véhicule en plus</li>';
			echo '<li>+ 4 Renames</li>';
			echo '<li>+ 2 Changements de numéro personnalisé</li>';
			echo '<li>+ 2 Changements de plaque</li>';
			echo '<li>+ 16 Points de respect</li>';
			echo '<li>+ Intéret 20 % aux payes</li>';
			echo '<li>+ Accès au sac VIP (+2 Slots d\'arme et 750 Kg Max.)</li></b>';
			echo '<br/><b><font color="blue"> Coût : 800 tokens</font></b>';
			echo '<br/><b><font color="red"> Durée 384 heures de jeu</font></b>';
			echo '</ul>',"\n"
                . '<center><form  name="form1" method="post" action="index.php?do=boutique&type=achat4">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></center></td>
				</table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Argent</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ $1.000</b>';
			echo '<br/><b><font color="blue"> Coût : 25 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat5">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ $2.500</b>';
			echo '<br/><b><font color="blue"> Coût : 50 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat6">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ $5.000</b>';
			echo '<br/><b><font color="blue"> Coût : 100 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat7">
					<input class="buton_b"  type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ $10.000 </b>';
			echo '<br/><b><font color="blue"> Coût : 200 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat8">
					<input class="buton_b"  type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ $20.000</b>';
			echo '<br/><b><font color="blue"> Coût : 300 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat9">
					<input class="buton_b"  type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '</ul>',"\n"
                . '<b>Notre but n\'est pas de vous faire évoler avec de l\'argent IRL et ni de vous arnaquer,
				nous avons décidé de faire des packs argent pour ceux qui vraiment en demandaient. </b></td>
				</table><br />',"\n";
				
			echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Autres</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
			echo '<li><b>+ 1 Rename</b>';
			echo '<br/><b><font color="blue"> Coût : 50 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat10">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ 1 Changement de numéro</b>';
			echo '<br/><b><font color="blue"> Coût : 50 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat11">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ 1 Changement de plaque</b>';
			echo '<br/><b><font color="blue"> Coût : 50 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat12">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ 1 Respect</b>';
			echo '<br/><b><font color="blue"> Coût : 25 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat13">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '<li><b>+ 1 Level</b>';
			echo '<br/><b><font color="blue"> Coût : 400 tokens</font></b>';
			echo '<form  name="form1" method="post" action="index.php?do=boutique&type=achat14">
					<input class="buton_b" type="submit" name="submit" id="send" value="Acheter" />
				</form></li>';
			echo '</ul>',"\n"
                . '</td>
				</table><br />',"\n";
				
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
	function boutique_BuyType($id)
	{
		if($id==1)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 100 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-100.0;
				$rename = $dStats['PointsRename']+1;
				$changnum = $dStats['ChangeNum']+1;
				$respect = $dStats['Respect']+2;
				$viptime = $dStats['VipTime']+2880;
				if($dStats['CarUnLock4'] == 0)
					{$db->query('UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='.$_SESSION['Login'].'');}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query('UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='.$_SESSION['Login'].'');}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query('UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='.$_SESSION['Login'].'');}
					
				$db->query("UPDATE `lvrp_users` SET PointsRename='".$rename."', ChangeNum='".$changnum."', Respect='".$respect."', DonateRank=1, VipTime='".$viptime."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"Pack VIP FER");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté le pack VIP Fer, merci.</center></b>';
				echo '<br/><i><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==2)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 300 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-300.0;
				$rename = $dStats['PointsRename']+2;
				$changnum = $dStats['ChangeNum']+1;
				$changplaq = $dStats['ChangePlaque']+1;
				$respect = $dStats['Respect']+4;
				$viptime = $dStats['VipTime']+5760;
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				$db->query("UPDATE `lvrp_users` SET PointsRename='".$rename."', ChangeNum='".$changnum."', Respect='".$respect."', ChangePlaque='".$changplaq."', DonateRank=2, VipTime='".$viptime."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"Pack VIP OR");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté le pack VIP Argent, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==3)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 500 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-500.0;
				$rename = $dStats['PointsRename']+3;
				$changnum = $dStats['ChangeNum']+1;
				$changplaq = $dStats['ChangePlaque']+2;
				$respect = $dStats['Respect']+8;
				$viptime = $dStats['VipTime']+11520;
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				$db->query("UPDATE `lvrp_users` SET PointsRename='".$rename."', ChangeNum='".$changnum."', Respect='".$respect."', ChangePlaque='".$changplaq."', DonateRank=3, VipTime='".$viptime."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"Pack VIP ARGENT");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté le pack VIP Or, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==4)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 800 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-800.0;
				$rename = $dStats['PointsRename']+4;
				$changnum = $dStats['ChangeNum']+2;
				$changplaq = $dStats['ChangePlaque']+2;
				$respect = $dStats['Respect']+16;
				$viptime = $dStats['VipTime']+23040;
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				if($dStats['CarUnLock4'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock4=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock5'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock5=1 WHERE Name='".$_SESSION['Login']."'");}
				elseif($dStats['CarUnLock6'] == 0)
					{$db->query("UPDATE `lvrp_users` SET CarUnLock6=1 WHERE Name='".$_SESSION['Login']."'");}
					
				$db->query("UPDATE `lvrp_users` SET PointsRename='".$rename."', ChangeNum='".$changnum."', Respect='".$respect."', ChangePlaque='".$changplaq."', DonateRank=4, VipTime='".$viptime."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"Pack VIP DIAMANT");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté le pack VIP Diamant, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==5)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 25 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-25.0;
				$cash = $dStats['Cash']+1000;
				
					
				$db->query("UPDATE `lvrp_users` SET Cash='".$cash."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+ $1.000");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté $1.000, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==6)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 50 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-50.0;
				$cash = $dStats['Cash']+2500;
				
					
				$db->query("UPDATE `lvrp_users` SET Cash='".$cash."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+ $2.500");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté $2.500, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==7)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 100 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-100.0;
				$cash = $dStats['Cash']+5000;
				
					
				$db->query("UPDATE `lvrp_users` SET Cash='".$cash."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+ $5.000");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté $5.000, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==8)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 200 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-200.0;
				$cash = $dStats['Cash']+10000;
				
					
				$db->query("UPDATE `lvrp_users` SET Cash='".$cash."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+ $10.000");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté $10.000, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==9)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 300 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-300.0;
				$cash = $dStats['Cash']+20000;
				
					
				$db->query("UPDATE `lvrp_users` SET Cash='".$cash."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+ $20.000");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté $20.000, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==10)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 50 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-50.0;
				$rename = $dStats['PointsRename']+1;
				
					
				$db->query("UPDATE `lvrp_users` SET PointsRename='".$rename."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+1 Rename");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté un rename, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==11)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 50 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-50.0;
				$changnum = $dStats['ChangeNum']+1;
				
					
				$db->query("UPDATE `lvrp_users` SET ChangeNum='".$changnum."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+1 ChangNum");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté un changement de numéro, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==12)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 50 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-50.0;
				$changplaq = $dStats['ChangePlaque']+1;
				
					
				$db->query("UPDATE `lvrp_users` SET ChangePlaque='".$changplaq."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+1 ChangePlaque");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté un changement de plaque, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==13)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 25 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-25.0;
				$respect = $dStats['Respect']+1;
				if($respect >= ($dStats['Level'])*4)
					{$db->query("UPDATE `lvrp_users` SET Level+1, Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");}
				else
					{$db->query("UPDATE `lvrp_users` SET Respect='".$respect."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");}
				log_Buy($_SESSION['Login'],"+1 Respect");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté un respect, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
		else if($id==14)
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			echo '<article><div class="title">Boutique</div><div class="new">';
			$token = round($dStats['Tokens'], 2);
			if($token >= 400 && $dStats['Connected']==0)
			{
				$jeton = $dStats['Tokens']-400.0;
				$level = $dStats['Level']+1;
				
					
				$db->query("UPDATE `lvrp_users` SET Level='".$level."', Tokens='".$jeton."' WHERE Name='".$_SESSION['Login']."'");
				log_Buy($_SESSION['Login'],"+1 Level");
					
				echo '<br/><center><b>Vous disposez de maintenant <font color="red"> '.$jeton.' </font> token(s).<br/><br/></b></center>';
				echo '<br/><b><center>Vous avez bien acheté un level, merci.</center></b>';
				echo '<br/><i><center><a href="index.php?do=boutique&type=buy"><center>Retour à la boutique</a></center></i>';
				echo '</div></article>';
			}
			else
				{header('Location: index.php?do=boutique&type=buy');}
		}
	}
	function log_Token($Name,$Reson)
	{
		$date = date("d-m-Y");
		$heure = date("H:i");
		$ip = $_SERVER["REMOTE_ADDR"];
		mysql_query("INSERT INTO `lvrp_site_tokens` SET Name='".$Name."', Date='".$date." a ".$heure."', Reson='".$Reson."', Ip='".$ip."'");
	}
	function log_Buy($Name,$Reson)
	{
		$date = date("d-m-Y");
		$heure = date("H:i");
		$ip = $_SERVER["REMOTE_ADDR"];
		mysql_query("INSERT INTO `lvrp_site_pay` SET Name='".$Name."', Date='".$date." a ".$heure."', Reson='".$Reson."', Ip='".$ip."'");
	}
	function vote_Top($type)
	{
		global $db;
		if($type==1)
		{
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			$datesql = date("d-m-Y H:i:s", time());
			$vote = date('d-m-Y H:i:s', $dStats['HasVoted1']);
			$date = strtotime($datesql);
			$vote = strtotime($vote);
			 
			$hours = round(abs($date-$vote)/60/60);
			
			if($hours > 2)
			{
				$ip = $_SERVER["REMOTE_ADDR"];
				echo '<meta http-equiv="refresh" content="0; URL=http://www.root-top.com/topsite/gta/in.php?ID=2382">';
				$jeton = $dStats['Tokens']+0.5;
				$db->query("UPDATE lvrp_users SET Tokens=$jeton, HasVoted1=UNIX_TIMESTAMP() WHERE Name='".$_SESSION['Login']."'");
				$db->query("INSERT INTO `lvrp_site_votes` SET Name='".$_SESSION['Login']."', Date='".$datesql."', Reason='Vote Root Top', Ip='".$ip."'");
			}
			else
				{echo '<meta http-equiv="refresh" content="0; URL=index.php">';}
		}
		if($type==2)
		{
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			$datesql = date("d-m-Y H:i:s", time());
			$vote = date('d-m-Y H:i:s', $dStats['HasVoted2']);
			$date = strtotime($datesql);
			$vote = strtotime($vote);
			 
			$hours = round(abs($date-$vote)/60/60);
			
			if($hours > 2)
			{
				$ip = $_SERVER["REMOTE_ADDR"];
				echo '<meta http-equiv="refresh" content="0; URL=http://gtatop.eu/vote.php?id=67">';
				$jeton = $dStats['Tokens']+0.5;
				$db->query("UPDATE lvrp_users SET Tokens=$jeton, HasVoted2=UNIX_TIMESTAMP() WHERE Name='".$_SESSION['Login']."'");
				$db->query("INSERT INTO `lvrp_site_votes` SET Name='".$_SESSION['Login']."', Date='".$datesql."', Reason='Vote GTA Top', Ip='".$ip."'");
			}
			else
				{echo '<meta http-equiv="refresh" content="0; URL=index.php">';}
		}
	}
	function vote_GetTime($type)
	{
		global $db;
		if($type==1)
		{
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			$date = date("d-m-Y H:i:s", time());
			$vote = date('d-m-Y H:i:s', $dStats['HasVoted1']);
			$date = strtotime($date);
			$vote = strtotime($vote);
			 
			$hours = round(abs($date-$vote)/60/60);
			
			return $hours;
		}
		if($type==2)
		{
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			$date = date("d-m-Y H:i:s", time());
			$vote = date('d-m-Y H:i:s', $dStats['HasVoted2']);
			$date = strtotime($date);
			$vote = strtotime($vote);
			 
			$hours = round(abs($date-$vote)/60/60);
			
			return $hours;
		}
	}
	function faction_Gestion($id)
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			if($dStats['Leader']==$id)
			{
				echo '<article><div class="title">Gestion Faction</div><div class="new"><br/>';
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 1</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=1">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],1).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],1).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 2</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],2).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],2).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 3</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],3).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],3).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 4</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],4).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],4).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 5</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],5).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],5).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 6</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],6).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],6).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
					
				if($id==1 || $id==2)
				{
					echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Rang 7</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px"><form method="post" action="index.php?do=faction_save&id=1&type=2">',"\n"
					. '<li><b>Nom du rang : </b><input type="text" value="'.get_FacRank($dStats['Member'],7).'" name="rank1_name" size="20" maxlength="32" /></li>',"\n"
					. '<li><b>Skin du rang : </b><input type="text" value="'.get_FacSkin($dStats['Member'],7).'" name="rank1_skin" size="20" maxlength="3" /></li>',"\n"
					. '</ul>',"\n"
					. '<center><input class="buton_b" type="submit" name="submit" value="Enregistrer" /></center></form></td></table><br />',"\n";
				}
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
                . '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b> Membres</b></font></td></tr>',"\n"
                . '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
                . '<ul style="list-style-type:square;margin-left:25px">',"\n";
				$result = $db->query("SELECT * FROM `lvrp_users` WHERE Member='".$id."'");	
				while($dJoueur = $db->fetch_assoc($result))
				{
					if($dJoueur['Member']==$id)
					{
						echo '<li><b>'.$dJoueur['Name'].'</b> (<a href="index.php?do=member&id='.$dJoueur['id'].'&type=1">Virer</a> - <a href="index.php?do=member&id='.$dJoueur['id'].'&type=2">Changer le rang</a>)</li>';
					}
				}
				
				echo '</ul>',"\n"
					. '</td></table><br />',"\n";
					
				echo '</div></article>';
			}
			else
				{header('Location: index.php');}
		}
		else
			{header('Location: index.php');}
	}
	function faction_Jack($idsql)
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);
			if($dStats['Leader']==$id)
			{
			}
			else
				{header('Location: index.php');}
		}
		else
			{header('Location: index.php');}
	}
	function profil_Fac2()
	{
		if(!$pun_user['is_guest'])
		{
			global $db;
			$result = $db->query("SELECT * FROM `lvrp_users` WHERE `Name`='".$_SESSION['Login']."'");
			$dStats = $db->fetch_assoc($result);

			if($dStats['Leader'] == $dStats['Member']) $dStats['Leader'] = 'Oui (<a href="index.php?do=faction&id='.$dStats['Leader'].'">Gestion</a>)';
			else $dStats['Leader']="Non";
			
			echo '<article><div class="title">Profil</div><div class="new">';
			
			echo '<div style="text-align: center"><br /><big><b>' . $dStats['Name'] . '</b></big><br /><br />',"\n"
                . '<a href="index.php?do=profil&amp;type=1"> Profil </a> | ',"\n"
				. '<b>Faction/Job </b> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=4"> Biens </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=5"> Casier </a> | ',"\n"
                . '<a href="index.php?do=profil&amp;type=6""> Inventaire</a> | ',"\n"
				. '<a href="index.php?do=profil&amp;type=7"> V.I.P </a></b></div><br />',"\n";
				
			if($dStats['Member'] > 0)
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Faction</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><b>Faction :</b> ' . get_FacName($dStats['Member']).'</li>',"\n"
					. '<li><b>Leader :</b> ' . $dStats['Leader'] . '</li>',"\n"
					. '<li><b>Rang :</b> ' .get_FacRank($dStats['Member'],$dStats['Rank']). ' ('.$dStats['Rank'].')</li>',"\n"
					. '<li><b>Temps de travail : </b> ' . $dStats['DutyTime'] . ' minute(s)</li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			else
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Faction</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><i>Vous ne faites parti d\'aucunes factions.</i></li>',"\n"
					. '</ul>',"\n"
					. '</td></table><br />',"\n";
			}
			
			if($dStats['Job'] > 0)
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Job</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><b>Job :</b> ' . get_JobName($dStats['Job']).'</li>',"\n"
					. '<li><b>Niveau :</b> ' . $dStats['JobLvl'] . '</li>',"\n"
					. '<li><b>Expérience :</b> ' .$dStats['JobExp'].'</li>',"\n"
					. '<li><b>Bonus : $</b> ' .$dStats['JobBonnus'].'</li>',"\n"
					. '<li><b>Temps de travail : </b> ' . $dStats['JobTime'] . ' minute(s)</li>',"\n"
					. '</ul>',"\n"
					. '</form></td></table><br />',"\n";
			}
			else
			{
				echo '<table style="margin:auto; background:#ffffff; border:1px solid #1E1E1E; width:75%;" cellpadding="0" cellspacing="1">',"\n"
					. '<tr style="background: #1E1E1E"><td colspan="2" align="center" style="padding:2px"><font color="white"><b>Job</b></font></td></tr>',"\n"
					. '<tr style="background: #00FFFA"><td align="left" valign="middle" style="width:100%; background:#ffffff">',"\n"
					. '<ul style="list-style-type:square;margin-left:25px">',"\n"
					. '<li><i>Vous ne faites parti d\'aucun jobs.</i></li>',"\n"
					. '</ul>',"\n"
					. '</form></td></table><br />',"\n";
			}
			
			echo '</div></article>';
		}
		else
			{header('Location: index.php');}
	}
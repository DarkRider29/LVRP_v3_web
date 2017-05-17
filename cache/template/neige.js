(function() {

	"use strict";

	var canvas = document.createElement('canvas');
	if (!canvas.getContext || !canvas.getContext('2d')) return;


	var container = document.createElement('div');
	document.body.insertBefore(container, document.body.firstElementChild);
	container.style.position = 'fixed';
	container.style.zIndex = 9999;
	container.style.width = 0;
	container.style.height = 0;

	function generateSprite() {

		var canvas = document.createElement( 'canvas' );
		canvas.width = 10;
		canvas.height = 10;
		var context = canvas.getContext( '2d' );

		context.fillStyle = "white";
		context.strokeStyle = "white";
		context.beginPath();
		context.arc(canvas.width/2, canvas.width/2, canvas.width/2 * 0.9, 0, Math.PI*2, false);
		context.closePath();

		var gradient = context.createRadialGradient( canvas.width /2, canvas.height /2, 0, canvas.width /2, canvas.height /2, canvas.width /2 )
		gradient.addColorStop( 0, 'rgba(211,211,211,0.9)' );
		gradient.addColorStop( 0.2, 'rgba(211,211,211,0.5)' );
		gradient.addColorStop( 0.8, 'rgba(211,211,211,0.1)' );
		gradient.addColorStop( 1, 'rgba(211,211,211,0)' );

		context.fillStyle = gradient;
		context.fill();

		return canvas;

	}

	var rand = Math.random;
	var sin = Math.sin;

	var rand2 = function () {
		return rand() - 0.5;
	}

	function generateParticle() {
		var x, y, z, dx, dy, dz;
		x = rand() * window.innerWidth;
		y = rand() * 1000;
		dx = rand2() * 5;
		dy = rand() * 3+2;
		var sprite = generateSprite();
		sprite.style.opacity = Math.random() * 0.5 + 0.5;
		sprite.style.display = 'block';
		sprite.style.position = 'absolute';
		sprite.style.top = 0;
		sprite.style.left = 0;
		container.appendChild(sprite);
		return {
			x: x, y: y,
			dx: dx, dy: dy,
			dom: sprite
		}
	}

	var particlesCount = 250;
	var particles, particle, p;

	particles = [];
	for (p=0; p<particlesCount; p++) {
		particles.push(generateParticle());
	}

	var transforms = ['transform', 'MozTransform', 'WebkitTransform', 'msTransform', 'OTransform'];
	update();

	function update() {
		
		window.setTimeout(update, 1000 / 30);

		var width = window.innerWidth;
		var height = window.innerHeight;

		var time = Date.now(), tx, left;
		for (p=0; p<particlesCount; p++) {
			particle = particles[p];

			left = (particle.x % width);
			if (left < -40) left = width + left;

			for (var tf=0; tf<transforms.length;tf++) {
				particle.dom.style[transforms[tf]] = 'translateX(' + left + 'px) translateY(' +
				particle.y + 'px) translateZ(0)';
			}

			particle.x += particle.dx;
			particle.y += particle.dy;
			particle.z += particle.dz;

			tx = particle.x + ((time %1000 ) / 1000 - 0.5)*height / 16;
			particle.dx += sin(tx % (height / 8)) * 0.4;
			particle.dx += sin(time / 2000) * 0.2;

			if (particle.y > height) {
				particle.y -= height;
				particle.dy = rand() * 3+2;
			}

			if (particle.dx > 5) particle.dx = 2;
			if (particle.dx < -5) particle.dx = -2;
			if (particle.x > width) particle.dx = 2;
			if (particle.x < 0) particle.dx = -2;

		}
	}

})();
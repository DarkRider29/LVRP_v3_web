(function(c){var a=function(){};c.extend(a.prototype,{name:"ElementRating",options:{url:null},initialize:function(a,b){this.options=c.extend({},this.options,b);var d=this,e=a.find("div.stars");e.each(function(b){c(this).bind("click",function(){c.ajax({url:d.options.url,data:{method:"vote","args[0]":e.length-b},type:"post",datatype:"json",success:function(b){var b=c.parseJSON(b),d=b.value;d>0&&a.find("div.previous-rating").css("width",d+"%");a.find("div.vote-message").text(b.message)}})}).bind("mouseenter",function(){c(this).addClass("hover")}).bind("mouseleave",function(){c(this).removeClass("hover")})})}});c.fn[a.prototype.name]=function(){var f=arguments,b=f[0]?f[0]:null;return this.each(function(){var d=c(this);if(a.prototype[b]&&d.data(a.prototype.name)&&b!="initialize")d.data(a.prototype.name)[b].apply(d.data(a.prototype.name),Array.prototype.slice.call(f,1));else if(!b||c.isPlainObject(b)){var e=new a;a.prototype.initialize&&e.initialize.apply(e,c.merge([d],f));d.data(a.prototype.name,e)}else c.error("Method "+
b+" does not exist on jQuery."+a.name)})}})(jQuery);(function(c){var a=function(){};c.extend(a.prototype,{name:"EditElementRating",options:{url:null},initialize:function(a,b){this.options=c.extend({},this.options,b);var d=this;a.find('input[name="reset-rating"]').bind("click",function(){c.ajax({url:d.options.url+"&task=callelement&method=reset",success:function(b){a.replaceWith(b)}})})}});c.fn[a.prototype.name]=function(){var f=arguments,b=f[0]?f[0]:null;return this.each(function(){var d=c(this);if(a.prototype[b]&&d.data(a.prototype.name)&&b!="initialize")d.data(a.prototype.name)[b].apply(d.data(a.prototype.name),Array.prototype.slice.call(f,1));else if(!b||c.isPlainObject(b)){var e=new a;a.prototype.initialize&&e.initialize.apply(e,c.merge([d],f));d.data(a.prototype.name,e)}else c.error("Method "+b+" does not exist on jQuery."+a.name)})}})(jQuery);
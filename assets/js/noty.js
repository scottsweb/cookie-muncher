/**
* noty - jQuery Notification Plugin v1.2.1
* Contributors: https://github.com/needim/noty/graphs/contributors
*
* Examples and Documentation - http://needim.github.com/noty/
*
* Licensed under the MIT licenses:
* http://www.opensource.org/licenses/mit-license.php
*
**/
function noty(a){return jQuery.noty(a)}(function(a){a.noty=function(b,c){var d={};var e=null;var f=false;d.init=function(b){d.options=a.extend({},a.noty.defaultOptions,b);d.options.type=d.options.cssPrefix+d.options.type;d.options.id=d.options.type+"_"+(new Date).getTime();d.options.layout=d.options.cssPrefix+"layout_"+d.options.layout;if(d.options.custom.container)c=d.options.custom.container;f=a.type(c)==="object"?true:false;return d.addQueue()};d.addQueue=function(){var b=a.inArray(d.options.layout,a.noty.growls)==-1?false:true;if(!b)d.options.force?a.noty.queue.unshift({options:d.options}):a.noty.queue.push({options:d.options});return d.render(b)};d.render=function(b){var g=f?c.addClass(d.options.theme+" "+d.options.layout+" noty_custom_container"):a("body");if(b){if(a("ul.noty_cont."+d.options.layout).length==0)g.prepend(a("<ul/>").addClass("noty_cont "+d.options.layout));g=a("ul.noty_cont."+d.options.layout)}else{if(a.noty.available){var h=a.noty.queue.shift();if(a.type(h)==="object"){a.noty.available=false;d.options=h.options}else{a.noty.available=true;return d.options.id}}else{return d.options.id}}d.container=g;d.bar=a('<div class="noty_bar"/>').attr("id",d.options.id).addClass(d.options.theme+" "+d.options.layout+" "+d.options.type);e=d.bar;e.append(d.options.template).find(".noty_text").html(d.options.text);e.data("noty_options",d.options);d.options.closeButton?e.addClass("noty_closable").find(".noty_close").show():e.find(".noty_close").remove();e.find(".noty_close").bind("click",function(){e.trigger("noty.close")});if(d.options.buttons)d.options.closeOnSelfClick=d.options.closeOnSelfOver=false;if(d.options.closeOnSelfClick)e.bind("click",function(){e.trigger("noty.close")}).css("cursor","pointer");if(d.options.closeOnSelfOver)e.bind("mouseover",function(){e.trigger("noty.close")}).css("cursor","pointer");if(d.options.buttons){$buttons=a("<div/>").addClass("noty_buttons");e.find(".noty_message").append($buttons);a.each(d.options.buttons,function(b,c){bclass=c.type?c.type:"gray";$button=a("<button/>").addClass(bclass).html(c.text).appendTo(e.find(".noty_buttons")).bind("click",function(){if(a.isFunction(c.click)){c.click.call($button,e)}})})}return d.show(b)};d.show=function(b){if(d.options.modal)a("<div/>").addClass("noty_modal").addClass(d.options.theme).prependTo(a("body")).fadeIn("fast");e.close=function(){return this.trigger("noty.close")};b?d.container.prepend(a("<li/>").append(e)):d.container.prepend(e);if(d.options.layout=="noty_layout_topCenter"||d.options.layout=="noty_layout_center"){a.noty.reCenter(e)}e.bind("noty.setText",function(b,c){e.find(".noty_text").html(c);if(d.options.layout=="noty_layout_topCenter"||d.options.layout=="noty_layout_center"){a.noty.reCenter(e)}});e.bind("noty.setType",function(b,c){e.removeClass(e.data("noty_options").type);c=e.data("noty_options").cssPrefix+c;e.data("noty_options").type=c;e.addClass(c);if(d.options.layout=="noty_layout_topCenter"||d.options.layout=="noty_layout_center"){a.noty.reCenter(e)}});e.bind("noty.getId",function(a){return e.data("noty_options").id});e.one("noty.close",function(b){var c=e.data("noty_options");if(c.onClose){c.onClose()}if(c.modal)a(".noty_modal").fadeOut("fast",function(){a(this).remove()});e.clearQueue().stop().animate(e.data("noty_options").animateClose,e.data("noty_options").speed,e.data("noty_options").easing,e.data("noty_options").onClosed).promise().done(function(){if(a.inArray(e.data("noty_options").layout,a.noty.growls)>-1){e.parent().remove()}else{e.remove();a.noty.available=true;d.render(false)}})});if(d.options.onShow){d.options.onShow()}e.animate(d.options.animateOpen,d.options.speed,d.options.easing,d.options.onShown);if(d.options.timeout)e.delay(d.options.timeout).promise().done(function(){e.trigger("noty.close")});return d.options.id};return d.init(b)};a.noty.get=function(b){return a("#"+b)};a.noty.close=function(b){for(var c=0;c<a.noty.queue.length;){if(a.noty.queue[c].options.id==b)a.noty.queue.splice(b,1);else c++}a.noty.get(b).trigger("noty.close")};a.noty.setText=function(b,c){a.noty.get(b).trigger("noty.setText",c)};a.noty.setType=function(b,c){a.noty.get(b).trigger("noty.setType",c)};a.noty.closeAll=function(){a.noty.clearQueue();a(".noty_bar").trigger("noty.close")};a.noty.reCenter=function(b){b.css({left:(a(window).width()-b.outerWidth())/2+"px"})};a.noty.clearQueue=function(){a.noty.queue=[]};var b=window.alert;a.noty.consumeAlert=function(b){window.alert=function(c){if(b){b.text=c}else{b={text:c}}a.noty(b)}};a.noty.stopConsumeAlert=function(){window.alert=b};a.noty.queue=[];a.noty.growls=["noty_layout_topLeft","noty_layout_topRight","noty_layout_bottomLeft","noty_layout_bottomRight"];a.noty.available=true;a.noty.defaultOptions={layout:"top",theme:"noty_theme_default",animateOpen:{height:"toggle"},animateClose:{height:"toggle"},easing:"swing",text:"",type:"alert",speed:500,timeout:5e3,closeButton:false,closeOnSelfClick:true,closeOnSelfOver:false,force:false,onShow:false,onShown:false,onClose:false,onClosed:false,buttons:false,modal:false,template:'<div class="noty_message"><span class="noty_text"></span><div class="noty_close"></div></div>',cssPrefix:"noty_",custom:{container:null}};a.fn.noty=function(b){return this.each(function(){new a.noty(b,a(this))})}})(jQuery)
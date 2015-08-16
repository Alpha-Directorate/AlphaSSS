/*
	jquery.fbjlike.js - http://socialmediaautomat.com/jquery-fbjlike-js.php
	based on: jQuery OneFBLike v1.1 - http://onerutter.com/open-source/jquery-facebook-like-plugin.html
	Copyright (c) 2010 Jake Rutter modified 2011 by Stephan Helbig
	This plugin available for use in all personal or commercial projects under both MIT and GPL licenses.
*/

(function($){  
$.fn.fbjlike = function(options) {  
	
  //Set the default values, use comma to separate the settings 
  var defaults = {  
	appID: '224229974253687',
	userID: '',
	siteTitle: '',
	siteName: '',
	siteImage: '',
	href:false,
	mode: 'insert',
	buttonWidth: 450,
	buttonHeight: 60,
	showfaces: true,
	font: 'lucida grande',
	layout: 'normal',	//box_count|button_count|standard
	action: 'like',		// like|recommend
	send:false,
	comments:false,
	numPosts:10,
	colorscheme: 'light',
	lang: 'en_US',
	hideafterlike:false,
	googleanalytics:false,							//true|false
	googleanalytics_obj: 'pageTracker',	//pageTracker|_gaq
	onlike: function(){return true;},
	onunlike: function(){return true;}
}  

var options =  $.extend(defaults, options);  
	
  return this.each(function() {  
  var o = options;  
  var obj = $(this);
  if(!o.href)
  	var dynUrl = document.location;
  else
  	var dynUrl = o.href;
  var dynTitle = document.title;
  
  // Add Meta Tags for additional data - options
  if(o.appID!='')$('head').append('<meta property="fb:app_id" content="'+o.appID+'"/>');
  if(o.userID!='')$('head').append('<meta property="fb:admins" content="'+o.userID+'"/>');
  if(o.siteTitle!='')$('head').append('<meta property="og:title" content="'+o.siteTitle+'"/>');
  if(o.siteName!='')$('head').append('<meta property="og:site_name" content="'+o.siteName+'"/>');
  if(o.siteImage!='')$('head').append('<meta property="og:image" content="'+o.siteImage+'"/>');
  
  // Add #fb-root div - mandatory - do not remove
  $('body').append('<div id="fb-root"></div>');
  $('#fb-like iframe').css('height','35px !important');
  
  (function() {
    var e = document.createElement('script');
    e.async = true;
    e.src = document.location.protocol + '//connect.facebook.net/'+o.lang+'/all.js';
    $('#fb-root').append(e);
  }());
	
  // setup FB Developers App Link - do not touch
  window.fbAsyncInit = function() {
    FB.init({appId: o.appID, status: true, cookie: true, xfbml: true});
    FB.Event.subscribe('edge.create', function(response) {
		  if(o.hideafterlike)$(obj).hide();
		  if(o.googleanalytics){
				if(o.googleanalytics_obj!='_gaq'){
					pageTracker._trackEvent('facebook', 'liked', dynTitle);
				} else {
		  		_gaq.push(['_trackEvent','facebook', 'liked', dynTitle]);
		  	}
		  }
		  o.onlike.call(response);
		});
    FB.Event.subscribe('edge.remove', function(response) {
		  if(o.googleanalytics){
				if(o.googleanalytics_obj!='_gaq'){
					pageTracker._trackEvent('facebook', 'unliked', dynTitle);
				} else {
		  		_gaq.push(['_trackEvent','facebook', 'unliked', dynTitle]);
		  	}
		  }
		  o.onunlike.call(response);
		});
  };
  var tSend = '';
  if(o.send)tSend = ' send="true"';
  var thtml = '<fb:like href="'+dynUrl+'" width="'+o.buttonWidth+'" height="'+o.buttonHeight+'" show_faces="'+o.showfaces+'" font="'+o.font+'" layout="'+o.layout+'" action="'+o.action+'" colorscheme="'+o.colorscheme+'"'+tSend+'/>';
  
	if(o.comments){
  	thtml += '<'+'div style="clear:both;"></div><fb:comments href="'+dynUrl+'" num_posts="'+o.numPosts+'" width="'+o.buttonWidth+'"></fb:comments>';
  }
  if(o.mode=='append')$(obj).append(thtml);
  else $(obj).html(thtml);
	
  });  
}  
})(jQuery);
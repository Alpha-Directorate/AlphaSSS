var $ = jQuery;
var room = $('#localVideo').data('room');
var localSrc = '';

var webrtc = new SimpleWebRTC({
  // the id/element dom element that will hold "our" video
  localVideoEl: 'localVideo',
  // the id/element dom element that will hold remote videos
  remoteVideosEl: 'remoteVideos',
  // immediately ask for camera access
  autoRequestMedia: true,
  debug: false,
  detectSpeakingEvents: true,
  autoAdjustMic: true
});

// when it's ready, join if we got a room from the URL
webrtc.on('readyToCall', function () {
	webrtc.joinRoom(room);
});

webrtc.on('addMute', function(){
	if($('.rtcVideoCOntainer .mute').length < 1){
		$('.rtcVideoContainer').append('<a class="mute fa fa-microphone" data-action="mute" title="audio mute">&nbsp;</a><a class="videoMute fa fa-power-off" data-action="mute" title="video mute">&nbsp;</a>');
	}
});

webrtc.on('joinedRoom', function( name ){
	webrtc.emit('addMute');
	//localSrc = $('#localVideo').attr('src');
});

webrtc.on('videoRemoved', function(video, peer) {
	var localVideo = $('#localVideo');
	var src2 = localVideo.attr('src');

	if($(video).attr('id').indexOf('incoming') > 0) { localVideo.attr('src', localSrc); }
});

webrtc.on('mute', function(){
	$('.mute').data('action', 'unmute').removeClass('fa-microphone').addClass('fa-microphone-slash muted');
	webrtc.mute();
});
webrtc.on('unmute', function(){
	$('.mute').data('action', 'mute').removeClass('fa-microphone-slash muted').addClass('fa-microphone');
	webrtc.unmute();
});

webrtc.on('pauseVideo', function(){
	webrtc.pauseVideo();
	$('.videoMute').data('action', 'mute').addClass('muted');
});
webrtc.on('resumeVideo', function(){
	webrtc.resumeVideo();
	$('.videoMute').data('action', 'unmute').removeClass('muted');
});



$(document).ready(function($){

  $('form#roomChange').on('change', function(e){
    $('form#roomChange').submit();
  });

  $('body').on('click', '.mute', function(e){
  	var action = $(this).data('action');
  	if(action == 'mute'){
		webrtc.emit('mute');
  	} else {
	  	webrtc.emit('unmute');
  	}
  });
  
  $('body').on('click', '.videoMute', function(e){
  	var action = $(this).data('action');
  	if(action == 'mute'){
		webrtc.emit('resumeVideo');
  	} else {
	  	webrtc.emit('pauseVideo'); 	
  	}
  });
  
  /*
$('#remoteVideos').on('click', 'video', function(){
  	var localVideo = $('#localVideo');
	var src = $(this).attr('src');
	var src2 = localVideo.attr('src');
	
	localVideo.attr('src', src);
	$(this).attr('src', src2);
  });
*/

});
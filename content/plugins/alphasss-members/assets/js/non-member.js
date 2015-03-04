jQuery(document).ready(function($) {

	function successAlert(message, close)
	{
		return baseAlert(message, 'alert-success', close);
	}

	function infoAlert(message, close)
	{
		return baseAlert(message, 'info', close);
	}

	function dangerAlert(message, close)
	{
		return baseAlert(message, 'alert-danger', close);
	}

	function baseAlert(message, cls, close)
	{
		el = $('<div role="alert">');

		if (close === true) {
			el.append('<button class="close" aria-label="Close" data-dismiss="alert" type="button"><span aria-hidden="true">×</span></button>');
		}

		el.addClass('alert alert-dismissible fade in');

		el.addClass(cls).append('<div class="alert-content">' + message + '</div>');
		
		return el;
	}

	var p = PUBNUB.init(php_vars.pubnub);
	var requestor_uuid = p.uuid();
	var code = false;

	// This event Fires when a new User has Joined.
	p.events.bind( 'presence-user-join', function(uuid) {
	
		$('#'+uuid).find('.member-offline').hide();
		$('#'+uuid).find('.member-online').show();
		$('#'+uuid).find('.action').show();
	} );
	// This event Fires when a new User has Left.
	p.events.bind( 'presence-user-leave', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
			message = 'Sorry but the member ' + nickname + ' went offline just a moment ago. Here\'s what you can do:<br />';
			message += '<p>&nbsp;&nbsp;1. The fastest: Request invitation from anybody who is online. You\'ll your code within seconds.</p>';
			message += '<p>&nbsp;&nbsp;2. Post your invitation request in the general forum. Someone will read it and send you invitation.</p>';

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	p.events.bind( 'presence-user-timeout', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
			message = 'Sorry but the member ' + nickname + ' went offline just a moment ago. Here\'s what you can do:<br />';
			message += '<p>&nbsp;&nbsp;1. The fastest: Request invitation from anybody who is online. You\'ll your code within seconds.</p>';
			message += '<p>&nbsp;&nbsp;2. Post your invitation request in the general forum. Someone will read it and send you invitation.</p>';

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	p.subscribe({
		channel: 'onlineUsers',
		callback: function(m) {
			console.log(m);
		},
		presence: function(details){
			var uuid = 'uuid' in details && (''+details.uuid).toLowerCase();

			if ('action' in details && uuid) p.events.fire(
				'presence-user-' + details.action, uuid
			);
		}
	});

	$('.channel-logout').click(function(){
		p.unsubscribe({
			channel: 'onlineUsers' 
		});
	});

	$('#wp-admin-bar-logout a').click(function(){

		p.unsubscribe({
			p: 'onlineUsers' 
		});
	});

	p.subscribe({
		channel: requestor_uuid + '_invitation_codes',
		callback: function(m) {
			console.log(m);
			if (! code) {

				nickname = $('#' + m.uuid + ' .item-title').text();
				code     = m.invitation_code;

				$('#' + m.uuid + ' .text-success').text('Invitation Code: ' + m.invitation_code);
				message = nickname + ' has sent you and invitation code:<br />' 
				message += m.invitation_code + '<br />';
				message += 'Write it down, and use it to <a href="/register/">register now</a>. The code will expire in 24 hours.';
				$('#alerts').append(successAlert(message , true));
			}
		}
	});

	$('.request-invitation').click(function(){
		// Detect username
		var nickname = $(this).parent().parent().parent().find('.item .item-title a').text();
		var uuid     = $(this).parent().parent().parent().attr('id');
		var el       = $(this);
		
		$('#alerts').append(successAlert(php_vars.i18n.RequestSent, true));

		$(this).hide();
		$(this).parent().append('<b class="text-success">' + php_vars.i18n.RequestSentShort + '</b>');

		uuid = $(this).parent().parent().parent().attr('id');

		p.publish({
			channel: uuid + '_invitation_request',
			message: {
				'requestor_uuid': requestor_uuid,
				'requestor_nickname': php_vars.nickname
			},
			callback: function(m) {
				console.log(m);
			}
		});

		return false;
	});
});
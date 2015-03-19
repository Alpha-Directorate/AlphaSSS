jQuery(document).ready(function($) {

	// If pre-member is logged in
	if (php_vars.show_top_alert === '1') {
		$("#top-alerts").append(successAlert('<div class="alert-content">' + php_vars.i18n.TopAlert + '</div>', true));
	}

	var requestor_uuid = uuid;
	var code = false;

	// This event Fires when a new User has Joined.
	pubnub.events.bind( 'presence-user-join', function(uuid) {
		$('#'+uuid +' .member-offline').hide();
		$('#'+uuid +' .member-online').show();
		$('#'+uuid + ' .action').show();
	} );
	// This event Fires when a new User has Left.
	pubnub.events.bind( 'presence-user-leave', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
			
			message = php_vars.i18n.UserLeaveAlphass.replace('%s', nickname);

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	pubnub.events.bind( 'presence-user-timeout', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();

		// User leaved just a moment ago
		if ($('#'+uuid).find('.text-success').text()) {
			nickname = $('#' + uuid + ' .item-title').text();
		
			message = php_vars.i18n.UserLeaveAlphass.replace('%s', nickname);

			$('#alerts').append(dangerAlert(message , true));
		}
		$('#'+uuid).find('.action').hide();
	} );

	pubnub.subscribe({
		channel: requestor_uuid + '_invitation_codes',
		callback: function(m) {
			if (! code) {

				nickname = $('#' + m.uuid + ' .item-title').text();
				code     = m.invitation_code;

				$('#' + m.uuid + ' .text-success').text('Invitation Code: ' + m.invitation_code);

				// Pass invintation code to cookies
				$.cookie("invintation_code", m.invitation_code, { path: '/register-pre-member'});

				message = sprintf(php_vars.i18n.InvitationCodeGetAlert, nickname, m.invitation_code);
				$('#alerts').append(successAlert(message , true));
			}
		},
		disconnect : function() {
			pubNubErrorAlert()
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

		pubnub.publish({
			channel: uuid + '_invitation_request',
			message: {
				'requestor_uuid': requestor_uuid,
				'requestor_nickname': php_vars.nickname
			},
			callback: function(m) {},
			disconnect : function() {
				pubNubErrorAlert()
			}
		});

		return false;
	});
});
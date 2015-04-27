jQuery(document).ready(function($) {

	var invitation_requests = [];

	function showInvitationRequestPopUp()
	{
		$('#invitation-code-modal').modal('hide');

		setTimeout(function(){ 
			if (invitation_requests.length > 0) {
				alert_data = invitation_requests[0];

				if ( alert_data.requestor_nickname ){
					$('#modal-nickname').text(', ' + alert_data.requestor_nickname + ',');
				} else {
					$('#modal-nickname').text();
				}
				$('#modal-code').text(alert_data.invitation_code);

				$('#invitation-code-modal').modal('show');
			}
		}, 600);
	}

	$('#wp-admin-bar-logout a').click(function(){
		pubnub.unsubscribe({
			channel: 'onlineUsers'
		});
	});

	$('#invitation-code-modal .close').click(function(){
		alert_data = invitation_requests[0];

		invitation_requests.shift();

		if (invitation_requests.length > 0) {
			showInvitationRequestPopUp();
		} else {
		   $('#invitation-code-modal').modal('hide');
		}
	});

	$('#deliver-invitation-code').click(function(){
		alert_data = invitation_requests[0];

		pubnub.publish({
			channel: alert_data.requestor_uuid + '_invitation_codes',
			message: alert_data,
			callback: function(m) {
				invitation_requests.shift();

				if (invitation_requests.length > 0) {
					showInvitationRequestPopUp();
				} else {
				   $('#invitation-code-modal').modal('hide');
				}
			}
		});

		return false;
	});

	pubnub.subscribe({
		channel: uuid + '_invitation_request',
		callback: function(m) {
			var message = m;

			// Setup request params
			params = {
				action: "get_invitation_code",
				requestor_nickname: m.requestor_nickname
			};

			$.post(ajaxurl, params, function(data) {

				invitation_requests.push({
					'requestor_uuid': message.requestor_uuid,
					'requestor_nickname': message.requestor_nickname,
					'invitation_code': data.data.invitation_code,
					'uuid': uuid
				});

				if (invitation_requests.length == 1) {
					showInvitationRequestPopUp();
				}
			}, "json");
		},
		disconnect : function() {
			pubNubErrorAlert();
		}
	});
});
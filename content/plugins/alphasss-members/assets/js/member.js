jQuery(document).ready(function($) {

	data = {
		action: "get_uuid"
	};

	$.post(ajaxurl, data, function(data){

		var uuid = php_vars.pubnub.uuid;

		var pubnub = PUBNUB.init(php_vars.pubnub);

		var invitation_requests = [];

		function showInvitationRequestPopUp()
		{
			if (invitation_requests.length > 0) {
				alert_data = invitation_requests[0];

				if ( alert_data.requestor_nickname ){
					$('#modal-nickname').text(',' + alert_data.requestor_nickname + ',');
				} else {
					$('#modal-nickname').text();
				}
				$('#modal-code').text(alert_data.invitation_code);

				$('#invitation-code-modal').modal('show');
			}
		}

		$('.channel-logout').click(function(){
			pubnub.unsubscribe({
				channel: 'onlineUsers' 
			});
		});

		$('#wp-admin-bar-logout a').click(function(){
			pubnub.unsubscribe({
				channel: 'onlineUsers'
			});
		});

		$('#deliver-invitation-code').click(function(){
			alert_data = invitation_requests[0];

			pubnub.publish({
				channel: alert_data.requestor_uuid + '_invitation_codes',
				message: alert_data,
				callback: function(m) {
					invitation_requests.shift();

					$('#invitation-code-modal').modal('hide');

					if (invitation_requests.length > 0) {
						showInvitationRequestPopUp();
					}
				}
			});
		});

		pubnub.subscribe({
			channel: 'onlineUsers',
			callback: function(m) {},
			heartbeat: 10
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
			}
		});
	},"json");
});
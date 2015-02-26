jQuery(document).ready(function($) {

	data = {
		action: "get_uuid"
	};

	$.post(ajaxurl, data, function(data){

		var uuid = data.data.user.username;
		var pubnub = PUBNUB.init({
			'publish_key': 'pub-c-bd645d1e-f4aa-4719-9008-d14e29514bab',
			'subscribe_key': 'sub-c-8e1b190a-b033-11e4-83d7-0619f8945a4f',
			'uuid': uuid
		});

		var invitation_requests = [];

		function showInvitationRequestPopUp()
		{
			if (invitation_requests.length > 0) {
				alert_data = invitation_requests[0];

				//@todo add nickname here
				$('#modal-nickname').text();
				$('#modal-code').text(alert_data.invitation_code);

				$('#invitation-code-modal').modal('show');
			}
		}

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
					action: "get_invitation_code"
				};

				$.post(ajaxurl, params, function(data) {

					invitation_requests.push({
						'requestor_uuid': message.requestor_uuid,
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
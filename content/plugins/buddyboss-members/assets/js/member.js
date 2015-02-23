jQuery(document).ready(function($) {

	data = {
		action: "get_uuid"
	};

	$.post(ajaxurl, data, function(data){

		uuid = data.data.user.username;

		var pubnub = PUBNUB.init({
			'publish_key': 'pub-c-bd645d1e-f4aa-4719-9008-d14e29514bab',
			'subscribe_key': 'sub-c-8e1b190a-b033-11e4-83d7-0619f8945a4f',
			'uuid': uuid
		}); 

		pubnub.subscribe({
			channel: 'onlineUsers',
			callback: function(m) {
				console.log(m);
			},
			heartbeat: 10
		});

		pubnub.subscribe({
			channel: uuid + '_invitation_request',
			callback: function(m) {
				console.log(m);
			}
		});
	},"json");
});
jQuery(document).ready(function($) {

	function successAlert(message, close)
	{
		return baseAlert(message, 'alert-success', close);
	}

	function infoAlert(message, close)
	{
		return baseAlert(message, 'info', close);
	}

	function errorAlert(message, close)
	{
		return baseAlert(message, 'error', close);
	}

	function dangerAlert(message, close)
	{
		return baseAlert(message, 'danger', close);
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

	var p = PUBNUB.init({
		'publish_key': 'pub-c-bd645d1e-f4aa-4719-9008-d14e29514bab',
		'subscribe_key': 'sub-c-8e1b190a-b033-11e4-83d7-0619f8945a4f'
	});

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
		$('#'+uuid).find('.action').hide();
	} );

	p.events.bind( 'presence-user-timeout', function(uuid) {
		$('#'+uuid).find('.member-offline').show();
		$('#'+uuid).find('.member-online').hide();
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

	$('.request-invitation').click(function(){
		// Detect username
		var nickname = $(this).parent().parent().parent().find('.item .item-title a').text();
		var uuid     = $(this).parent().parent().parent().attr('id');

		var message  = "Okay! Great, we have sent your request to " + nickname + ".<br />";
		message += "In a couple of seconds, we will display your code in this window, right here.";
		
		$('#alerts').append(successAlert(message, true));

		$(this).hide();
		$(this).parent().append('<b class="text-success">Request sent</b>');

		uuid = $(this).parent().parent().parent().attr('id');

		p.publish({
			channel: uuid + '_invitation_request',
			message: 'code',
			callback: function(m) {
				console.log(m);
			}
		});

		return false;
	});
});
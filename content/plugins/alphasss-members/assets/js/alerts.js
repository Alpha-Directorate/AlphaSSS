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

function pubNubErrorAlert(callback)
{
	if ($('#top-alerts .connection-error').length == 0){
		$('#top-alerts').append(baseAlert(php_vars.i18n.ConnectionError,'alert-danger connection-error' ,true));
	}

	if (typeof(callback) === 'function') {
		callback();
	}
}

/**
* @param string Alert message
* @param string Alert state class
* @param boolean Display close alert button
*/
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

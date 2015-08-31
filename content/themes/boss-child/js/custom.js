jQuery(document).ready(function($) {
	
	function reposition() {
		var modal = $(this),
			dialog = modal.find('.modal-dialog');
			modal.css('display', 'block');

			// Dividing by two centers the modal exactly, but dividing by three 
			// or four works better for larger screens.
			dialog.css("margin-top", Math.max(0, ($(window).height() - dialog.height()) / 2));
	}

	// Reposition when a modal is shown
	$('.modal').on('show.bs.modal', reposition);
	$('[data-toggle="tooltip"]').tooltip();

	$('#subnav').append('<div id="profile-alerts"></div>');

	$('#adminbar-links .screen-reader-shortcut').hide();
});
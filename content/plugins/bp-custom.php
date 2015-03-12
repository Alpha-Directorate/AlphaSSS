<?php
	// Remove gavatar from buddypress
	add_filter('bp_core_fetch_avatar_no_grav', '__return_true');
?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/wp/' );
}

define( 'DB_NAME', getenv( 'DB_NAME' ) );
define( 'DB_USER', getenv( 'DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'DB_HOST' ) );
define( 'WP_MEMORY_LIMIT', '96M' );

require_once(ABSPATH . "/wp-load.php");

// Load all the admin APIs, for convenience
require ABSPATH . 'wp-admin/includes/admin.php';
//--
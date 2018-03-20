<?php
// If uninstall called directly and not from wordpress exit!
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit;

// delete the options
delete_option( 'pai_activated_plugins' );
delete_option( 'pai_display_relative_date' );
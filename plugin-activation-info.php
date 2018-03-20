<?php
/*
 * Plugin Name: Plugin Activation Info
 * Plugin URI: 
 * Description: This plugin creates a new column to dashboard->plugins "Last Activated By Username" which tells you when and who activated the plugin last time.
 * Version: 1.0
 * Author: Ganesh Veer
 * Author URI: 
 * License: GPLv2
 * Text Domain: pai_date
 * Domain Path: /languages/
 */

/**
 * Main plugin class
 */
class Plugins_Activation_Info {

	/**
	 * Holds the deactivation & activation date for all plugins.
	 */
	private $options = array();

	/**
	 * Constructor.
	 * Sets up activation hook, localization support and registers some essential hooks.
	 * @return Plugin_Activation_Info
	 */
	public function __construct() {
		// Register essential hooks
		add_filter( 'manage_plugins_columns', 		array( $this, 'plugins_columns' ) );
		add_action( 'activate_plugin', 				array( $this, 'pai_plugin_status_changed' ) );
		add_action( 'deactivate_plugin', 			array( $this, 'pai_plugin_status_changed' ) );
		add_action( 'admin_head-plugins.php', 		array( $this, 'column_css_styles' ) );
		add_action( 'manage_plugins_custom_column', array( $this, 'activated_columns' ), 10, 3 );

		// Load our text domain
		load_plugin_textdomain( 'pai_date', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		// Get the options, and keep around for later use
		$this->options = get_option( 'pai_activated_plugins', array() );

		// Runs on activation only
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
	}

	/**
	 * Runs when a plugin changes status, and adds the de/activation timestamp
	 * to $this->options, then stores it in the options table.	 * 
	 */
	public function pai_plugin_status_changed( $plugin ) {
 		$current_user = wp_get_current_user();
		$this->options[ $plugin ] = array(
			'status' 	=> current_filter() == 'activate_plugin' ? 'activated' : 'deactivated',
			'timestamp' => current_time( 'mysql' ),
			'username' => $current_user->user_login
		);
		update_option( 'pai_activated_plugins', $this->options );
	}

	/**
	 * Sets up the column with headings.
	 * 	 
	 */
	public function plugins_columns( $columns ) {
		global $status;

		// If we're either on the Must Use or Drop-ins tabs, no need to show the column
		if ( ! in_array( $status, array( 'mustuse', 'dropins' ) ) )
			if ( ! in_array( $status, array( 'recently_activated', 'inactive' ) ) )
				$columns['last_activated'] = __( 'Last Activated By Username', 'pai_date' );
			else
				$columns['last_deactivated'] = __( 'Last Deactivated By Username', 'pai_date' );

		return $columns;
	}

	/**
	 * Outputs the date & time when this plugin was last activated and by which user. Repeats for all plugins.
	 */
	public function activated_columns( $column_name, $plugin_file, $plugin_data ) {
		$current_plugin = &$this->options[ $plugin_file ];

		switch ( $column_name ) {
			case 'last_activated':
				if ( ! empty( $current_plugin ) )
					echo $current_plugin['timestamp']." By ".$current_plugin['username'];
				break;
			case 'last_deactivated':
				if ( ! empty( $current_plugin ) && $current_plugin['status'] == 'deactivated' )
					echo $current_plugin['timestamp']. " By " .$current_plugin['username'];;
				break;
		}
	}

	/**
	 * Set our column's width so it's more readable.
	 */
	public function column_css_styles() {
		?>
		<style>#last_activated, #last_deactivated { width: 20%; }</style>
		<?php
	}

	/**
	 * Runs on activation, registers a few options for this plugin to operate.
	 */
	public function activation() {
		add_option( 'pai_activated_plugins' );
	}
}
// Initiate the plugin. Access everywhere using $global plugin_activation_date
$GLOBALS['plugins_activation_info'] = new Plugins_Activation_Info;
<?php
/*
Plugin Name: Revitalize Orders
Version: 0.1
Plugin URI: https://github.com/tenkabuto/Revitalize-Orders
Author: Brandon Hall
Author URI: http://brandon.zeroqualms.net
Description: Transitioning from Jigoshop to WooCommerce can leave your orders' statuses grey and dead-looking. This helps you put back what went missing: your orders' true statuses.
*/
global $wp_version;
  
$exit_msg = 'Revitalize Orders requires both the plugin "WooCommerce" and WordPress 3.5 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';

if ( version_compare($wp_version, "3.5", "<") && class_exists('Woocommerce') ) {
  exit($exit_msg);
}

class RevitalizeOrders {
	var $plugin_url;
	
	// For the time being, let's effectively setup a useless page
	function page_handler() {
		print '<div class="wrap">';
		
		// Query all orders
		$ro_main_query = new WP_Query();
		
		$ro_main_query->query(array(
			'post_type' => 'shop_order',
			'tax_query' => array(
				array(
					'taxonomy' => 'shop_order_status',
					'field' => 'slug'
				)),
			'posts_per_page' => '-1'
			)
		);
		
		// the main query's loop
		while ($ro_main_query->have_posts()) : $ro_main_query->the_post();

			// Individual checks go here
		
		endwhile;
		
		print '</div>';
	}
	
	// Initialize the plugin
	function RevitalizeOrders() {
		$this->plugin_url = trailingslashit( WP_PLUGIN_URL.'/'.dirname( plugin_basename(__FILE__) ));
		
		// Add page
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}
	
	// Hook the options page
	function admin_menu() {
		// The designation of add_MANAGEMENT_page causes the menu item to be listed under the Tools menu!
		add_management_page('Revitalize Orders Output', 'Revitalize Orders', 'edit_posts', basename(__FILE__), array(&$this, 'page_handler'));
	}
}

// Create a new instance of the class
$RevitalizeOrders = new RevitalizeOrders();
if (isset($RevitalizeOrders)) {
	// Register the activation function by passing the reference to my instance
	register_activation_hook( __FILE__, array(&$RevitalizeOrders, 'RevitalizeOrders') );
}
?>
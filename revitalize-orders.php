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

global $woocommerce;

class RevitalizeOrders {
	var $plugin_url;
	
	// For the time being, let's effectively setup a useless page
	function page_handler() {
		print '<div class="wrap">
			<ul>';
		
		// Query all orders
		$first_query = new WP_Query();
		
		$first_query->query(array(
			'post_type' => 'shop_order',
			'posts_per_page' => '-1'
			)
		);
		
		$are_alive = array();
		
		while ($first_query->have_posts()) : $first_query->the_post();
			
			// $are_alive[] = get_the_ID();
			
			the_terms( $post->ID, 'shop_order_status', 'Order Status: ', '"', '"' );
		
		endwhile;
		
		// Query all orders BUT those contained in the $are_alive array
		$second_query = new WP_Query();
		
		$second_query->query(array(
			'post_type' => 'shop_order',
			'post__not_in' => $are_alive,
			'posts_per_page' => '-1'
			)
		);
		
		while ($second_query->have_posts()) : $second_query->the_post();
			if ( is_object_in_term( get_the_ID(), 'shop_order_status' ) ) :
				$terms = 'YES';
			else :
				$terms = 'NO';
			endif;
			
			echo "<li>Order #".get_the_ID()." is a zombie!</li>";
		
		endwhile;
		
		print '</ul>
		</div>';
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
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
		print '<div class="wrap">
			<ul>';
		
		// Query all orders
		$main_query = new WP_Query();
		
		$main_query->query(array(
			'post_type' => 'shop_order',
			'tax_query' => array(
					'taxonomy' => 'shop_order_status',
					'field' => 'slug',
					'terms' => 'pending'
				),
			'posts_per_page' => '-1'
			)
		);
		
		while ($main_query->have_posts()) : $main_query->the_post();

			global $wpdb;

			// The Query
			$comments = $wpdb->get_results ("SELECT *
				FROM $wpdb->comments
				WHERE comment_approved = '1' AND comment_type = 'order_note' AND comment_post_ID=".get_the_ID()."
				ORDER BY comment_date_gmt DESC
				LIMIT 2");

			// Comment Loop
			if ( $comments ) {
				foreach ( $comments as $comment ) {
					// Get comment info and replace as status intended
					$vital_status = preg_replace("/.*Order status changed from .* to (.*)./", "$1", $comment->comment_content);
					echo '<li>Order #' . $comment->comment_post_ID . ' would be "' . $vital_status . '"!</li>';
				}
			} else {
				echo 'No comments found.' . $comments->comment_content;
			}
		
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
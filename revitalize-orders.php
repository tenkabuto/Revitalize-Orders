<?php
/*
Plugin Name: Revitalize Orders
Version: 0.2
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
		
		$first_query = new WP_Query();
		
		// Query all orders
		$first_query->query(array(
			'post_type' => 'shop_order',
			'posts_per_page' => '-1'
			)
		);
		
		$not_alive = array();
		
		while ($first_query->have_posts()) : $first_query->the_post();
			
			// Finds all orders lacking signs of life
			if ( !is_object_in_term( get_the_ID(), 'shop_order_status' ) ) :
				$not_alive[] = get_the_ID();
			endif;
		
		endwhile;
		
		$second_query = new WP_Query();
		
		// Query all orders BUT those contained in the $not_alive array
		$second_query->query(array(
			'post_type' => 'shop_order',
			'post__in' => $not_alive,
			'posts_per_page' => '-1'
			)
		);
		
		while ($second_query->have_posts()) : $second_query->the_post();
			
			global $wpdb;

			$offset = '0';
		
			do {
				if ( $offset != '0' ) {
					$db_offset = " OFFSET " . $offset;
				} else {
					$db_offset = '';
				}
				
				// Check comments until a solid match is found
				// The Query
				$comments = $wpdb->get_results ("SELECT *
					FROM $wpdb->comments
					WHERE comment_approved = '1' AND comment_type = 'order_note' AND comment_post_ID=".get_the_ID()."
					ORDER BY comment_date_gmt DESC
					LIMIT 1" . $db_offset);

				// Looping through comments to find last true indication of life
				if ( $comments ) {
					foreach ( $comments as $comment ) {

						$vital_check = preg_match("/.*Order status changed from .* to (.*)./", $comment->comment_content);
						$vital_status = preg_replace("/.*Order status changed from .* to (.*)./", "$1", $comment->comment_content);
						
						// Check if comment is a match to Woo template
						if ( $vital_check == '1' ) {
						
							// Extract $vital_status
							$order = new WC_Order(get_the_ID());
							
							// Update order status with extracted $vital_status
							$order->update_status($vital_status, 'Revitalized!');
							
							echo '<li>Order #'.get_the_ID().' has been revitalized, due to its previously logged state of "'.$vital_status.'"!';

						} else {
							$offset++;
						}
						
						break;
						
					}
				}
				
				break;
				
			}
			while ($vital_check === '0');
		
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
<?php
/*
Plugin Name: WooCommerce Invoice Sharing
Plugin URI: http://wpbackoffice.com/plugins/woocommerce-invoice-sharing
Description: Enable user to share their invoices with as many email address as they like, all from the order review and view order pages.
Version: 1.0.2
Author: J. Tyler Wiest
Author URI: http://wpbackoffice.com
License: GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'share-order-admin.php' );

/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 0.1
 * @param	array $email_classes available email classes
 * @return 	array filtered available email classes
 */
add_filter( 'woocommerce_email_classes', 'wso_add_expedited_order_woocommerce_email' );

function wso_add_expedited_order_woocommerce_email( $email_classes ) {

	// include our custom email class
	require( 'classes/class-wc-share-customer-order.php' );

	// add the email class to the list of email classes that WooCommerce loads
	$email_classes['WC_Share_Order'] = new WC_Share_Order();

	return $email_classes;
}

/*
*	Activation Hook - add wso_options if it doesn't already
* 	exist.
*
*	@return void
*/
register_activation_hook( __FILE__, 'wso_activation_hook' );

function wso_activation_hook() {
	
	$options = get_option( 'wso_options' );
	
	if ( $options == false ) {
	
		$defaults = array (		
			'form_title'		=> 'Share Your Invoice',
			'help_message'		=> 'Enter an email address to share your invoice.',
			'button_text' 		=> 'Share Invoice',
			'input_placeholder' => 'Enter Email',
			'success_message' 	=> 'Success! Your invoice has been sent, send another?',
			'email_message'		=> 'Invalid email address, please try again.',
			'order_message'		=> 'Invalid order number, please refresh and try again.',
			'account_message'	=> 'Invalid account, please make sure you are logged in and try again.',
			'default_message'	=> 'Something went wrong, please refresh the page and try again.',
			'order_received' 	=> 'on',
			'order_received_top'=> 'on',
			'view_order'	 	=> 'on',
		);
	
		add_option( 'wso_options', $defaults, '', false );
	}

}

/*
*	Thank You Page Filter - add form to order received page
*	if user checks it on the settings page.
*
*	@return void
*/
add_filter( 'woocommerce_thankyou','wso_thankyou_page' );

function wso_thankyou_page() {
	$options = get_option( 'wso_options' );
	extract( $options );

	if ( $order_received == 'on' and $order_received_top == 'on' ) {
		wso_print_share_order_form();
	}
}

/*
*	Display Share Form Filter - add form after the details
*	page on as configured on the settings page.
*
*	@return void
*/
add_filter( 'woocommerce_order_details_after_order_table','wso_order_detail_page' );

function wso_order_detail_page() {
	$options = get_option( 'wso_options' );
	extract( $options );
	
	// Display on View Order Page
	if ( is_page( 'my-account' ) and $view_order == 'on' ) {
		wso_print_share_order_form();
		
	// Display on bottom of Order Received Page
	} elseif ( is_page( 'checkout' ) and $order_received == 'on' and $order_received_top != 'on' ) {
		wso_print_share_order_form();
	}
}

/*
*	Print Share Order Input Box
*
*	@return void Prints Share Order form
*/
function wso_print_share_order_form() {
	$options = get_option( 'wso_options' );
	extract( $options );
	
	?>	
	<div class="share_order_container">
		<h2><?php echo $form_title; ?></h2>
		<label><?php echo $help_message; ?></label>
		<input type="email" name="order_share_email" id="order_share_email" placeholder="<?php echo $input_placeholder ?>" />
		<input type="submit" value="<?php echo $button_text; ?>" id="submit_order_share" class="button button-primary button-large" />
	</div>
	<?php
}

/*
*	Enque Order Input Box Script for the order received page
*
*	@return void
*/
add_action( 'template_redirect', 'wso_add_share_order_script' );

function wso_add_share_order_script() {
	$options = get_option( 'wso_options' );
	global $wp_query;
	extract( $options );
	$display_script = false;
	
	// Test for the page and if it is activated
	if ( is_page( 'checkout' ) and $order_received == 'on' ) {
		$display_script = true;
	} elseif ( is_page( 'my-account') and $view_order == 'on' ) {
		$display_script = true;
	}
		
	if ( $display_script == true ) {
		
		// Enque the style
		wp_enqueue_style( 
			'wso_styles', 
			plugin_dir_url( __FILE__ ) . 'css/share-order-styles.css'
		);
		
		// Enque the script
		wp_enqueue_script( 'shareorder',
			plugin_dir_url( __FILE__ ) . 'js/share-order.js',
			array('jquery'), '1.0.0', true
		);
			
		// Get page protocal
		$protocol = isset( $_SERVER["HTTPS"]) ? 'https://' : 'http"//';
		
		// Extract the message options
		extract( get_option( 'wso_options' ) );
		
		// Output admin-ajax.php URL with sma eprotocol as current page
		$params = array (
			'ajaxurl' 			=> admin_url( 'admin-ajax.php', $protocol ),
			'success_message' 	=> $success_message,
			'email_message' 	=> $email_message,
			'order_message' 	=> $order_message,
			'account_message' 	=> $account_message,
			'default_message' 	=> $default_message,
		);	
		
		wp_localize_script( 'shareorder', 'shareorder', $params );
	}
}

/*
*	Include Admin Styles
*
*	@return void
*/
add_action( 'admin_enqueue_scripts', 'wso_admin_styles' );

function wso_admin_styles( $hook_suffix ) {

	if($hook_suffix == 'woocommerce_page_share-order-admin') {
		wp_enqueue_style( 
			'wso_admin_styles', 
			plugin_dir_url( __FILE__ ) . 'css/share-order-admin.css'
		);
	}
}

/*
*	Share Order Ajax Handler
*
*	@return array results 
*
*/
add_action( 'wp_ajax_nopriv_wso_share_order', 'wso_share_order_nopriv' );
add_action( 'wp_ajax_wso_share_order', 'wso_share_order' );

function wso_share_order() {
	global $woocommerce;
	
	$email = stripslashes( $_REQUEST['email'] );
	$order = intval( stripslashes( $_REQUEST['order'] ) );
	$user = wp_get_current_user();

	// Get order to validate against
	if ( is_int( $order ) ){
		$order_result = new WC_Order( $order );
	} 
	
	// Validate the Email
	if ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  		$result['type'] = "invalid_email";
  	
  	// Validate Order Exists
	} elseif( $order_result->id != $order ) {
		$result['type'] = "invalid_order";

	// Validate This is the Current User's Account
	} elseif ( $user->data->user_email != $order_result->billing_email ) {
		$result['type'] = 'invalid_account';
		
	// If everything checks out, send the email
	} else {
	
		$mailer = new WC_Emails();		
		$invoice = $mailer->emails['WC_Share_Order'];
		$invoice->trigger( $order, $email );
	
		$result['type'] = "success";
	}

	// Send results back
    $result_json = json_encode($result);
    echo $result_json;
	
	// reset jQuery
	wp_reset_query();
	die();
}

/*
*	Share Order Ajax Handler - NoPriv
*
*	@return array results will return invalid accoutn error
*	since the user won't be logged it.
*
*/
function wso_share_order_nopriv() {
	
	$result['type'] = 'invalid_account';
	
	// Send results back
    $result_json = json_encode($result);
    echo $result_json;
	
	// reset jQuery
	wp_reset_query();
	die();
}
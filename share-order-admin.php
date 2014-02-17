<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
*	Admin Page Setup - Allows users to control various 
*	options for the plugin.
*
*	@return void
*/
add_action( 'admin_menu', 'wso_add_admin_menu' );

function wso_add_admin_menu() {
    add_submenu_page(
    	'woocommerce', 
    	'Invoice Sharing', 
    	'Invoice Sharing', 
    	'edit_posts', 
    	basename(__FILE__), 
    	'wso_admin_menu'
    );
}

/*
*	Share Order Settings Page - Allows users to control 
*	various options for the plugin.
*
*	@return void Prints HTML form
*/
function wso_admin_menu() { ?>
	<div class="wrap wso_wrap">
		<h2>Invoice Sharing Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields('wso_options'); ?>
			<?php do_settings_sections('wso_options_page'); ?>
			<input name="Submit" type="submit" class="button button-primary button-large" value="Save Changes" />
		</form>
	</div><!-- .wrap -->
<?php
}

/*
*	Register WSO Admin Menu Settings
*	
*	@return void
*/
add_action('admin_init', 'wso_admin_init');

function wso_admin_init() {
	register_setting( 
		'wso_options', 
		'wso_options',
		'wso_validate_options' 
		);
	add_settings_section( 
		'wso_settings', 
		'', 
		'wso_settings_help_text', 
		'wso_options_page'); 
	add_settings_field(
		'wso_form_options',
		'Update Form Values',
		'wso_form_options',
		'wso_options_page',
		'wso_settings'
		);
	add_settings_field(
		'wso_messages_field',
		'Update Message Values',
		'wso_message_input',
		'wso_options_page',
		'wso_settings'
		);
	add_settings_field(
		'wso_output_select_field',
		'Upate Positioning',
		'wso_page_input',
		'wso_options_page',
		'wso_settings'
		);
}

/*
*	Settings Help Text
*	
*	@return void Prints HTML message
*/
function wso_settings_help_text() {
	// echo "Help Text";
}

/*
*	Messages Input Section - Allow the user to customize the 
* 	messages displayed throughout the plugin.
*
*	@return void Prints HTML form
*/
function wso_form_options() {

	$options = get_option( 'wso_options' );
	
	if ($options == false) {
		$options = array();
	}
	
	$defaults = array(
		'form_title'		=> 'Share Your Invoice',
		'help_message'		=> 'Enter an email address to share your invoice.',
		'button_text' 		=> 'Share Invoice',
		'input_placeholder' => 'Enter Email'
	);
	
	$options = wp_parse_args($options, $defaults);
	extract($options);
	
	?>
	<div id="wso_form_options">
		<label for="wso_options[form_title]">Form Title</label>
		<input type="text" name="wso_options[form_title]" value="<?php echo $form_title ?>" />
		
		<label for="wso_options[help_message]">Help Message</label>
		<input type="text" name="wso_options[help_message]" value="<?php echo $help_message ?>" />
		
		<label for="wso_options[button_text]">Button Text</label>
		<input type="text" name="wso_options[button_text]" value="<?php echo $button_text ?>" />
		
		<label for="wso_options[input_placeholder]">Input Placeholder</label>
		<input type="text" name="wso_options[input_placeholder]" value="<?php echo $input_placeholder ?>" />
	</div>
	<?php
}


/*
*	Messages Input Section - Allow the user to customize the messages displayed
* 	throughout the plugin.
*
*	@return void Prints HTML form
*/
function wso_message_input() {

	$options = get_option( 'wso_options' );
	
	if ($options == false) {
		$options = array();
	}
	
	$defaults = array(
		'success_message' 	=> 'Success! Your invoice has been sent, send another?',
		'email_message'		=> 'Invalid email address, please try again.',
		'order_message'		=> 'Invalid order number, please refresh and try again.',
		'account_message'	=> 'Invalid account, please make sure you are logged in and try again.',
		'default_message'	=> 'Something went wrong, please refresh the page and try again.'
	);
	
	$options = wp_parse_args($options, $defaults);
	extract($options);
	
	?>
	<div id="wso_message_options">
		
		<label for="wso_options[success_message]">Success Message</label>
		<input type="text" name="wso_options[success_message]" value="<?php echo $success_message ?>" />
		
		<label for="wso_options[email_message]">Invalid Email Message</label>
		<input type="text" name="wso_options[email_message]" value="<?php echo $email_message ?>" />
		
		<label for="wso_options[order_message]">Invalid Order Message</label>
		<input type="text" name="wso_options[order_message]" value="<?php echo $order_message ?>" />
		
		<label for="wso_options[account_message]">Invalid Account Message</label>
		<input type="text" name="wso_options[account_message]" value="<?php echo $account_message ?>" />
		
		<label for="wso_options[default_message]">Default Error Message</label>
		<input type="text" name="wso_options[default_message]" value="<?php echo $default_message ?>" />
	</div>
	<?php
}

/*
*	Page Options - Allow the user to decide which page to display
*	the form on.
*
*	@return void Prints html form
*/
function wso_page_input() {

	$options = get_option( 'wso_options' );
	
	if ($options == false) {
		$options = array();
	}
	
	$defaults = array(
		'order_received' 		=> 'on',
		'order_received_top' 	=> 'on',
		'view_order'			=> 'on',
	);
	
	$options = wp_parse_args($options, $defaults);
	extract($options);

	?>
	
	<div id="wso_page_options">
	
		<div>
			<input type="checkbox" name="wso_options[order_received]" <?php if ( $order_received == 'on' ) echo 'checked' ?> />
			<label for="wso_options[order_received]">Display on Order Received Page</label>
		</div>
		
		<div>
			<input type="checkbox" name="wso_options[order_received_top]" <?php if ( $order_received_top == 'on' ) echo 'checked' ?> />
			<label for="wso_options[order_received_top]">Top of Order Received Page? (Unchecked will display the form at the bottom)</label>
		</div>
		
		<div>
			<input type="checkbox" name="wso_options[view_order]" <?php if ( $view_order == 'on' ) echo 'checked' ?> />
			<label for="wso_options[view_order]">Display on View Order Page</label>
		</div>
	</div>
	
	<?php
}

/*
*	Validation Settings 
*	
*	@param	array	$input
*	@return	array	$input
*/
function wso_validate_options( $input ) {

	$input['form_title'] = strip_tags( $input['form_title'] );
	$input['help_message'] = strip_tags( $input['help_message'] );
	$input['button_text'] = strip_tags( $input['button_text'] );
	$input['input_placeholder'] = strip_tags( $input['input_placeholder'] );
	$input['success_message'] = strip_tags( $input['success_message'] );
	$input['email_message'] = strip_tags( $input['email_message'] );
	$input['order_message'] = strip_tags( $input['order_message'] );
	$input['account_message'] = strip_tags( $input['account_message'] );
	$input['default_message'] = strip_tags( $input['default_message'] );

	if ( ! isset( $input['order_received'] ) ) {
		$input['order_received'] = '';
	}
	
	if ( ! isset( $input['order_received_top'] ) ) {
		$input['order_received_top'] = '';
	}
	
	if ( ! isset( $input['view_order'] ) ) {
		$input['view_order'] = '';
	}

	return $input;
}
<?php
/**
 * Plugin Name:     Post/Page Edit Restriction Days
 * Plugin URI:      https://wordpress.org/plugins/postpage-edit-restriction-days/
 * Description:     Many companies or Website owners are constantly concerned about editors or other users inadvertently or knowingly editing or removing posts of others or their own after they are published. This WordPress plugin helps you restrict post or page edits after certain hours or days once they were published.  For example you set a 3 day time out after which the editors or other roles would not be able to edit what has been published in any shape or form.   Only the admin or authorized roles could go back and edit those posts once the time out days are expired.
 * Author:          JNamin
 * Author URI:      https://profiles.wordpress.org/josefnamin/
 * Text Domain:     postpage-edit-restriction-days
 * Domain Path:     /languages
 * Version:         1.2
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * @package         Days_Restriction
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'includes/class-postpage-edit-restriction-days.php';

/**
 * Plugin activation.
 */
function days_restriction_activation() {
	// If check pro plugin activated or not.
	if( class_exists( 'Days_Restriction_Pro' ) ) {
		// Deactivate contact form 7 plguin.
		deactivate_plugins( plugin_basename( __FILE__ ) );
		// Display error message.
		wp_die(
		    esc_html__( 'Please deactivate days restriction pro.', 'postpage-edit-restriction-days' ),
		    esc_html__( 'Plugin dependency check', 'postpage-edit-restriction-days' ),
		    array(
		        'back_link' => true,
		    )
		);

	}
}
register_activation_hook( __FILE__, 'days_restriction_activation' );

/**
 * Plugin deactivation.
 */
function days_restriction_deactivation() {
	// Deactivation code here.
}
register_deactivation_hook( __FILE__, 'days_restriction_deactivation' );

/**
 * Initialization class.
 */
function days_restriction_init() {
	global $days_restriction;
	$days_restriction = new Days_Restriction();
}
add_action( 'plugins_loaded', 'days_restriction_init' );

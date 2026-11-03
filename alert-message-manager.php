<?php
/**
 * Plugin Name: Alert Message Manager
 * Description: Puts alert messages on certain pages, for instance to indicate building closures.
 * Version: 1.0.0
 * Author: Coral Sheldon-Hess
 */

// make sure we're in a WordPress context
defined('ABSPATH') || exit;



// add a message (currently) above the header, which isn't ideal
function write_alert_message() {

    $options = get_option( 'amm_plugin_options', [] );
    // print the alert message if it's been set to "active"
    if (/*$options["alert_message_active"] == "true" 
        && */is_page( [ 'home', 'general-email-form', 'library-research-question-form', 'hours', 'reserve-a-room', 'contact-us', 'ask-us', 'peer-research-mentors', 'how-to-print' ] )
        && empty( $_COOKIE['lib_notification_dismissed'] )) {
          ?>
          <div class="notification notification-global">
          <div class="notification-global-inner">
            <div class="notification-global-content">
              <img class="notification-global-icon" src="//assets.library.colostate.edu/images/notifications/megaphone.svg" alt="">
              <p><?php echo esc_html__( $options["alert_message"], 'alert-message-manager' ); ?></p>
            </div>
            <button class="notification-global-close" aria-label="Dismiss notification">
              <img src="//assets.library.colostate.edu/images/notifications/notification-close.svg" alt="">
            </button>
          </div>
        </div>
        <?php
    }
}
// add_action actually does the printing, currently above the header
// (alert_message_hook() is in header.php)
add_action( 'alert_message_hook', 'write_alert_message' );



// adds the option to the Tools menu on the admin side, if the user is allowed to manage_options,
// and that option takes you to the plugin landing page via amm_plugin_options()
function amm_plugin_menu() {
    add_management_page( 
        'Alert Message Manager', // page title
        'Alert Message Manager', // menu title
        'manage_options',        // capability (of admin user)
        'alert-message-manager', // menu slug
        'amm_render_page'        // callback - function that outputs content of page
    );
}
// actually does the addition to the menu
add_action( 'admin_menu', 'amm_plugin_menu' );





// Register settings, sections, and fields
function amm_register_settings() {
    register_setting(
        'amm_settings_group',               // Option group (must match settings_fields())
        'amm_plugin_options',               // Option name in wp_options (array), will be santitized
        'amm_plugin_sanitize'               // Sanitization callback
    );
    
    add_settings_section(
        'amm_settings',                     // slug name to identify the settings section, used in add_settings_field
        'Alert Message Manager Settings',   // title of the section, shown as a heading
        '__return_false',                   // a callback could go here, but we don't need it
        'alert-message-manager'             // slug name of the page where the section will be shown
    );

    add_settings_field(
        'alert_message',                    // slug name to identify the field
        'Alert Message',                    // formatted title of the field, shown as label during output
        'get_amm_alert_message',            // function that fills the field with form output; function should echo its output
        'alert-message-manager',            // slug-name of page on which to show the section
        'amm_settings'                      // settings section the field goes in
        );                     
   
}
add_action( 'admin_init', 'amm_register_settings' );



// fills alert_message field via form output
function get_amm_alert_message() {
    $options = get_option( 'amm_plugin_options', [] );
    $value   = isset( $options['alert_message'] ) ? $options['alert_message'] : '';
    echo '<input type="text" name="amm_plugin_options[alert_message]"
           value="' . esc_attr( $value ) . '" class="regular-text" />';
}

// sanitizes the alert message; called by register_setting()
function amm_plugin_sanitize( $input ) {
    $clean = [];
    if ( isset( $input['alert_message'] ) ) {
        $clean['alert_message'] = sanitize_text_field( $input['alert_message'] );
    }
    return $clean;
}

// draws the page, called by add_management_page()
function amm_render_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'amm_settings_group' );            // group was set up in register_settings()
            do_settings_sections( 'alert-message-manager' );    // prints all the setting sections on the page with the slug alert-message-manager
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

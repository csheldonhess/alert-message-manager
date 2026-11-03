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

    // print the alert message if it's been set to "active"
    if (get_option("alert_message_active") == "true" 
        && is_page( [ 'home', 'general-email-form', 'library-research-question-form', 'hours', 'reserve-a-room', 'contact-us', 'ask-us', 'peer-research-mentors', 'how-to-print' ] )
        && empty( $_COOKIE['lib_notification_dismissed'] )) {
          ?>
          <div class="notification notification-global">
          <div class="notification-global-inner">
            <div class="notification-global-content">
              <img class="notification-global-icon" src="//assets.library.colostate.edu/images/notifications/megaphone.svg" alt="">
              <p><?php echo esc_html__( get_option("alert_message"), 'alert-message-manager' ); ?></p>
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
add_action( 'alert_message_hook', 'write_alert_message' );



// adds the option to the Tools menu on the admin side, if the user is allowed to manage_options,
// and that option takes you to the plugin landing page via amm_plugin_options()
function amm_plugin_menu() {
    add_management_page( 'Alert Message Manager', 'Alert Message Manager', 'manage_options', 'alert-message-manager', 'amm_plugin_options' );
}
// actually does the addition to the menu
add_action( 'admin_menu', 'amm_plugin_menu' );

// creates the plugin landing page (a form)
function amm_plugin_options() {
    //must check that the user has the required capability, again 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    // variables for the field and option names 
    $opt_name = 'alert_message'; // the alert message
    $active_name = 'alert_message_active'; // is the alert active?
    $hidden_field_name = 'submit_hidden'; // have we submitted the form?
    $msg_field_name = 'alert_message';
    $active_field_name = 'active';

    // Read in existing option value from database
    $opt_val = get_option( $opt_name );
    $active_val = get_option( $active_name);

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $msg_field_name ];
        $active_val = $_POST[ $active_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );
        update_option( $active_name, $active_val);

        // Put a "settings saved" message on the screen

    ?>
    <div class="updated"><p><strong><?php _e('settings saved.', 'alert-message-manager' ); ?></strong></p></div>
    <?php

    }

    // Now display the settings editing screen

    echo '<div class="wrap">';

    // header

    echo "<h1>" . __( 'Test of Alert Message Manager', 'alert-message-manager' ) . "</h1>";

    // settings form
    
    ?>
    <p>Please do not enter HTML into the <em>Alert message text</em> box. This is for plaintext alerts only.</p>
    <form name="form1" method="post" action="">
    <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

    <p><?php _e("Alert message text:", 'alert-message-manager' ); ?> 
    <input type="text" name="<?php echo $msg_field_name; ?>" value="<?php echo $opt_val; ?>">
    </p>

    <p><?php _e("Show alert:", 'alert-message-manager' ); ?><br> 
        <input type="radio" name="<?php echo $active_field_name; ?>" value="true" <?php if ($active_val == "true") { echo "checked"; }; ?>> <label for="Yes">Yes</label><br>
        <input type="radio" name="<?php echo $active_field_name; ?>" value="false" <?php if ($active_val == "false") { echo "checked"; }; ?>> <label for="No">No</label><br>
    </p>

    <p class="submit">
    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
    </p>

    </form>
    </div>

    <?php

}
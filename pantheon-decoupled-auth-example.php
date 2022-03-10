<?php
/**
 * Plugin Name:     Pantheon Decoupled Auth example
 * Plugin URI:      https://pantheon.io/
 * Description:     Example Application & content to demonstrate sourcing content from a Decoupled WordPress site using Application Passwords.
 * Author:          Pantheon
 * Author URI:      https://pantheon.io/
 * Text Domain:     pantheon_decoupled_auth_example
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Pantheon_Decoupled_Auth_example
 */

/**
 * Create a private post when activating the plugin.
 */
function pantheon_decoupled_auth_example_create_post() {
    $image_url = dirname(__FILE__) . '/chocolate-brownies.jpeg';
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents($file, $image_data);
    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $file);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    $example_post = [
        'post_title' => 'Private Example Post',
        'post_content' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.",
        'post_status' => 'private'
    ];
    $post_id = wp_insert_post($example_post);
    set_post_thumbnail($post_id, $attach_id);
}

/**
 * Create example application password.
 */
function pantheon_decoupled_auth_example_create_application_password() {
	$created = \WP_Application_Passwords::create_new_application_password('1', ['name' => 'Example Application']);
    set_transient( 'application_password_created', $created[0]);
}

/**
 * Show the Example App password.
 */
function app_password_admin_notice() {
    if( get_transient( 'application_password_created' ) ) {
		?>
		    <div class="notice notice-success notice-alt below-h2">
			    <strong>Pantheon Decoupled Auth Example</strong>
			    <p class="application-password-display">
				    <label for="new-application-password-value">
					    The password of the <strong>Example Application</strong> is:
				    </label>
				    <input type="text" class="code" value="<?php printf(esc_attr( \WP_Application_Passwords::chunk_password(get_transient( 'application_password_created' )) )); ?>" />
			    </p>
			    <p><?php _e( 'Be sure to save this in a safe location. You will not be able to retrieve it.' ); ?></p>
		    </div>
		<?php
        delete_transient('application_password_created');
    }
}

/**
 * Create example menu when activating the plugin.
 */
function pantheon_decoupled_auth_example_menu() {
    $menu = wp_get_nav_menu_object('Example Menu');
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Example Menu');
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' =>  __('Private Example Post'),
        'menu-item-classes' => 'private_example_post',
        'menu-item-url' => home_url( '/private-example-post/' ),
        'menu-item-status' => 'private'
    ]);
    $menu_locations = get_nav_menu_locations();
    $menu_locations['footer'] = $menu_id;
    set_theme_mod( 'nav_menu_locations', $menu_locations );
}

/**
 * Activate the plugin.
 */
function pantheon_decoupled_auth_example_activate() {
    pantheon_decoupled_auth_example_create_post();
    pantheon_decoupled_auth_example_menu();
    pantheon_decoupled_auth_example_create_application_password();
}
add_action('admin_notices', 'app_password_admin_notice');
register_activation_hook(__FILE__, 'pantheon_decoupled_auth_example_activate');

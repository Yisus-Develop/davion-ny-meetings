<?php
/**
 * Plugin Name:       EWEB - Davion NY Meetings
 * Description:       Meeting booking plugin for Davion's DFNY 2026 event with multilingual scheduling and slot control.
 * Version:           1.2.1
 * Author:            Yisus_Dev
 * Author URI:        https://github.com/Yisus-Develop
 * Plugin URI:        https://github.com/Yisus-Develop/davion-ny-meetings
 * License:           GPL v2 or later
 * Requires at least: 6.0
 * Requires PHP:      8.1+
 * Tested up to:      6.8
 * Text Domain:       eweb-davion-ny-meetings
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'DNM_VERSION', '1.2.1' );
define( 'DNM_FILE', __FILE__ );
define( 'DNM_DIR', plugin_dir_path( __FILE__ ) );
define( 'DNM_URL', plugin_dir_url( __FILE__ ) );
define( 'DNM_TABLE', 'dnm_meetings' );
define( 'DNM_TZ', 'Europe/Lisbon' );

define( 'DNM_GITHUB_USER', 'Yisus-Develop' );
define( 'DNM_GITHUB_REPO', 'davion-ny-meetings' );

require_once DNM_DIR . 'includes/i18n.php';
require_once DNM_DIR . 'includes/db.php';
require_once DNM_DIR . 'includes/slots.php';
require_once DNM_DIR . 'includes/mail.php';
require_once DNM_DIR . 'includes/form.php';
require_once DNM_DIR . 'includes/admin.php';
require_once DNM_DIR . 'includes/class-eweb-github-updater.php';

add_action( 'plugins_loaded', 'dnm_load_textdomain' );
register_activation_hook( DNM_FILE, 'dnm_activate' );
add_shortcode( 'davion_meetings_form', 'dnm_render_shortcode' );
add_action( 'wp_enqueue_scripts', 'dnm_enqueue_assets' );

function dnm_enqueue_assets(): void {
    wp_register_style( 'dnm-form', DNM_URL . 'assets/css/form.css', array(), DNM_VERSION );
    wp_register_script( 'dnm-form', DNM_URL . 'assets/js/form.js', array(), DNM_VERSION, true );
}

if ( class_exists( 'EWEB_GitHub_Updater' ) ) {
    new EWEB_GitHub_Updater( DNM_FILE, DNM_GITHUB_USER, DNM_GITHUB_REPO );
}

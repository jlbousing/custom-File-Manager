<?php
/*
Plugin Name: Custom File Manager
Description: Plugin personalizado para gestionar archivos y directorios.
Version: 1.0
Author: Jorge Luis Bou-saad
*/

define('CFM_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once CFM_PLUGIN_DIR . 'includes/file-manager-functions.php';

register_activation_hook(__FILE__, 'cf_manager_create_custom_folder');

function cf_manager_create_custom_folder() {
    $uploads = wp_upload_dir();
    $custom_dir = $uploads['basedir'] . '/files-custom';

    if (!file_exists($custom_dir)) {
        wp_mkdir_p($custom_dir);
    }
}

function cfm_enqueue_styles() {
    wp_enqueue_style('cfm-styles', plugin_dir_url(__FILE__) . 'css/custom-file-manager.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}

add_action('wp_enqueue_scripts', 'cfm_enqueue_styles');

function cfm_add_rewrite_rules() {
    add_rewrite_rule('^files-custom/(.+)', 'index.php?cfm_file=$matches[1]', 'top');
}
add_action('init', 'cfm_add_rewrite_rules');

function cfm_add_query_vars($vars) {
    $vars[] = 'cfm_file';
    return $vars;
}
add_filter('query_vars', 'cfm_add_query_vars');

function cfm_template_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['cfm_file'])) {
        $file = $wp_query->query_vars['cfm_file'];
        $uploads = wp_upload_dir();
        $file_path = $uploads['basedir'] . '/files-custom/' . $file;

        if (file_exists($file_path)) {
            header('Content-Type: ' . mime_content_type($file_path));
            header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
            readfile($file_path);
            exit();
        } else {
            status_header(404);
            echo 'File not found.';
            exit();
        }
    }
}
add_action('template_redirect', 'cfm_template_redirect');

function cfm_add_custom_page() {
    $page_id = get_option('cfm_page_id');
    if (!$page_id) {
        $page = array(
            'post_title'   => 'Custom File Manager',
            'post_content' => '[cf_manager]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        );
        $page_id = wp_insert_post($page);
        update_option('cfm_page_id', $page_id);
    }
}
register_activation_hook(__FILE__, 'cfm_add_custom_page');

function cfm_remove_custom_page() {
    $page_id = get_option('cfm_page_id');
    if ($page_id) {
        wp_delete_post($page_id, true);
        delete_option('cfm_page_id');
    }
}
register_deactivation_hook(__FILE__, 'cfm_remove_custom_page');

function cf_manager_shortcode($atts) {
    ob_start();
    include CFM_PLUGIN_DIR . 'templates/file-manager-page.php';
    return ob_get_clean();
}
add_shortcode('cf_manager', 'cf_manager_shortcode');

add_action('init', 'cf_manager_handle_file_upload');
add_action('init', 'cf_manager_handle_create_directory');
add_action('init', 'cf_manager_handle_delete_file');
?>

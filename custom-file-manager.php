<?php
/*
Plugin Name: Custom File Manager
Description: Plugin personalizado para gestionar archivos y directorios.
Version: 1.0
Author: Jorge Luis Bou-saad
*/

define('CFM_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once CFM_PLUGIN_DIR . 'includes/file-manager-functions.php';

function cfm_enqueue_styles() {
    wp_enqueue_style('cfm-styles', plugin_dir_url(__FILE__) . 'css/custom-file-manager.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}

add_action('admin_enqueue_scripts', 'cfm_enqueue_styles');

add_action('admin_menu', 'cf_manager_create_menu');
add_action('init', 'cf_manager_handle_file_upload');
add_action('init', 'cf_manager_handle_create_directory');
add_action('init', 'cf_manager_handle_delete_file');
?>

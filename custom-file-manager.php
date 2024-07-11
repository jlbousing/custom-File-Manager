<?php
/*
Plugin Name: Custom File Manager
Description: Plugin personalizado para gestionar archivos y directorios.
Version: 1.0
Author: Jorge Luis Bou-saad
*/

define('CFM_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once CFM_PLUGIN_DIR . 'includes/file-manager-functions.php';

register_activation_hook(__FILE__, 'cf_manager_create_custom_folder_and_page');

function cf_manager_create_custom_folder_and_page() {
    // Crear la carpeta 'files-custom'
    $uploads = wp_upload_dir();
    $custom_dir = $uploads['basedir'] . '/files-custom';

    if (!file_exists($custom_dir)) {
        wp_mkdir_p($custom_dir);
    }

    // Crear la página privada
    $page_title = 'Gestión de Archivos';
    $page_content = '[cf_manager]';
    $page_template = ''; // Deja vacío si no usas una plantilla específica

    // Verifica si la página ya existe
    $page_check = get_page_by_title($page_title);
    if (!isset($page_check->ID)) {
        wp_insert_post(array(
            'post_title'     => $page_title,
            'post_content'   => $page_content,
            'post_status'    => 'private',
            'post_type'      => 'page',
            'post_author'    => 1,
            'page_template'  => $page_template
        ));
    }
}

function cfm_enqueue_styles() {
    if (is_page('gestion-de-archivos')) {
        wp_enqueue_style('cfm-styles', plugin_dir_url(__FILE__) . 'css/custom-file-manager.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    }
}

add_action('wp_enqueue_scripts', 'cfm_enqueue_styles');
add_action('admin_enqueue_scripts', 'cfm_enqueue_styles');

add_shortcode('cf_manager', 'cf_manager_shortcode');

function cf_manager_shortcode() {
    ob_start();
    cf_manager_render_page();
    return ob_get_clean();
}

add_action('template_redirect', 'cf_manager_restrict_page_access');

function cf_manager_restrict_page_access() {
    if (is_page('gestion-de-archivos') && !current_user_can('view_files_custom')) {
        wp_redirect(home_url());
        exit();
    }
}

add_action('init', 'cf_manager_handle_file_upload');
add_action('init', 'cf_manager_handle_create_directory');
add_action('init', 'cf_manager_handle_delete_file');
?>

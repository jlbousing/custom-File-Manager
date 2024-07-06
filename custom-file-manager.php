<?php
/**
 * Plugin Name: Custom File Manager
 * Description: Plugin personalizado para la gestión de archivos.
 * Version: 1.0
 * Author: Tu Nombre
 */

define('CFM_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once CFM_PLUGIN_DIR . 'includes/file-manager-functions.php';

// Asegurarse de que el administrador tenga los permisos adecuados
function cf_manager_add_caps_to_admin() {
    $role = get_role('administrator');
    $role->add_cap('upload_files');
    $role->add_cap('delete_files');
}

add_action('admin_init', 'cf_manager_add_caps_to_admin');

add_action('admin_menu', 'cf_manager_create_menu');
add_action('init', 'cf_manager_check_login');
add_action('init', 'cf_manager_handle_file_upload');
add_action('init', 'cf_manager_handle_create_directory');
add_action('init', 'cf_manager_handle_delete_file');

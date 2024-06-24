<?php
/*
Plugin Name: Custom File Manager
Description: Gestión de archivos personalizada para usuarios autenticados.
Version: 1.0
Author: Jorge Luis Bou-saad
*/

// Definir una constante para la ruta del plugin
define('CFM_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Incluir archivos necesarios
require_once CFM_PLUGIN_DIR . 'includes/file-manager-functions.php';

// Hook para asegurar autenticación
add_action('template_redirect', 'cf_manager_check_login');

// Hook para crear páginas de gestión de archivos
add_action('admin_menu', 'cf_manager_create_menu');

// Hook para manejar la subida de archivos
add_action('init', 'cf_manager_handle_file_upload');

<?php

function cf_manager_check_login() {
    if (!is_user_logged_in()) {
        auth_redirect();
    }
}

function cf_manager_create_menu() {
    add_menu_page('Gestión de Archivos', 'Gestión de Archivos', 'read', 'cf-manager', 'cf_manager_page', 'dashicons-media-spreadsheet', 6);
}

function cf_manager_page() {
    if (current_user_can('upload_files')) {
        // Incluir la plantilla de la página de gestión de archivos
        include CFM_PLUGIN_DIR . 'templates/file-manager-page.php';
    } else {
        echo '<p>No tienes permiso para acceder a esta página.</p>';
    }
}

function cf_manager_handle_file_upload() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para subir archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';

        $file = $_FILES['file'];
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['basedir'] . '/' . $current_directory;
        $upload_file = $upload_path . '/' . basename($file['name']);

        if (!file_exists($upload_path)) {
            wp_mkdir_p($upload_path);
        }

        if (move_uploaded_file($file['tmp_name'], $upload_file)) {
            wp_safe_redirect(add_query_arg('upload_status', 'success', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        } else {
            wp_safe_redirect(add_query_arg('upload_status', 'error', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        }
    }
}

function cf_manager_handle_create_directory() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_directory'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para crear directorios.');
        }

        // Obtener el directorio actual y eliminar barras adicionales
        $current_directory = isset($_POST['current_directory']) ? trim(sanitize_text_field($_POST['current_directory']), '/') : '';

        $uploads = wp_upload_dir();
        $new_dir = sanitize_text_field($_POST['new_directory']);

        // Construir la ruta completa
        $new_dir_path = $uploads['basedir'] . '/' . ($current_directory ? $current_directory . '/' : '') . $new_dir;

        if (!file_exists($new_dir_path)) {
            wp_mkdir_p($new_dir_path);
            wp_safe_redirect(add_query_arg('directory_status', 'success', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        } else {
            wp_safe_redirect(add_query_arg('directory_status', 'exists', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        }
    }
}

function cf_manager_handle_delete_file() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
        if (!current_user_can('delete_files')) {
            wp_die('No tienes permiso para eliminar archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $uploads = wp_upload_dir();
        $file_to_delete = sanitize_text_field($_POST['delete_file']);
        $file_path = $uploads['basedir'] . '/' . $current_directory . '/' . $file_to_delete;

        if (file_exists($file_path)) {
            unlink($file_path);
            wp_safe_redirect(add_query_arg('delete_status', 'success', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        } else {
            wp_safe_redirect(add_query_arg('delete_status', 'not_found', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
            exit();
        }
    }
}

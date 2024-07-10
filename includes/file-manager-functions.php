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
        include CFM_PLUGIN_DIR . 'templates/file-manager-page.php';
    } else {
        echo '<p>No tienes permiso para acceder a esta página.</p>';
    }
}

function cf_manager_handle_file_upload() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para subir archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? trim(sanitize_text_field($_POST['current_directory']), '/') : '';
        $uploads = wp_upload_dir();
        $custom_base_directory = $uploads['basedir'] . '/files-custom';
        $custom_base_url = $uploads['baseurl'] . '/files-custom';
        $upload_dir = $custom_base_directory . '/' . $current_directory;

        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        $uploaded_file = $_FILES['file'];
        $upload_file_path = $upload_dir . '/' . basename($uploaded_file['name']);

        if (move_uploaded_file($uploaded_file['tmp_name'], $upload_file_path)) {
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

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $uploads = wp_upload_dir();
        $custom_base_directory = $uploads['basedir'] . '/files-custom';
        $new_dir = sanitize_text_field($_POST['new_directory']);
        $new_dir_path = $custom_base_directory . '/' . $current_directory . '/' . $new_dir;

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


// Función para eliminar directorios y archivos de forma recursiva
function cf_manager_delete_directory($dir) {
    if (!is_dir($dir)) {
        return false;
    }

    $items = array_diff(scandir($dir), array('.', '..'));
    foreach ($items as $item) {
        $item_path = $dir . '/' . $item;
        if (is_dir($item_path)) {
            cf_manager_delete_directory($item_path);
        } else {
            unlink($item_path);
        }
    }
    return rmdir($dir);
}

function cf_manager_handle_delete_file() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para eliminar archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $file_to_delete = sanitize_text_field($_POST['delete_file']);
        $is_directory = isset($_POST['is_directory']) ? intval($_POST['is_directory']) : 0;

        $uploads = wp_upload_dir();
        $custom_base_directory = $uploads['basedir'] . '/files-custom';
        $file_path = $custom_base_directory . '/' . $current_directory . '/' . $file_to_delete;

        if ($is_directory) {
            if (is_dir($file_path)) {
                if (count(scandir($file_path)) == 2) {
                    rmdir($file_path);
                    wp_safe_redirect(add_query_arg('delete_status', 'success', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
                    exit();
                } else {
                    wp_die('El directorio no está vacío.');
                }
            } else {
                wp_safe_redirect(add_query_arg('delete_status', 'not_found', admin_url('admin.php?page=cf-manager&directory=' . urlencode($current_directory))));
                exit();
            }
        } else {
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
}


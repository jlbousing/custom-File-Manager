<?php
function cf_manager_safe_redirect($query_args = array()) {
    $current_url = get_permalink();
    wp_safe_redirect(add_query_arg($query_args, $current_url));
    exit();
}

function cf_manager_handle_file_upload() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para subir archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $uploads = wp_upload_dir();
        $upload_dir = $uploads['basedir'] . '/files-custom/' . $current_directory;

        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }

        $uploaded_file = $_FILES['file'];
        $target_file = $upload_dir . '/' . basename($uploaded_file['name']);

        if (move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
            cf_manager_safe_redirect(array('upload_status' => 'success'));
        } else {
            cf_manager_safe_redirect(array('upload_status' => 'error'));
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
        $new_dir = sanitize_text_field($_POST['new_directory']);
        $new_dir_path = $uploads['basedir'] . '/files-custom/' . $current_directory . '/' . $new_dir;

        if (!file_exists($new_dir_path)) {
            wp_mkdir_p($new_dir_path);
            cf_manager_safe_redirect(array('directory_status' => 'success'));
        } else {
            cf_manager_safe_redirect(array('directory_status' => 'exists'));
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
        $file_path = $uploads['basedir'] . '/files-custom/' . $current_directory . '/' . $file_to_delete;

        if (file_exists($file_path)) {
            if (is_dir($file_path)) {
                if (count(scandir($file_path)) == 2) {
                    rmdir($file_path);
                    cf_manager_safe_redirect(array('delete_status' => 'success'));
                } else {
                    cf_manager_safe_redirect(array('delete_status' => 'not_empty'));
                }
            } else {
                unlink($file_path);
                cf_manager_safe_redirect(array('delete_status' => 'success'));
            }
        } else {
            cf_manager_safe_redirect(array('delete_status' => 'not_found'));
        }
    }
}
?>

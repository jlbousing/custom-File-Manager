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

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $uploads = wp_upload_dir();
        $upload_dir = $uploads['basedir'] . '/files-custom/' . $current_directory;

        $file = $_FILES['file'];
        $file_path = $upload_dir . '/' . basename($file['name']);

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_safe_redirect(add_query_arg(array('upload_status' => 'success', 'directory' => urlencode($current_directory)), get_permalink()));
            exit();
        } else {
            wp_safe_redirect(add_query_arg(array('upload_status' => 'error', 'directory' => urlencode($current_directory)), get_permalink()));
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
        $new_dir = sanitize_text_field($_POST['new_directory']);
        $new_dir_path = $uploads['basedir'] . '/files-custom/' . $current_directory . '/' . $new_dir;

        if (!file_exists($new_dir_path)) {
            wp_mkdir_p($new_dir_path);
            wp_safe_redirect(add_query_arg(array('directory_status' => 'success'), get_permalink()));
            exit();
        } else {
            wp_safe_redirect(add_query_arg(array('directory_status' => 'exists'), get_permalink()));
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
        if (!current_user_can('delete_files')) {
            wp_die('No tienes permiso para eliminar archivos.');
        }

        $current_directory = isset($_POST['current_directory']) ? sanitize_text_field($_POST['current_directory']) : '';
        $uploads = wp_upload_dir();
        $file_to_delete = sanitize_text_field($_POST['delete_file']);
        $file_path = $uploads['basedir'] . '/files-custom/' . $current_directory . '/' . $file_to_delete;

        if (isset($_POST['is_directory']) && $_POST['is_directory'] == '1') {
            if (is_dir($file_path)) {
                $files = array_diff(scandir($file_path), array('.','..'));
                foreach ($files as $file) {
                    (is_dir("$file_path/$file")) ? delTree("$file_path/$file") : unlink("$file_path/$file");
                }
                rmdir($file_path);
                wp_safe_redirect(add_query_arg(array('delete_status' => 'success', 'directory' => urlencode($current_directory)), get_permalink()));
                exit();
            } else {
                wp_safe_redirect(add_query_arg(array('delete_status' => 'not_found', 'directory' => urlencode($current_directory)), get_permalink()));
                exit();
            }
        } else {
            if (file_exists($file_path)) {
                unlink($file_path);
                wp_safe_redirect(add_query_arg(array('delete_status' => 'success', 'directory' => urlencode($current_directory)), get_permalink()));
                exit();
            } else {
                wp_safe_redirect(add_query_arg(array('delete_status' => 'not_found', 'directory' => urlencode($current_directory)), get_permalink()));
                exit();
            }
        }
    }
}


function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function cf_manager_render_page() {
    echo '<div class="cfm-container">';
    echo '<h1>Gestión de Archivos</h1>';

    $current_directory = isset($_GET['directory']) ? trim(sanitize_text_field($_GET['directory']), '/') : '';

    if (strpos($current_directory, '..') !== false) {
        wp_die('No tienes permiso para acceder a esta ubicación.');
    }

    if (isset($_GET['upload_status'])) {
        if ($_GET['upload_status'] == 'success') {
            echo '<p class="cfm-message success">Archivo subido exitosamente.</p>';
        } elseif ($_GET['upload_status'] == 'error') {
            echo '<p class="cfm-message error">Error subiendo el archivo.</p>';
        }
    }

    if (isset($_GET['directory_status'])) {
        if ($_GET['directory_status'] == 'success') {
            echo '<p class="cfm-message success">Directorio creado exitosamente.</p>';
        } elseif ($_GET['directory_status'] == 'exists') {
            echo '<p class="cfm-message error">El directorio ya existe.</p>';
        }
    }

    if (isset($_GET['delete_status'])) {
        if ($_GET['delete_status'] == 'success') {
            echo '<p class="cfm-message success">Archivo/Directorio eliminado exitosamente.</p>';
        } elseif ($_GET['delete_status'] == 'not_found') {
            echo '<p class="cfm-message error">El archivo o directorio no existe.</p>';
        }
    }

    echo '<div class="cfm-actions">';
    echo '<form method="POST" enctype="multipart/form-data" class="cfm-form">';
    echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
    echo '<input type="file" name="file" />';
    echo '<input type="submit" value="Subir Archivo" class="cfm-button" />';
    echo '</form>';

    echo '<form method="POST" class="cfm-form">';
    echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
    echo '<input type="text" name="new_directory" placeholder="Nombre del nuevo directorio" />';
    echo '<input type="submit" value="Crear Directorio" class="cfm-button" />';
    echo '</form>';
    echo '</div>';

    $uploads = wp_upload_dir();
    $custom_base_directory = $uploads['basedir'] . '/files-custom';
    $custom_base_url = $uploads['baseurl'] . '/files-custom';
    $base_directory = $custom_base_directory . '/' . $current_directory;

    if (is_dir($base_directory)) {
        $files = scandir($base_directory);

        if ($files !== false) {
            echo '<h2>Archivos y Directorios en: ' . esc_html($current_directory) . '</h2>';
            echo '<ul class="cfm-file-list">';

            if ($current_directory) {
                $parent_directory = dirname($current_directory);
                echo '<li class="cfm-file-item">';
                echo '<a href="' . esc_url(add_query_arg('directory', urlencode($parent_directory), get_permalink())) . '">';
                echo '<i class="fas fa-folder"></i> .. (subir)</a>';
                echo '</li>';
            }

            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $file_path = $base_directory . '/' . $file;
                    if (is_dir($file_path)) {
                        echo '<li class="cfm-file-item">';
                        echo '<a href="' . esc_url(add_query_arg('directory', urlencode($current_directory . '/' . $file), get_permalink())) . '">';
                        echo '<i class="fas fa-folder"></i> ' . esc_html($file) . '</a>';
                        echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este directorio?\');">';
                        echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
                        echo '<input type="hidden" name="delete_file" value="' . esc_attr($file) . '" />';
                        echo '<input type="hidden" name="is_directory" value="1" />';
                        if(current_user_can("delete_files")) echo '<button type="submit" class="cfm-delete-button"><i class="fas fa-times"></i></button>';
                        echo '</form>';
                        echo '</li>';
                    } else {
                        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);
                        $allowed_image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
                        $icon_class = 'fas fa-file'; // icono por defecto para archivos

                        if (in_array(strtolower($file_extension), $allowed_image_extensions)) {
                            $icon_class = 'fas fa-file-image'; // si es una imagen
                        }

                        echo '<li class="cfm-file-item">';
                        echo '<a href="' . esc_url($custom_base_url . '/' . $file) . '" download>';
                        echo '<i class="' . $icon_class . '"></i> ' . esc_html($file) . '</a>';
                        echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este archivo?\');">';
                        echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
                        echo '<input type="hidden" name="delete_file" value="' . esc_attr($file) . '" />';
                        if(current_user_can("delete_files")) echo '<button type="submit" class="cfm-delete-button"><i class="fas fa-times"></i></button>';
                        echo '</form>';
                        echo '</li>';
                    }
                }
            }
            echo '</ul>';
        } else {
            echo '<p>No se pueden listar los archivos en este directorio.</p>';
        }
    } else {
        echo '<p>El directorio no existe.</p>';
    }

    echo '</div>';
}



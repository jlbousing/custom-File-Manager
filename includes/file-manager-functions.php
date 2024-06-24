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
    // Verificar si la solicitud es POST y si se ha enviado un archivo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        if (!current_user_can('upload_files')) {
            wp_die('No tienes permiso para subir archivos.');
        }

        $file = $_FILES['file'];
        $upload = wp_upload_bits($file['name'], null, file_get_contents($file['tmp_name']));

        if (!$upload['error']) {
            echo 'Archivo subido exitosamente: ' . $upload['url'];
        } else {
            echo 'Error subiendo el archivo: ' . $upload['error'];
        }
    }
}

<?php
echo '<div class="cfm-container">';
echo '<h1>Gestión de Archivos</h1>';

$current_directory = isset($_GET['directory']) ? trim(sanitize_text_field($_GET['directory']), '/') : '';

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
$base_directory = $uploads['basedir'] . '/' . $current_directory;
$base_url = $uploads['baseurl'] . '/' . $current_directory;

if (is_dir($base_directory)) {
    $files = scandir($base_directory);

    if ($files !== false) {
        echo '<h2>Archivos y Directorios en: ' . esc_html($current_directory) . '</h2>';
        echo '<ul class="cfm-file-list">';

        if ($current_directory) {
            $parent_directory = dirname($current_directory);
            echo '<li class="cfm-file-item">';
            echo '<a href="' . esc_url(add_query_arg('directory', urlencode($parent_directory), admin_url('admin.php?page=cf-manager'))) . '">';
            echo '<i class="fas fa-folder"></i> .. (subir)</a>';
            echo '</li>';
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_path = $base_directory . '/' . $file;
                if (is_dir($file_path)) {
                    echo '<li class="cfm-file-item">';
                    echo '<a href="' . esc_url(add_query_arg('directory', urlencode($current_directory . '/' . $file), admin_url('admin.php?page=cf-manager'))) . '">';
                    echo '<i class="fas fa-folder"></i> ' . esc_html($file) . '</a>';
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este directorio?\');">';
                    echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
                    echo '<input type="hidden" name="delete_file" value="' . esc_attr($file) . '" />';
                    echo '<input type="hidden" name="is_directory" value="1" />';
                    echo '<button type="submit" class="cfm-delete-button"><i class="fas fa-times"></i></button>';
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
                    echo '<a href="' . esc_url($base_url . '/' . $file) . '" download>';
                    echo '<i class="' . $icon_class . '"></i> ' . esc_html($file) . '</a>';
                    echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'¿Estás seguro de que deseas eliminar este archivo?\');">';
                    echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
                    echo '<input type="hidden" name="delete_file" value="' . esc_attr($file) . '" />';
                    echo '<button type="submit" class="cfm-delete-button"><i class="fas fa-times"></i></button>';
                    echo '</form>';
                    echo '</li>';
                }
            }
        }

        echo '</ul>';
    } else {
        echo '<p>No se pudieron leer los contenidos del directorio.</p>';
    }
} else {
    echo '<p>El directorio no existe.</p>';
}

echo '</div>';

<?php
echo '<h1>Gestión de Archivos</h1>';

$current_directory = isset($_GET['directory']) ? sanitize_text_field($_GET['directory']) : '';

if (isset($_GET['upload_status'])) {
    if ($_GET['upload_status'] == 'success') {
        echo '<p>Archivo subido exitosamente.</p>';
    } elseif ($_GET['upload_status'] == 'error') {
        echo '<p>Error subiendo el archivo.</p>';
    }
}

if (isset($_GET['directory_status'])) {
    if ($_GET['directory_status'] == 'success') {
        echo '<p>Directorio creado exitosamente.</p>';
    } elseif ($_GET['directory_status'] == 'exists') {
        echo '<p>El directorio ya existe.</p>';
    }
}

if (isset($_GET['delete_status'])) {
    if ($_GET['delete_status'] == 'success') {
        echo '<p>Archivo eliminado exitosamente.</p>';
    } elseif ($_GET['delete_status'] == 'not_found') {
        echo '<p>El archivo no existe.</p>';
    }
}

echo '<form method="POST" enctype="multipart/form-data">';
echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
echo '<input type="file" name="file" />';
echo '<input type="submit" value="Subir Archivo" />';
echo '</form>';

echo '<form method="POST">';
echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
echo '<input type="text" name="new_directory" placeholder="Nombre del nuevo directorio" />';
echo '<input type="submit" value="Crear Directorio" />';
echo '</form>';

echo '<form method="POST">';
echo '<input type="hidden" name="current_directory" value="' . esc_attr($current_directory) . '" />';
echo '<input type="text" name="delete_file" placeholder="Nombre del archivo a eliminar" />';
echo '<input type="submit" value="Eliminar Archivo" />';
echo '</form>';

$uploads = wp_upload_dir();
$base_directory = $uploads['basedir'] . '/' . $current_directory;
$base_url = $uploads['baseurl'] . '/' . $current_directory;

if (is_dir($base_directory)) {
    $files = scandir($base_directory);

    if ($files !== false) {
        echo '<h2>Archivos y Directorios en: ' . esc_html($current_directory) . '</h2>';
        echo '<ul>';

        if ($current_directory) {
            $parent_directory = dirname($current_directory);
            echo '<li><a href="' . esc_url(add_query_arg('directory', urlencode($parent_directory), admin_url('admin.php?page=cf-manager'))) . '">.. (subir)</a></li>';
        }

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $file_path = $base_directory . '/' . $file;
                if (is_dir($file_path)) {
                    echo '<li><a href="' . esc_url(add_query_arg('directory', urlencode($current_directory . '/' . $file), admin_url('admin.php?page=cf-manager'))) . '">' . esc_html($file) . ' (Directorio)</a></li>';
                } else {
                    echo '<li><a href="' . esc_url($base_url . '/' . $file) . '">' . esc_html($file) . '</a></li>';
                }
            }
        }
        echo '</ul>';
    } else {
        echo '<p>No se pudo leer el directorio.</p>';
    }
} else {
    echo '<p>El directorio no existe.</p>';
}

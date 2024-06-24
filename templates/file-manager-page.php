<?php
echo '<h1>Gestión de Archivos</h1>';
echo '<form method="POST" enctype="multipart/form-data">';
echo '<input type="file" name="file" />';
echo '<input type="submit" value="Subir Archivo" />';
echo '</form>';

// Obtener el directorio base de uploads
$uploads = wp_upload_dir();
$upload_base = $uploads['basedir'];
$upload_url_base = $uploads['baseurl'];

// Obtener el subdirectorio actual
$current_dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$current_path = $upload_base . '/' . $current_dir;
$current_url = $upload_url_base . '/' . $current_dir;

// Verificar si el directorio actual existe antes de escanear
if (is_dir($current_path)) {
    $files = scandir($current_path);

    if ($files !== false) {
        echo '<h2>Archivos Subidos</h2>';
        echo '<ul>';
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($current_path . '/' . $file)) {
                    echo '<li><a href="?page=cf-manager&dir=' . $current_dir . '/' . $file . '">' . $file . '</a> (Directorio)</li>';
                } else {
                    echo '<li><a href="' . $current_url . '/' . $file . '">' . $file . '</a></li>';
                }
            }
        }
        echo '</ul>';
    } else {
        echo '<p>No se pudo leer el directorio de uploads.</p>';
    }
} else {
    echo '<p>El directorio de uploads no existe.</p>';
}

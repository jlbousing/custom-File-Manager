# Custom File Manager Plugin

## Descripción

Este plugin personalizado permite gestionar archivos y directorios dentro de WordPress de manera similar a Dropbox o Google Drive. Proporciona funcionalidades como subir archivos, crear directorios, eliminar archivos y directorios, entre otras.

## Instalación

1. **Descarga del Plugin:**
    - Descarga el plugin ZIP desde GitHub o desde la sección de plugins de WordPress.

2. **Instalación desde el Panel de WordPress:**
    - Ve a `wp-admin -> Plugins -> Añadir nuevo -> Subir plugin`.
    - Sube el archivo ZIP que descargaste y haz clic en "Instalar ahora".
    - Activa el plugin una vez que se haya instalado.

3. **Configuración Inicial:**
    - El plugin creará automáticamente la carpeta `files-custom` dentro del directorio de uploads (`wp-content/uploads`) si no existe.

## Configuración de Permisos con User Role Editor

Para gestionar los permisos de usuarios, este plugin utiliza el plugin "User Role Editor". A continuación se detallan los pasos para configurar los permisos adecuadamente:

1. **Instalación de User Role Editor:**
    - Ve a `wp-admin -> Plugins -> Añadir nuevo`.
    - Busca "User Role Editor" y haz clic en "Instalar ahora".
    - Activa el plugin una vez que se haya instalado.

2. **Configuración de Permisos:**
    - Ve a `wp-admin -> Users -> User Role Editor`.
    - Selecciona el rol de usuario al que deseas asignar permisos (por ejemplo, "Cliente").
    - Asegúrate de que el rol tenga las siguientes capacidades:
        - **view_files_custom:** Permite al usuario acceder a la página personalizada de gestión de archivos.
        - **upload_files:** Permite al usuario subir archivos.
        - **delete_files:** Permite al usuario eliminar archivos y directorios.

    - Guarda los cambios realizados en los permisos del rol de usuario.

## Uso del Plugin

Una vez instalado y configurado, podrás acceder a la gestión de archivos desde:

- **Para Administradores:** `wp-admin -> Archivos Personalizados`.
- **Para Usuarios con Rol de Cliente:** Asegúrate de que el usuario tenga el rol con los permisos adecuados configurados en User Role Editor. Luego, podrán acceder a través de la página `gestion-de-archivos`, que debe ser creada y contener el shortcode `[cf_manager]`.

## Soporte

Para soporte adicional o para reportar problemas, por favor crea un issue en [GitHub](https://github.com/tu-usuario/tu-repositorio) o contacta con nosotros en [nuestra página de soporte](https://tusitio.com/soporte).

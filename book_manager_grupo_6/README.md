# Book Manager Pro - Grupo6-3pm

Versión funcional básica y auto-instalable del proyecto solicitado.

## Archivos principales
- `install.php` - Script de instalación automática (crear BD, tablas, usuario admin y datos de ejemplo).
- `index.php` - Listado de libros con paginación (requiere login).
- `add.php` - Agregar libros.
- `edit.php` - Editar libros.
- `delete.php` - Eliminar libros (responde a POST).
- `login.php` / `logout.php` - Autenticación simple.
- `config/database.php` - Conexión PDO a SQLite.
- `config/setup.php` - Funciones de instalación.
- `models/Book.php`, `models/User.php` - Modelos simple.

## Instalación rápida
1. Subir la carpeta al servidor (Apache).  
2. Asegúrate de que PHP y extensión PDO_SQLITE estén instalados.  
3. Dar permisos para que el servidor web pueda escribir en `data/` (install.php crea la carpeta si es posible).  
4. Abrir en el navegador `http://tu-dominio/ruta/install.php`.  
5. Login por defecto: `admin / clave123`.

> **Seguridad**: Después de la instalación elimina o protege `install.php` (por ejemplo renombrándolo) y ajusta permisos de `data/book_manager.db` según políticas del servidor.

## Recomendaciones (bonus)
- Añadir token CSRF a formularios.  
- Validaciones cliente más robustas (JS).  
- Mejorar UI y añadir búsqueda/filtrado.  
- Soporte multiusuario y roles.

## Nota del grupo
Proyecto preparado para la entrega del grupo6-3pm. Fecha de entrega según enunciado: 7 de noviembre 2025.

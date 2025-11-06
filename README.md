# Book Manager Pro

**Book Manager Pro** es una aplicación web PHP que permite gestionar libros en una biblioteca de forma sencilla y portable.  
Incluye instalación automática, base de datos SQLite y compatibilidad total entre **Windows (XAMPP)**, **Linux (Docker)** y **macOS (Docker Desktop)**.

---

## Características principales

- **Auto-instalación**: crea la base de datos y usuario administrador la primera vez.
- **CRUD completo**: agregar, editar, eliminar y listar libros. (Durante la instalación se insertan algunos libros de ejemplo para validar el correcto funcionamiento del sistema)
- **Persistencia local** con SQLite (sin servidor de base de datos externo).
- **Seguridad básica**:
  - `password_hash()` y `password_verify()` para contraseñas.
  - `htmlspecialchars()` en todas las salidas HTML.
  - PDO con *prepared statements* para prevenir inyección SQL.
- **Totalmente portable**: funciona en cualquier entorno con PHP ≥ 8.1 y SQLite activo.
- **Sin dependencias externas** ni frameworks.

---

## Estructura del proyecto

```
book_manager_grupo_6/
├─ index.php
├─ install.php
├─ add.php
├─ edit.php
├─ delete.php
├─ config/
│  ├─ database.php
│  └─ setup.php
├─ models/
│  ├─ Book.php
│  └─ User.php
├─ data/  carpeta vacía, escritura habilitada
└─ assets/
   └─ css/style.css
```

---

##  Requisitos

| Requisito    | Versión mínima            | Notas                                      |
|------------  |----------------           |-------                                     |
| PHP          | 8.1                       | Con módulos `pdo`, `pdo_sqlite`, `sqlite3` |
| Servidor web | Apache o integrado en PHP | Se puede usar XAMPP o Docker               |
| Permisos     | Escritura en `data/`      | Necesario para `book_manager.db`           |

---

##  Instalación y ejecución

###  **Windows (XAMPP)**

1. Copiar `book_manager_grupo_6/` dentro de `htdocs/`.
2. Iniciar Apache desde XAMPP.
3. Navegar a  
   ```
   http://localhost/book_manager_grupo_6/install.php
   ```
4. Se generará la base de datos en `data/book_manager.db`.

---

###  **Linux (Docker)**

####  Docker Desktop / Linux nativo
```bash
cd Book-Manager-Pro/book_manager_grupo_6
docker build -t book-manager-pro .
docker run -p 8080:80 -v "$(pwd):/var/www/html" book-manager-pro
```

Abrir en el navegador:  
http://localhost:8080/install.php

---

###  **macOS **

Ejecutar con **Docker Desktop para Mac** (usa la misma imagen Linux multiplataforma):

```bash
git clone https://github.com/Juanp1712/Book-Manager-Pro.git
cd Book-Manager-Pro/book_manager_grupo_6
docker compose up --build
```

Abrir: http://localhost:8080/install.php

---

Si no existe la base de datos, install.php crea las tablas, el usuario admin, inserta datos de prueba y redirige automáticamente a login.php

##  Credenciales iniciales

| Usuario | Contraseña |
|----------|-------------|
| `admin`  | `clave123`  |

Se almacenan en la base con `password_hash()` al instalar.

---

##  Compatibilidad cross-server

| Entorno                        | Resultado | Observaciones                                  |
|--------------------------------|-----------|------------------------------------------------|
|  Windows 10/11 + XAMPP         | bueno     | CRUD funcional y persistente                   |
|  Linux (Docker php:8.2-apache) | bueno     | Verificado: Debian Linux dentro del contenedor |
|  macOS (Docker Desktop)        | bueno     | Misma imagen Linux/ARM64                       |

---

##  Autores

Proyecto académico desarrollado por **Grupo 6 –  Administracion de Base de datos**.  
Repositorio oficial: https://github.com/Juanp1712/Book-Manager-Pro
Entrega: 7/11/2025. 
Evaluado según funcionalidad CRUD, auto-instalación, trabajo en equipo y documentación

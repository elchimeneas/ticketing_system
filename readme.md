# 🎫 Ticketing System

Sistema de gestión de tickets de soporte desarrollado en PHP, con interfaz moderna gracias a Tailwind CSS y backend conectado a PostgreSQL. Permite a usuarios crear tickets, comunicarse con el equipo de soporte y gestionar solicitudes de manera eficiente.

## 🚀 Características

- **Gestión de usuarios**: Registro, inicio de sesión y roles (`user`, `support`, `administrator`).
- **Creación y seguimiento de tickets**: Los usuarios pueden crear tickets, asignar categorías y etiquetas, y seguir su estado.
- **Sistema de mensajería en tickets**: Comunicación entre usuarios y personal de soporte dentro de cada ticket.
- **Carga de archivos adjuntos**: Los usuarios pueden adjuntar archivos relevantes a sus tickets. *EN DESAROLLO*
- **Panel de administración**: Visualización y gestión de usuarios, tickets, categorías y etiquetas.
- **Configuración del sistema**: Personalización de ajustes generales como nombre del sitio y correo del administrador.

## 🛠️ Tecnologías utilizadas

- **Backend**: PHP
- **Frontend**: HTML, Tailwind CSS
- **Base de datos**: PostgreSQL
- **ORM**: PDO (PHP Data Objects)

## 📦 Estructura del proyecto

```
ticketing_system/
├── assets/             # Archivos estáticos (CSS, JS, imágenes)
├── config/             # Archivos de configuración
├── includes/           # Archivos comunes (headers, footers, etc.)
├── models/             # Modelos de datos y lógica de negocio
├── index.php           # Página principal
├── login.php           # Página de inicio de sesión
├── register.php        # Página de registro de usuarios
├── dashboard.php       # Panel principal de usuarios
├── tickets.php         # Listado de tickets
├── ticket_detail.php   # Detalle de ticket individual
├── new_ticket.php      # Formulario para crear nuevos tickets
├── settings.php        # Configuración del sistema
├── users.php           # Gestión de usuarios
├── user_panel.php      # Panel de usuario, información personal.
├── logout.php          # Cierre de sesión
└── README.md           # Documentación del proyecto
```

## ⚙️ Instalación

1. **Clonar el repositorio**:

   ```bash
   git clone https://github.com/elchimeneas/ticketing_system.git
   cd ticketing_system
   ```

2. **Configurar la base de datos**:

   - Crear una base de datos en PostgreSQL.
   - Ejecutar el script de creación de tablas y datos iniciales proporcionado en el archivo `database.sql`.

3. **Configurar la conexión a la base de datos**:

   - Editar el archivo `config/database.php` con las credenciales de tu base de datos.

4. **Configurar el entorno**:

   - Si utilizas variables de entorno, asegúrate de definirlas correctamente en un archivo `.env`.

5. **Iniciar la aplicación**:

   - Configura tu servidor web (Apache, Nginx, etc.) para servir la aplicación desde el directorio del proyecto.

## 🧪 Datos de prueba

Se incluyen usuarios de ejemplo en el script de base de datos:

- **Usuario estándar**:

  - Correo: `user@example.com`
  - Contraseña: `password`

- **Soporte**:

  - Correo: `support@example.com`
  - Contraseña: `password`

- **Administrador**:
  - Correo: `admin@example.com`
  - Contraseña: `password`

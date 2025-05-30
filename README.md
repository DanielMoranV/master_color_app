# Sistema de Gestión de Colores con Laravel y Vue.js

## Descripción
Sistema de gestión de colores y productos con funcionalidades para administración de productos, pedidos, clientes y stock. Este proyecto utiliza Laravel como backend y Vue.js con Inertia.js como frontend.

## Características Principales
*   **Autenticación de Usuarios:** Sistema de login y registro para administradores y personal.
*   **Autenticación de Clientes:** Sistema de autenticación separado para clientes.
*   **Gestión de Productos:** Crear, leer, actualizar y eliminar productos.
*   **Gestión de Stock:** Seguimiento de niveles de stock y movimientos de inventario.
*   **Gestión de Pedidos:** Procesamiento de pedidos de clientes y gestión de su ciclo de vida.
*   **Gestión de Clientes:** Administración de información de clientes y su historial de pedidos.
*   **Procesamiento de Pagos:** Integración para manejo de pagos (utilizando el modelo `Payment` y sus controladores relacionados).

## Tecnologías Utilizadas
*   **PHP:** ^8.2
*   **Laravel:** ^12.0
*   **Vue.js:** ^3.5 (a través de **Inertia.js** ^2.0.0)
*   **Ziggy:** ^2.4 (para usar rutas de Laravel en JavaScript)
*   **Tailwind CSS:** ^4.1
*   **Base de Datos:** SQLite (por defecto), fácilmente configurable para MySQL, PostgreSQL, etc. a través de `config/database.php`.

## Configuración Inicial

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/your-username/your-repository-name.git
    cd your-repository-name
    ```
2.  **Copiar `.env.example` a `.env` y configurar variables de entorno:**
    ```bash
    cp .env.example .env
    ```
    *   Asegúrate de que `APP_KEY` esté configurado (puede generarse en un paso posterior).
    *   Configura los ajustes de tu base de datos en `.env` (por ejemplo, para MySQL o PostgreSQL si no usas SQLite por defecto).

3.  **Instalar dependencias de PHP:**
    ```bash
    composer install
    ```
4.  **Instalar dependencias de Node.js:**
    ```bash
    npm install
    ```
5.  **Generar clave de aplicación:**
    ```bash
    php artisan key:generate
    ```
6.  **Ejecutar migraciones de la base de datos:**
    ```bash
    php artisan migrate
    ```
    *(Esto también creará el archivo `database/database.sqlite` si no existe y estás usando SQLite).*

7.  **Sembrar la base de datos (opcional, si hay seeders disponibles):**
    ```bash
    php artisan db:seed
    ```
8.  **Compilar assets del frontend:**
    *   Para desarrollo (con recarga automática):
        ```bash
        npm run dev
        ```
    *   Para producción:
        ```bash
        npm run build
        ```
9.  **Iniciar la aplicación:**
    *   Si ejecutaste `npm run dev` usando el script `composer dev`, el servidor PHP ya está en ejecución.
    *   De lo contrario, puedes iniciar el servidor de desarrollo de Laravel:
        ```bash
        php artisan serve
        ```
    Accede a la aplicación en `http://localhost:8000` (o la dirección mostrada por `php artisan serve`).

## Estructura del Proyecto
*   `app/`: Código principal de la aplicación Laravel (Modelos, Controladores, Proveedores, Servicios, etc.).
*   `bootstrap/`: Scripts para inicializar el framework.
*   `config/`: Archivos de configuración de la aplicación.
*   `database/`: Migraciones, fábricas y seeders de la base de datos.
*   `public/`: Assets accesibles públicamente (JS/CSS compilados, imágenes, etc.).
*   `resources/`:
    *   `css/`: Archivos CSS (por ejemplo, `app.css` para Tailwind).
    *   `js/`: Componentes Vue.js, páginas, layouts y assets JavaScript (manejados por Vite).
    *   `views/`: Plantillas Blade (principalmente `app.blade.php` que sirve como punto de entrada para Inertia.js).
*   `routes/`: Definiciones de rutas web y API (`web.php`, `api.php`, `auth.php`, etc.).
*   `storage/`: Cache de la aplicación, logs y archivos subidos por usuarios.
*   `tests/`: Pruebas de características y unitarias.
*   `vite.config.ts`: Configuración de Vite para el empaquetado de assets del frontend.

## Scripts Disponibles (desde `composer.json`)

*   `composer dev`:
    *   Ejecuta `php artisan serve`, `php artisan queue:listen --tries=1`, y `npm run dev` (Vite) simultáneamente.
    *   Esta es la forma recomendada de iniciar el entorno de desarrollo.
*   `composer dev:ssr`:
    *   Construye assets SSR (`npm run build:ssr`).
    *   Ejecuta `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail` (logs), y `php artisan inertia:start-ssr` simultáneamente.
    *   Usa esto si estás trabajando con Renderizado del Lado del Servidor.
*   `composer test`:
    *   Limpia la caché de configuración de la aplicación (`php artisan config:clear`).
    *   Ejecuta pruebas Pest (`php artisan test`).

También puedes usar scripts de `npm` directamente (desde `package.json`):
*   `npm run dev`: Inicia el servidor de desarrollo de Vite con recarga automática.
*   `npm run build`: Compila assets del frontend para producción.
*   `npm run build:ssr`: Compila assets del frontend para producción incluyendo build SSR.
*   `npm run format`: Formatea código en el directorio `resources/` usando Prettier.
*   `npm run lint`: Analiza archivos JavaScript/TypeScript usando ESLint.

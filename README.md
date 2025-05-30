# Laravel Vue Starter Kit - E-commerce/Inventory

## Description
An e-commerce platform or inventory management system with features for product, order, client, and stock management. This starter kit provides a foundation for building robust web applications using Laravel and Vue.js with Inertia.js.

## Key Features
*   **User Authentication:** Secure login and registration for administrators and staff.
*   **Client Authentication:** Separate authentication system for customers.
*   **Product Management:** Create, read, update, and delete products.
*   **Inventory/Stock Management:** Track product stock levels and manage inventory movements.
*   **Order Management:** Process customer orders and manage their lifecycle.
*   **Client Management:** Manage customer information and order history.
*   **Payment Processing:** Integration for handling payments (leveraging the `Payment` model and related controllers).

## Technologies Used
*   **PHP:** ^8.2
*   **Laravel:** ^12.0
*   **Vue.js:** ^3.5 (via **Inertia.js** ^2.0.0)
*   **Ziggy:** ^2.4 (for using Laravel routes in JavaScript)
*   **Tailwind CSS:** ^4.1
*   **Database:** SQLite (default), easily configurable for MySQL, PostgreSQL, etc. via `config/database.php`.

## Getting Started / Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/your-repository-name.git
    cd your-repository-name
    ```
2.  **Copy `.env.example` to `.env` and configure environment variables:**
    ```bash
    cp .env.example .env
    ```
    *   Ensure `APP_KEY` is set (can be generated in a later step).
    *   Configure your database settings in `.env` (e.g., for MySQL or PostgreSQL if not using the default SQLite).

3.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
4.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```
5.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```
6.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```
    *(This will also create the `database/database.sqlite` file if it doesn't exist and you are using SQLite).*

7.  **Seed the database (optional, if seeders are available):**
    ```bash
    php artisan db:seed
    ```
8.  **Compile frontend assets:**
    *   For development (with hot reloading):
        ```bash
        npm run dev
        ```
    *   For production:
        ```bash
        npm run build
        ```
9.  **Serve the application:**
    *   If you ran `npm run dev` using the `composer dev` script, the PHP server is already running.
    *   Otherwise, you can start the Laravel development server:
        ```bash
        php artisan serve
        ```
    Access the application at `http://localhost:8000` (or the address shown by `php artisan serve`).

## Project Structure (Brief Overview)
*   `app/`: Core Laravel application code (Models, Controllers, Providers, Services, etc.).
*   `bootstrap/`: Scripts for bootstrapping the framework.
*   `config/`: Application configuration files.
*   `database/`: Database migrations, factories, and seeders.
*   `public/`: Publicly accessible assets (compiled JS/CSS, images, etc.).
*   `resources/`:
    *   `css/`: CSS files (e.g., `app.css` for Tailwind).
    *   `js/`: Vue.js components, pages, layouts, and JavaScript assets (managed by Vite).
    *   `views/`: Blade templates (primarily `app.blade.php` which serves as the entry point for Inertia.js).
*   `routes/`: Web and API route definitions (`web.php`, `api.php`, `auth.php`, etc.).
*   `storage/`: Application cache, logs, and user-uploaded files.
*   `tests/`: Feature and unit tests.
*   `vite.config.ts`: Vite configuration for frontend asset bundling.

## Available Scripts (from `composer.json`)

*   `composer dev`:
    *   Runs `php artisan serve`, `php artisan queue:listen --tries=1`, and `npm run dev` (Vite) concurrently.
    *   This is the recommended way to start the development environment.
*   `composer dev:ssr`:
    *   Builds SSR assets (`npm run build:ssr`).
    *   Runs `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail` (logs), and `php artisan inertia:start-ssr` concurrently.
    *   Use this if you are working with Server-Side Rendering.
*   `composer test`:
    *   Clears application configuration cache (`php artisan config:clear`).
    *   Runs Pest tests (`php artisan test`).

You can also use `npm` scripts directly (from `package.json`):
*   `npm run dev`: Starts the Vite development server with hot module replacement.
*   `npm run build`: Compiles frontend assets for production.
*   `npm run build:ssr`: Compiles frontend assets for production including SSR build.
*   `npm run format`: Formats code in the `resources/` directory using Prettier.
*   `npm run lint`: Lints JavaScript/TypeScript files using ESLint.

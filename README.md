
# To-Do List App

<p align="center"><img src="public/favicon.svg" style="width: 200px;" alt="Archivix Docuvault Logo"></p>

## 🦯 Local Development Setup Guide

### ✅ 1. Clone the repository

```bash
git clone https://github.com/nhilya/nhilya-test-nuha.git
cd nhilya-test-nuha
```

### ✅ 2. Install PHP dependencies

Ensure you have PHP (8.1 or newer) and Composer installed.

```bash
composer install
```

### ✅ 3. Install Node dependencies

Make sure you have Node.js and npm installed.

```bash
npm install
```

### ✅ 4. Set up environment file

```bash
cp .env.example .env
```

Then generate the app key:

```bash
php artisan key:generate
```

### ✅ 5. Configure your database

First, create the PostgreSQL database:

```bash
createdb to-do-list-app
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=to-do-list-app
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

Generate the session table migration, then run all migrations:

```bash
php artisan session:table
php artisan migrate
```

### ✅ 6. Create storage link

```bash
php artisan storage:link
```

### ✅ 7. Run Vite in development

```bash
npm run dev
```

### ✅ 8. Serve the app with Laravel Valet or PHP's built-in server

**If using Valet:**

```bash
valet link
valet secure
```

-   `valet link` registers your project with Valet so it can be accessed via `https://test-nuha.test/`.
-   `valet secure` enables HTTPS by generating a self-signed TLS certificate for your site, making it accessible at `https://test-nuha.test/`.

> ⚠️ Note: Browsers may show a warning for self-signed certificates. You can safely bypass it in local development.

Visit: [https://test-nuha.test/](https://test-nuha.test/)

**Or use PHP server:**

```bash
php artisan serve
```

Visit: [http://localhost:8000](http://localhost:8000)

### ✅ Notes

-   Session driver: `database` (Configured in `.env` and migrations)
-   Logging: daily logs in `storage/logs`

### ✅ Troubleshooting

-   Run `composer install` if `vendor` folder is missing.
-   Run `npm run build` for production assets.
-   Ensure database is running locally.
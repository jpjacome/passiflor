# Laravel Shared Hosting Deployment Guide

This guide explains how to deploy your Laravel project to a shared hosting environment using FTP and cPanel, following the latest Laravel documentation and best practices (as of Laravel 10+).

---

## 1. Prepare Your Project Locally

If you already have your CSS and JS files ready in the `public/` folder, you can skip any npm or build steps. Only use `npm run build` if you are using a frontend build tool (like Vite or Laravel Mix) and need to compile assets from `resources/` to `public/`.

---

## 2. Upload Files via FTP (Single or Multiple Projects)

There are two common ways to host one or more Laravel projects on shared hosting:

### A. Each Project in Its Own Subfolder (Recommended for Multiple Projects)

- Place each Laravel project in its own subfolder under your home directory (e.g., `/home/username/project1`, `/home/username/project2`).
- Each project keeps its own `public` folder (e.g., `/home/username/project1/public`).
- In cPanel, set up a subdomain or addon domain for each project, and set the document root to the `public` folder of that project (e.g., `/home/username/project1/public`).
- This keeps projects isolated and avoids conflicts.
- If you want to access via subfolder (e.g., `yourdomain.com/project1`), copy the contents of each project's `public` folder into a subfolder inside `public_html` (e.g., `public_html/project1/`).
- In this case, you may need to update the `index.php` in each subfolder to point to the correct paths for `vendor` and `bootstrap` (see below).

### B. Single Project in Root (Not recommended for multiple projects)

- If you only have one Laravel project, you can upload the contents of its `public` folder to `public_html/` and the rest of the project outside `public_html/`.
- Update `public_html/index.php` to point to the correct paths if needed.

**Example for multiple projects with subdomains:**

```
/home/username/project1/app
/home/username/project1/bootstrap
/home/username/project1/public (set as document root for subdomain)
/home/username/project2/app
/home/username/project2/bootstrap
/home/username/project2/public (set as document root for another subdomain)
```

**Example for multiple projects with subfolders:**

```
/home/username/project1/app
/home/username/project1/bootstrap
/home/username/public_html/project1/index.php
/home/username/public_html/project1/css/
/home/username/public_html/project2/index.php
/home/username/public_html/project2/css/
```

---

## 3. Update `public/index.php` (Only if using subfolders)

If you are using subdomains or addon domains and set the document root to the `public` folder of each project, you do **not** need to edit `index.php`.

If you are using subfolders (e.g., `public_html/project1/`), edit `public_html/project1/index.php` and update the following lines to point to the correct paths:

```
require __DIR__.'/../../project1/vendor/autoload.php';
$app = require_once __DIR__.'/../../project1/bootstrap/app.php';
```

Replace `project1` with your actual folder name.

---

## 4. Set File Permissions

- Via cPanel terminal or FTP, set the following permissions:
  - `storage/` and `bootstrap/cache/` should be writable (usually 755 or 775).

---

## 5. Composer Install on Server

- Use cPanel Terminal to navigate to your Laravel app directory (not `public_html`).
- Run:
  ```
  composer install --no-dev --optimize-autoloader
  ```

---

## 6. Environment File

- Upload your `.env` file (never commit this to git).
- Set your database credentials, mail settings, and `APP_KEY`.
- If you need to generate a new key, run:
  ```
  php artisan key:generate
  ```

---

## 7. Database Setup

- Create a new database and user via cPanel > MySQL Databases or phpMyAdmin.
- Update your `.env` with the new credentials.
- Run migrations:
  ```
  php artisan migrate --force
  ```
- (Optional) Seed the database:
  ```
  php artisan db:seed --force
  ```

---

## 8. Caching and Optimization

- Run the following for best performance:
  ```
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```

---

## 9. Cron Jobs (Optional)

- Set up a cron job in cPanel to run Laravel's scheduler:
  ```
  * * * * * php /home/username/yourapp/artisan schedule:run >> /dev/null 2>&1
  ```

---

## 10. Troubleshooting

- Check `storage/logs/laravel.log` for errors.
- Ensure permissions are correct if you see write/read errors.
- If you see a blank page, check for PHP errors or missing extensions.

---

## References
- [Laravel Shared Hosting Docs](https://laravel.com/docs/10.x/deployment#shared-hosting)
- [Laravel Deployment Best Practices](https://laravel.com/docs/10.x/deployment)

---

**This guide is up to date as of August 2025.**

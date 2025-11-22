# Laravel Deployment Guide for Shared Hosting (cPanel)

![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![cPanel](https://img.shields.io/badge/cpanel-%23FF6C2C.svg?style=for-the-badge&logo=cpanel&logoColor=white)

This guide provides a detailed, step-by-step process for deploying a Laravel application to a shared hosting environment with cPanel access.

## 1. Pre-Deployment Preparation

> **Important Note**: To avoid breaking your working local development environment, perform these steps in a separate copy of your project specifically for deployment preparation.

### 1.1. Optimize Application for Production

```bash
# Install production dependencies only
composer install --no-dev --optimize-autoloader
```

> **Windows Troubleshooting**: If you encounter file deletion errors on Windows (common with antivirus or Windows Search Indexer), try these solutions:
> - Close your IDE and any file explorer windows that might have project files open
> - Temporarily disable your antivirus
> - Try the alternative approach: `composer update --no-dev`
> - As a last resort, manually delete the vendor directory and run `composer install --no-dev` again

> **Important Note About Development Dependencies**: If you encounter errors like `Class "Laravel\Pail\PailServiceProvider" not found` after running `composer install --no-dev`, it means your application is referencing packages that are marked as development dependencies in `composer.json`. Solutions:
> 1. Edit your `composer.json` to move those packages from `require-dev` to `require` section
> 2. For deployment preparation only, use `composer install` instead of `composer install --no-dev`
> 3. Check your service providers in `config/app.php` and comment out any development-only providers before running production optimizations

```bash
# Compile front-end assets for production
npm install
npm run build

# Create optimized configuration files
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.2. Handle Development Dependencies

Before deploying to production, ensure your application doesn't rely on development packages:

1. **Check for Development Dependencies in Your Code**:
   - Open `config/app.php` and look for service providers that might be development-only
   - Common development providers include: Laravel Pail, Laravel Telescope, Laravel Sail, Laravel Breeze installation providers

2. **Comment Out Development Providers**:
   ```php
   // In config/app.php, comment out development-only providers:
   'providers' => [
       // ...
       // Laravel\Pail\PailServiceProvider::class, // Comment out for production
       // ...
   ],
   ```

3. **Create a Production Copy**:
   - Make these changes in a copy of your project specifically for deployment, not in your main development codebase

### 1.3. Prepare Production Environment File

Create a production-ready `.env` file with the following key configurations:

```
APP_NAME="DR PIXEL"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_from_email
MAIL_FROM_NAME="${APP_NAME}"
```

## 2. cPanel Server Setup

### 2.1. Database Creation

1. Log in to your cPanel dashboard
2. Locate and click on "MySQL Databases"
3. Create a new database:
   - Enter a database name
   - Click "Create Database"
4. Create a database user:
   - Enter a username and password
   - Click "Create User"
5. Add the user to the database:
   - Select the database and user from respective dropdowns
   - Grant "ALL PRIVILEGES"
   - Click "Add"

### 2.2. Application Directory Structure

Create an optimal directory structure to secure your application:

```
public_html/                # Web root directory (publicly accessible)
├── laravel_app/           # Private directory for application files
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── public/                # Publicly accessible content
    ├── index.php
    ├── .htaccess
    ├── css/
    ├── js/
    ├── favicon.ico
    └── robots.txt
```

## 3. Deployment Process

### 3.1. File Upload

1. **Application Files Upload**:
   - Connect to your hosting via FTP/SFTP client (FileZilla, Cyberduck, etc.)
   - Upload all Laravel application files to `laravel_app/` directory (excluding `public/`)
   - Upload `public/` directory contents to `public_html/` or your desired subdirectory

2. **Excluded Items**:
   - `node_modules/`
   - `.git/`
   - `.github/`
   - `tests/`
   - Development configuration files

3. **Upload `.env` file**:
   - Upload your prepared production `.env` file to the `laravel_app/` directory

### 3.2. Configure Public Directory

Update the `index.php` file in the public directory to point to the correct application paths:

```php
// Update these paths to point to your Laravel application location
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
```

### 3.3. Configure .htaccess

Create or update the `.htaccess` file in your public directory:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Prevent directory listings
Options -Indexes

# Set default character set
AddDefaultCharset UTF-8

# Cache control for assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 3.4. Set File Permissions

Set the correct permissions for security and functionality:

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage and bootstrap/cache directories writable
chmod -R 775 laravel_app/storage
chmod -R 775 laravel_app/bootstrap/cache
```

## 4. Database Migration and Setup

### 4.1. SSH Access (If Available)

If SSH access is available:

```bash
# Navigate to application directory
cd ~/laravel_app

# Run migrations
php artisan migrate --force

# Seed database if needed
php artisan db:seed --class=ProductionSeeder

# Generate application key if not already set
php artisan key:generate

# Create symbolic link for storage
php artisan storage:link
```

### 4.2. Without SSH Access

If SSH is not available:

1. **Create Migration Script**:
   - Create a temporary PHP file (e.g., `migrate.php`) in your public directory:

```php
<?php
require __DIR__.'/../laravel_app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Run migrations
$kernel->call('migrate', ['--force' => true]);

// Seed database
$kernel->call('db:seed', ['--class' => 'ProductionSeeder', '--force' => true]);

// Generate application key
$kernel->call('key:generate');

// Link storage
$kernel->call('storage:link');

echo "Database migration and setup completed successfully!";
```

2. **Execute the Script**:
   - Visit `https://your-domain.com/migrate.php` in your browser
   - After successful execution, delete this file immediately for security

## 5. Post-Deployment Configuration

### 5.1. Storage Symbolic Link

If storage:link doesn't work on your shared hosting:

1. Manually create a symlink or copy the contents of `laravel_app/storage/app/public/` to `public_html/storage/`
2. Update your asset URLs to use the correct storage path

### 5.2. Cron Jobs Setup

Set up Laravel Scheduler via cPanel Cron Jobs:

1. Navigate to cPanel > Cron Jobs
2. Add a new cron job:
   - Set frequency to "Every 1 minute"
   - Command: `php /home/username/laravel_app/artisan schedule:run >> /dev/null 2>&1`

### 5.3. Queue Worker (Optional)

If your application uses queues:

1. Set up a cron job to process jobs:
   ```
   * * * * * php /home/username/laravel_app/artisan queue:work --stop-when-empty >> /dev/null 2>&1
   ```

## 6. Security Measures

### 6.1. Protect Sensitive Files

1. Create `.htaccess` files in sensitive directories:

```apache
# Place in laravel_app/.htaccess
Order deny,allow
Deny from all
```

2. Ensure sensitive files are not accessible via web browser:
   - `.env`
   - `composer.json`
   - `package.json`
   - `artisan`

### 6.2. SSL Configuration

1. Enable SSL in cPanel (Let's Encrypt or purchase SSL certificate)
2. Enforce HTTPS in `.htaccess`:

```apache
# Add to public_html/.htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 7. Testing and Verification

### 7.1. Application Functionality

Verify all key functionality works correctly:

- User registration and login
- Database operations
- File uploads and storage
- Email sending
- Form submissions
- Admin panel access

### 7.2. Error Logging

1. Check Laravel logs at `laravel_app/storage/logs/laravel.log`
2. Monitor cPanel error logs for PHP or server issues

## 8. Maintenance and Updates

### 8.1. Application Updates

To update your application:

1. Enable maintenance mode (if available):
   ```php
   // Create update.php in public directory
   <?php
   require __DIR__.'/../laravel_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
   $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
   $kernel->call('down');
   echo "Application is now in maintenance mode";
   ```

2. Update application files via FTP
3. Run migrations if necessary
4. Disable maintenance mode:
   ```php
   // Create up.php in public directory
   <?php
   require __DIR__.'/../laravel_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
   $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
   $kernel->call('up');
   echo "Application is now live";
   ```

### 8.2. Backup Strategy

1. **Database Backups**:
   - Use cPanel's MySQL Backup feature
   - Schedule regular backups (daily/weekly)
   - Download and store offsite

2. **File Backups**:
   - Use cPanel's Backup Wizard
   - Create full or partial backups
   - Download and store offsite

## 9. Troubleshooting Common Issues

### 9.1. 500 Internal Server Error

Check the following:
- File permissions
- .htaccess configuration
- PHP version compatibility
- Error logs in cPanel

### 9.2. URL Rewriting Issues

- Ensure mod_rewrite is enabled
- Verify .htaccess file is correct
- Contact hosting provider if issues persist

### 9.3. White Screen/Blank Page

- Check PHP memory limit in cPanel
- Enable error display temporarily for debugging
- Review Laravel logs

### 9.4. Recovering Local Development Environment

If you've broken your local development environment during deployment preparation:

1. **Missing Development Dependencies**:
   If you get errors like `Class "Laravel\Pail\PailServiceProvider" not found` after running `composer install --no-dev`:
   ```bash
   # Reinstall all dependencies including development packages
   composer install
   ```

2. **Cached Configuration**:
   If your application has cached configuration that references missing providers:
   ```bash
   # Clear all Laravel caches
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

3. **Reset Application**:
   If your local environment is still not working:
   ```bash
   # Remove vendor directory and reinstall
   rm -rf vendor
   composer install

   # Regenerate autoloader
   composer dump-autoload
   ```

## 10. Performance Optimization

### 10.1. Shared Hosting Optimizations

1. Use caching aggressively:
   ```php
   // In .env
   CACHE_DRIVER=file
   ```

2. Optimize autoloader:
   ```bash
   composer dump-autoload --optimize
   ```

3. Reduce database queries using eager loading

4. Optimize images and assets

5. Consider a CDN for static assets

## 10.2. Deployment Checklist

Use this practical checklist to ensure a smooth deployment process:

### Pre-Deployment
- [ ] Back up your local development environment
- [ ] Run all tests to ensure application stability
- [ ] Optimize local application (composer, npm)
- [ ] Create production-ready `.env` file
- [ ] Record all third-party credentials (database, mail, etc.)
- [ ] Check for hardcoded development URLs in your code

### Server Setup
- [ ] Create database and database user
- [ ] Create appropriate directory structure
- [ ] Set up SSL certificate
- [ ] Configure PHP version and settings

### File Transfer
- [ ] Upload application files (excluding `public/`)
- [ ] Upload `public/` contents to web root
- [ ] Upload production `.env` file
- [ ] Verify all files transferred correctly
- [ ] Set correct file permissions

### Configuration
- [ ] Update `index.php` paths
- [ ] Configure `.htaccess` file
- [ ] Run database migrations
- [ ] Set up storage symbolic link
- [ ] Configure cron jobs

### Testing
- [ ] Verify application loads correctly
- [ ] Test all critical functionality
- [ ] Check for 404/500 errors
- [ ] Test forms and user authentication
- [ ] Verify file uploads work
- [ ] Test on multiple browsers and devices

### Post-Deployment
- [ ] Set up monitoring
- [ ] Configure backups
- [ ] Document deployment process for future updates
- [ ] Test application under load (if possible)

## 11. Platform-Specific Considerations

### 11.1. Windows Development Environment

When preparing deployment from a Windows environment:

- **File Permissions**: Windows doesn't use the same permission system as Unix. You'll need to set proper permissions after uploading files to the server.

- **Line Endings**: Windows uses CRLF (`\r\n`) while Linux/Unix uses LF (`\n`). Configure Git to handle line endings appropriately:
  ```bash
  git config --global core.autocrlf input
  ```

- **Path Separators**: Windows uses backslashes (`\`) while Linux/Unix uses forward slashes (`/`). Always use forward slashes in your code for cross-platform compatibility.

- **File Locking**: Windows may lock files that are in use, making it difficult to replace them during deployment preparation. Close all applications that might be accessing project files before running deployment commands.

### 11.2. macOS Development Environment

When preparing deployment from a macOS environment:

- **Hidden Files**: macOS creates `.DS_Store` files that should not be uploaded to the server. Add them to your `.gitignore` file.

- **Case Sensitivity**: macOS filesystem is case-insensitive by default, while most Linux servers are case-sensitive. Be careful with file naming consistency.

### 11.3. Linux Development Environment

When preparing deployment from a Linux environment:

- **Permission Preservation**: When using tools like `rsync` for deployment, use options that preserve permissions to avoid manual permission setting on the server.

## 12. Contact and Support

For issues or questions about this deployment guide:

- Developer: [DR PIXEL](https://drpixel.it.nf/)
- Email: [your-support-email@domain.com]

---

*This deployment guide was last updated on July 25, 2025.*

# Passiflor

**"Regresa a Tu Ser Natural"**

Passiflor is a comprehensive therapy practice management platform built for a collective of psychologists committed to helping individuals reconnect with their natural selves. The application provides a complete solution for managing therapists, patients, personalized therapy sessions, and online consultations.

## About Passiflor

In a world that often takes us away from ourselves, Passiflor offers a way back. We are a **collective of psychologists** committed to helping you reconnect with your **natural self**—that part of you that exists beyond roles, expectations, and noise.

Here, **growth is constant**, **healing has solid foundations**, and knowledge is a quiet return to what has always been within you.

## Key Features

### 🧠 Therapy Management System
- **Personalized Therapy Pages**: Create dynamic, patient-specific therapy content with customizable sections
- **Multi-page Therapy Sessions**: Hero sections with step-by-step guidance and informational content
- **Therapist Assignment**: Link therapies to specific therapists and patients
- **Draft & Published States**: Preview unpublished therapies before making them public

### 👥 User Role Management
- **Four User Roles**: Admin, Therapist, Patient, Guest
- **Therapist-Patient Relationships**: Assign patients to therapists for personalized care
- **Role-based Authorization**: Granular permissions using Laravel policies

### 📅 Consultation Booking System
- **Free 15-Minute Consultations**: Online booking form for initial consultations
- **Email Notifications**: Automated confirmation emails to patients and therapists
- **Admin Dashboard**: Manage and track all consultation requests

### 🎨 Modern Admin Panel
- **Complete CRUD Operations**: Manage users, therapies, and consultations
- **Therapy Builder**: Visual interface for creating multi-page therapy content
- **User Management**: Create and assign therapists to patients
- **Responsive Dashboard**: Mobile-friendly admin interface

### 🔐 Authentication & Security
- **Laravel Sanctum**: Secure authentication system
- **CSRF Protection**: Built-in security measures
- **Password Hashing**: Secure user credential storage
- **Policy-based Authorization**: Fine-grained access control

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: SQLite (development), MySQL/PostgreSQL (production)
- **Frontend**: Blade templates, custom CSS/JS
- **Icons**: Phosphor Icons
- **Authentication**: Laravel Sanctum
- **Email**: Laravel Mail with SMTP support

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM (for asset compilation)
- SQLite (or MySQL/PostgreSQL for production)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/jpjacome/passiflor.git
   cd passiflor-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (edit `.env`)
   ```env
   DB_CONNECTION=sqlite
   # Or for MySQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=passiflor
   # DB_USERNAME=root
   # DB_PASSWORD=
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Create storage symbolic link**
   ```bash
   php artisan storage:link
   ```

7. **Compile assets**
   ```bash
   npm run build
   ```

8. **Serve the application**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## Database Schema

### Users Table
- Roles: `admin`, `therapist`, `patient`, `guest`
- Therapist assignment for patients via `therapist_id`
- Standard authentication fields

### Therapies Table
- Dynamic therapy content management
- Author and therapist assignment
- Patient assignment for personalized therapy
- Published state for draft/live control

### Therapy Pages Table
- Multi-page therapy content
- Page types: `hero`, `step`, `info`
- Position-based ordering
- Rich content fields (title, subtitle, body, notes)

### Consultations Table
- Free consultation booking requests
- Patient information and session preferences
- Email tracking and consent management

## User Roles & Permissions

### Admin
- Full access to all features
- User management (create therapists, patients, admins)
- Therapy CRUD operations
- Consultation management
- System configuration

### Therapist
- Create and manage therapies
- View assigned patients
- Preview unpublished therapies
- Manage consultations

### Patient
- View assigned therapies
- Access published therapy content
- Book consultations

### Guest
- View public therapy pages (published only)
- Book consultations
- View homepage

## Services Offered

- **Individual Therapy**: Evidence-based personalized therapy adapted to unique healing journeys
- **Group Sessions**: Community-based supportive environment for shared healing
- **Online Therapy**: Remote sessions for accessibility
- **Integrative Therapy**: Holistic approach combining multiple modalities
- **Psychedelic Therapy**: Guided therapeutic sessions
- **Microdosing Support**: Professional guidance for microdosing protocols
- **Autism Support**: Specialized therapy for autism spectrum
- **Child & Young Adult Therapy**: Age-appropriate therapeutic approaches
- **Trauma Recovery**: Support for trauma survivors
- **Parent Support**: Guidance for parents navigating challenges

## Configuration

### Mail Setup
Configure SMTP settings in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@passiflor.org
MAIL_FROM_NAME="Passiflor"
```

### Application URL
```env
APP_URL=https://passiflor.org
```

## Deployment

Refer to `LARAVEL_SHARED_HOSTING_DEPLOYMENT.md` for detailed deployment instructions for shared hosting environments.

### Quick Production Checklist
- Set `APP_ENV=production` and `APP_DEBUG=false`
- Configure production database
- Set up SSL certificate
- Configure mail service
- Run `php artisan config:cache`
- Run `php artisan route:cache`
- Run `php artisan view:cache`
- Set proper file permissions

## Development

### Running Tests
```bash
php artisan test
```

### Code Style
This project follows Laravel coding standards and uses PHP Pint for code formatting:
```bash
./vendor/bin/pint
```

## Documentation

Additional documentation available in the repository:
- `ADMIN_PANEL_SETUP.md` - Admin panel implementation details
- `CONSULTATION_SYSTEM_SUMMARY.md` - Consultation booking system
- `LOGIN_AUTHENTICATION_IMPLEMENTATION.md` - Authentication system
- `THERAPIES_IMPLEMENTATION.md` - Therapy management system
- `LARAVEL_SHARED_HOSTING_DEPLOYMENT.md` - Deployment guide

## Contact

- **Website**: [passiflor.org](https://passiflor.org)
- **Email**: info@passiflor.com
- **Phone**: +593 (9) 9064-9181
- **Instagram**: [@passiflor__](https://www.instagram.com/passiflor__/)

## Credits

Carefully crafted by [DR PIXEL](https://drpixel.it.nf)

## License

This project is proprietary software. All rights reserved © 2025 Passiflor.

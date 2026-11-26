## Application Setup

Run the Laravel migrations, then create an account through `/register`. When the built-in registration challenge is enabled, registration requires solving the displayed addition problem. Sign in with the account's username or email address. New passwords must contain uppercase and lowercase letters, a number, and a symbol.

Grant administrator access from the command line after the account exists:

```sh
php artisan app:promote-admin user@example.com
```

The application seeds default roles and permissions through migrations. Administrators can manage role permissions at `/admin/roles`; route middleware enforces permissions even if a navigation link is opened directly. The default Manager role can view reports and the audit trail. The administrator's profile page supports TOTP or email verification and displays one-time recovery codes when MFA is enabled.

Reports can be downloaded as CSV, XLSX, or PDF. Controlled QMS documents are stored privately; their revisions, approval status, owners, and history are available in the QMS area. The QMS features are record-management tools only: they do not establish or certify ISO 9001 conformity.

Set `BACKUP_ARCHIVE_PASSWORD` to a long, unique secret before creating backups. Encrypted archives are stored privately under `storage/app/backups`; configure off-host storage and retain the encryption secret separately for production. The configured backup-retention period is applied by the scheduled cleanup task. Run Laravel's scheduler in production (or `php artisan schedule:work` locally).

### Configuration

Copy `.env.example` to `.env`, set `APP_URL`, `APP_KEY`, and database settings, then run:

```sh
composer install
npm install
php artisan key:generate
php artisan migrate
npm run build
php artisan db:seed
```

Configure SMTP with `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and `MAIL_FROM_ADDRESS` for password-reset and email-MFA delivery. An administrator may also set the SMTP host, port, username, and encrypted password in System Settings; a sender address and encryption scheme still come from deployment configuration. Email delivery cannot be verified until valid SMTP credentials are supplied.

The registration challenge is an arithmetic check, not a third-party CAPTCHA service. Selecting a provider name in settings does not integrate an external CAPTCHA provider. System-wide MFA enforcement requires each account to enroll before accessing the rest of the authenticated application.

Production prerequisites include strong `APP_KEY` management, HTTPS, a configured mail sender, a running scheduler, off-host encrypted backups, tested restoration procedures, and organization-specific QMS policies and evidence. ISO 9001 certification or conformity requires independent assessment and cannot be established by this application.

### Online appointments

New self-registered accounts are patient (`Guest`) accounts and can book appointments from the Dashboard. Administrators can create active `Clinician` and `Receptionist` accounts under User Management; clinicians need an active account to appear in booking. Booking is available Monday through Friday, 9:00 AM–5:00 PM in 30-minute slots, up to 90 days ahead. These default hours are implemented in `app/Services/AppointmentScheduling.php` and should be updated to match the clinic before production use. Patient requests are pending until the receptionist confirms them.

Receptionists can review the appointment queue, confirm or check in patients, record cancellations, and mark no-shows. Clinicians see their assigned appointments and can record clinical notes, diagnosis, treatment plans, and follow-up dates. Patients can cancel pending or confirmed visits and see their own consultation history. Appointment and consultation records are private to the patient and authorized clinic staff. Review privacy and retention policies before using this workflow in production.

### Backup restoration

Download the private backup archive from `/admin/backups`, keep its encryption password secure, and restore it in a staging environment first. Stop the application and restore the selected database dump or application files according to the dump contents. After restoration, run `php artisan migrate --force` and verify that `database/database.sqlite` or the configured production database is readable. Never place credentials or `.env` files in downloadable public directories.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

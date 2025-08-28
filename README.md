# 🚀 Laravel Multi-Dashboard Starter Kit

A comprehensive, production-ready Laravel starter kit with React/Inertia.js that provides dynamic multi-dashboard functionality, multi-database support, SMS authentication, and responsive design.

## ✨ Features

### 🏗️ Core Architecture
- **Laravel 12.x** - Latest version with modern features
- **React + Inertia.js** - SEO-optimized SPA experience
- **Tailwind CSS** - Responsive design with dark/light modes
- **Multi-Database Support** - Separate databases per dashboard
- **Dynamic Dashboard Generation** - Auto-create dashboards via Artisan commands

### 🔐 Authentication & Security
- **Multi-Guard Authentication** - Separate auth per dashboard
- **SMS OTP Integration** - Aakash SMS API support
- **Email Verification** - Complete email auth flow
- **Role-Based Access Control** - Spatie Permissions integration
- **Rate Limiting** - Built-in protection against brute force

### 🎨 UI/UX Features
- **Responsive Layouts** - Mobile-first design with sidebars
- **Dynamic Theming** - Customizable colors and dark mode
- **Multi-Language Support** - I18n ready
- **Standard Typography** - Inter font family
- **Component Library** - Reusable React components

### 📱 API & Mobile
- **RESTful APIs** - Dashboard-specific endpoints
- **JWT Authentication** - Mobile app ready
- **Rate Limiting** - API protection
- **CORS Support** - Cross-origin requests

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Node.js 18+
- MySQL 8.0+
- Composer

### Installation

1. **Clone and Setup**
```bash
git clone <repository-url>
cd laravel_react_api_starter_kit
composer install
npm install
```

2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**
Create two databases:
- `laravel_react_starter_kit` (main)
- `laravel_starter_test` (test dashboard)

Update `.env`:
```env
# Main Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_react_starter_kit
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Test Database
DB_CONNECTION_TEST=test_mysql
DB_HOST_TEST=127.0.0.1
DB_PORT_TEST=3306
DB_DATABASE_TEST=laravel_starter_test
DB_USERNAME_TEST=your_username
DB_PASSWORD_TEST=your_password

# SMS Configuration (Aakash SMS)
AAKASH_SMS_TOKEN=your_token_here
AAKASH_SMS_FROM=your_sender_id
AAKASH_SMS_TEST_PHONE=9843223774

# Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

4. **Run Migrations and Seeders**
```bash
php artisan migrate
php artisan migrate --database=test_mysql
php artisan db:seed --class=SimpleTestSeeder
```

5. **Build Assets and Start**
```bash
npm run build
php artisan serve
```

## 📊 Dashboard Management

### Create New Dashboard
```bash
php artisan dashboard:create {type} [--force]
```

Example:
```bash
php artisan dashboard:create hospital
php artisan dashboard:create clinic --force
```

This automatically creates:
- Models with proper database connections
- Controllers (Web + API)
- React components and layouts
- Routes and middleware
- Migrations for permissions
- Authentication guards

### Dashboard Structure
```
app/
├── Http/Controllers/{Type}/
│   ├── AuthController.php
│   ├── DashboardController.php
│   └── Api/AuthController.php
├── Models/{Type}/
│   └── User.php
resources/js/
├── Pages/{Type}/
│   ├── Dashboard.jsx
│   └── Auth/Login.jsx
└── Layouts/{Type}/
    └── AppLayout.jsx
```

## 🔑 Authentication

### Test Credentials
- **Email**: admin@test.com / password
- **Email**: manager@test.com / password  
- **Email**: user@test.com / password
- **SMS**: 9843223774 (Demo OTP: 123456)

### Access URLs
- Test Login: http://127.0.0.1:8000/test/login
- Test Dashboard: http://127.0.0.1:8000/test/dashboard
- Master Admin: http://127.0.0.1:8000/master-admin/dashboard

## 🛠️ Configuration

### Multi-Database Setup
Each dashboard can use its own database connection. Configure in `config/database.php`:

```php
'connections' => [
    'mysql' => [...], // Main database
    'test_mysql' => [...], // Test dashboard database
    'hospital_mysql' => [...], // Hospital dashboard database
]
```

### Theme Customization
Themes are stored in `dashboard_types` table:
```php
'theme_config' => [
    'primary_color' => '#3b82f6',
    'secondary_color' => '#64748b',
    'dark_mode' => false,
    'sidebar_color' => '#ffffff',
    'background_color' => '#f9fafb',
]
```

### SMS Configuration
Update Aakash SMS settings in `.env`:
```env
AAKASH_SMS_TOKEN=your_api_token
AAKASH_SMS_FROM=YourBrand
AAKASH_SMS_TEST_PHONE=98XXXXXXXX
```

## 📱 API Documentation

### Authentication Endpoints
```http
POST /api/{dashboard}/login
POST /api/{dashboard}/register
POST /api/{dashboard}/logout
POST /api/{dashboard}/send-sms-otp
POST /api/{dashboard}/verify-sms-otp
```

### Dashboard Endpoints
```http
GET /api/{dashboard}/user
PUT /api/{dashboard}/user/profile
GET /api/{dashboard}/dashboard-stats
```

Example:
```bash
curl -X POST http://127.0.0.1:8000/api/test/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'
```

## 🧪 Testing

### System Verification
```bash
php artisan tinker --execute="require 'system-verification.php'"
```

### Manual Testing
1. Visit login pages for different dashboards
2. Test email and SMS authentication
3. Verify dashboard access and functionality
4. Test API endpoints with tools like Postman

## 🔧 Development

### Adding New Features
1. Use the dashboard generator for new dashboard types
2. Extend base controllers and models
3. Add new React components in appropriate directories
4. Update routes and middleware as needed

### Database Migrations
- Main database: `php artisan migrate`
- Specific database: `php artisan migrate --database=test_mysql`

### Frontend Development
```bash
npm run dev     # Development with hot reload
npm run build   # Production build
```

## 📋 System Requirements

### Production Requirements
- **PHP**: 8.2+ with extensions (bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml)
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx or Apache with URL rewriting
- **Node.js**: 18+ for asset compilation
- **Memory**: 512MB+ RAM
- **Storage**: 1GB+ disk space

### Development Requirements
- All production requirements plus:
- **Composer**: Latest version
- **NPM/Yarn**: Package management
- **Git**: Version control

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up SSL certificates
- [ ] Configure email and SMS services
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Set proper file permissions

### Deployment Commands
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation
- Review the system verification output

---

**Built with ❤️ using Laravel, React, and modern web technologies.**
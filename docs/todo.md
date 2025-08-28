# Complete Development Instructions - Laravel Multi-Dashboard Starter Kit

## 🔑 Essential Credentials & Configuration

### Database Setup
```
Database Name: laravel_react_starter_kit
Username: database_user
Password: Suxen++
Host: localhost
Port: 3306
```

### Aakash SMS Configuration
```
API Token: e03c6792746ba77e2c26e4245a5c78e813878f0be3976f26d79072407ad5bc96
Test Phone Number : 9843223774
Provider: Aakash SMS Nepal
```

### SMTP Configuration
```
Username: test@fewaweb.com
Password: Clfa4ace7d++
SMTP Server: mail.fewaweb.com
SMTP Port: 465 (SSL)
IMAP Port: 993
POP3 Port: 995
```

## 🎯 Complete System Requirements

### Core System Architecture
Build a Laravel application with React/Inertia that can dynamically generate unlimited dashboard types (doctor, nurse, manufacturer, supplier, etc.). Each dashboard type operates as a separate system with its own authentication, users, roles, and APIs while sharing the same codebase and optionally the same database.

### Master Admin System
Create a super admin system that can configure everything: email/phone authentication toggle, system color codes, login attempt limits, log retention periods, auto-cleanup schedules, database backup automation, tenant management, and all system-wide settings. This admin panel should be the central control hub.

### Dynamic Dashboard Generation
Build an artisan command that creates complete dashboard systems on demand. When you run the command, it should generate controllers, models, migrations, routes, React components, API endpoints, authentication systems, role management, and admin interfaces specific to that dashboard type.

### Multi-Database Support
Design the system to work with shared database initially but be capable of switching to separate databases per tenant. Include database connection management, migration synchronization, and data isolation mechanisms.

### Authentication & Communication Systems
Implement both SMS (Aakash SMS) and email authentication systems that can be configured per dashboard type. Include OTP generation, verification workflows, notification systems, delivery status tracking, and fallback mechanisms.

### Security & Compliance Framework
Build comprehensive security including SQL injection prevention, XSS protection, CSRF tokens, rate limiting, audit logging, intrusion detection, session security, and compliance features. All security measures should be configurable per dashboard type.

### API Architecture for Mobile
Create separate API endpoints for each dashboard type with JWT authentication, proper versioning, rate limiting, caching, offline sync capabilities, and comprehensive documentation. APIs should be mobile-optimized with minimal data transfer.

### Theme & Customization System
Build dynamic theming where each dashboard type can have custom colors, logos, layouts, dark/light modes, multi-language support, and user preferences. Themes should be inheritable and overridable at multiple levels.

### Testing & Quality Assurance
Implement comprehensive testing including unit tests, integration tests, security tests, performance tests, and edge case testing. Include automated testing for generated dashboards and security vulnerability scanning.

### Logging & Monitoring
Create advanced logging with automatic cleanup, log rotation, performance monitoring, security event tracking, audit trails, and automated alerting. Logs should be searchable, filterable, and exportable.

### Documentation & Maintenance
Generate automatic documentation for generated dashboards, API endpoints, security configurations, and system architecture. Include troubleshooting guides, deployment procedures, and maintenance schedules.

## 🔧 Development Guidelines & Standards

### Code Quality Requirements
Follow Laravel conventions strictly, use PSR-12 coding standards, implement proper error handling, create reusable services and traits, use dependency injection, implement caching strategies, and maintain clean architecture patterns throughout.

### Security Implementation Standards  
Validate all inputs, sanitize all outputs, use parameterized queries, implement proper authentication flows, encrypt sensitive data, secure file uploads, implement rate limiting, log security events, and follow OWASP security guidelines.

### Performance Requirements
Optimize database queries with proper indexing, implement caching at multiple levels, use queue systems for heavy operations, optimize assets and images, implement lazy loading, monitor performance metrics, and ensure scalable architecture.

### Testing Requirements
Write unit tests for all models and services, create feature tests for all workflows, implement security testing for vulnerabilities, create performance tests for scalability, test edge cases and error conditions, and maintain high test coverage.

### Documentation Standards
Document all APIs with examples, create setup and deployment guides, maintain security documentation, document all configuration options, create troubleshooting guides, and keep documentation current with code changes.

## 🧪 Testing Instructions

### MySQL Testing Commands
### timnker testing 

### Security Testing Requirements
Test SQL injection on all input fields, verify XSS protection in React components, check CSRF token validation, test authentication bypass attempts, verify authorization escalation prevention, test rate limiting effectiveness, check session security measures, verify data encryption, test file upload security, and validate input sanitization.

### Edge Case Testing Requirements
Test dashboard creation with invalid names, test concurrent user sessions across dashboards, test database connection failures, test SMS/email delivery failures, test high-volume concurrent requests, test system behavior under memory pressure, test backup and recovery procedures, test theme switching during active sessions, test API rate limiting under load, and test log cleanup during high activity.

### Performance Testing Requirements
Test database query performance with large datasets, test concurrent dashboard generation, test API response times under load, test theme loading performance, test authentication performance with multiple guards, test log writing performance, test backup system performance, and test overall system scalability.

## 🎨 Admin Customization Features

### System Configuration Options
Email/phone authentication toggle per dashboard, system-wide color scheme management, login attempt limits and lockout durations, log retention periods and cleanup schedules, backup frequency and retention policies, maintenance mode scheduling, security alert thresholds, API rate limiting configurations, and theme inheritance rules.

### Dashboard-Specific Settings
Custom color schemes and branding per dashboard type, authentication method selection (SMS, email, both), role hierarchy configuration, permission matrix management, notification preferences, user registration policies, password complexity requirements, session timeout settings, and feature toggles.

### User Management Options
Bulk user import/export capabilities, user activity monitoring, role assignment workflows, permission auditing tools, user communication preferences, account activation workflows, password reset policies, and user analytics dashboards.

### Security & Compliance Controls
Audit log configuration and retention, security event alerting, compliance reporting automation, data export and purging tools, encryption key management, access control reviews, vulnerability scanning schedules, and incident response workflows.

### System Maintenance Features
Automated database optimization, log file rotation and compression, backup verification and testing, system health monitoring, performance metric collection, storage cleanup automation, and maintenance window scheduling.

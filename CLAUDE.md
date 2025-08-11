# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Building and Asset Compilation
```bash
npm run dev          # Start Vite development server for assets
npm run build        # Build production assets
```

### Testing
```bash
php artisan test     # Run all PHPUnit/Pest tests
vendor/bin/pest      # Run tests using Pest directly
vendor/bin/phpunit   # Run tests using PHPUnit directly
```

### Code Quality
```bash
vendor/bin/pint      # Format PHP code using Laravel Pint
```

### Database Operations
```bash
php artisan migrate           # Run database migrations
php artisan migrate:fresh     # Fresh migration (drops all tables)
php artisan db:seed          # Run database seeders
php artisan migrate:fresh --seed  # Fresh migration with seeding
```

### Queue and Jobs
```bash
php artisan queue:work       # Start processing queued jobs
php artisan schedule:work    # Run the task scheduler
```

### Custom Artisan Commands
```bash
php artisan send:dealer-email        # Send scheduled dealer emails
php artisan send:reminder            # Send reminder emails
php artisan update:next-send-date    # Update next send dates for emails
php artisan assign:users-by-state    # Assign users to dealerships by state
php artisan remove:users-by-state    # Remove users from dealerships by state
```

## Architecture Overview

This is a Laravel 10 CRM application built with Filament v3 for the admin interface. The system manages dealerships, contacts, and email communications.

### Core Domain Models

- **Dealership**: Central entity representing automotive/RV/motorsports/maritime dealerships
- **Contact**: Individual contacts associated with dealerships
- **User**: System users with role-based permissions via Spatie Permission
- **DealerEmail**: Scheduled marketing emails sent to dealerships
- **DealerEmailTemplate**: Reusable email templates
- **SentEmail**: Log of sent emails for tracking
- **Progress**: Activity log entries for dealership interactions
- **Reminder**: Scheduled reminder system
- **PdfAttachment**: File attachments for emails

### Key Integrations

- **Filament**: Primary admin interface with two panels (main + development)
- **Spatie Activity Log**: Comprehensive activity logging across models
- **Spatie Permission**: Role-based access control
- **Mailcoach SDK**: Email marketing integration with contact tagging
- **Laravel Jetstream**: Authentication scaffolding with teams
- **Laravel Telescope**: Application debugging and monitoring
- **Sentry**: Error tracking and performance monitoring

### Filament Structure

The application uses Filament v3 with:
- Main admin panel for standard operations
- Development panel (`app/Filament/Development/`) for development-specific resources
- Custom widgets for dashboards and overviews
- Extensive use of relation managers for nested data

### Email System Architecture

The email system uses a job-based approach:
- `SendDealerEmail` job handles email dispatch
- Templates support customization per dealership
- PDF attachments via polymorphic relationships
- Integration with Mailcoach for list management and tagging

### Key Business Logic

1. **Dealership Types**: Automotive, RV, Motorsports, Maritime, Association
2. **Development Status**: Tracked via `in_development` flag and `dev_status` enum
3. **Rating System**: Hot/Warm/Cold classification for dealerships
4. **Geographic Organization**: State-based assignment and filtering
5. **Multi-user Assignment**: Many-to-many relationship between users and dealerships

### File Organization

- `app/Filament/Resources/`: Main Filament resource classes
- `app/Filament/Development/Resources/`: Development-specific resources  
- `app/Models/`: Eloquent models with relationships and business logic
- `app/Jobs/`: Background job classes for email sending
- `app/Mail/`: Mailable classes for various email types
- `app/Listeners/`: Event listeners, particularly for Mailcoach integration
- `app/Console/Commands/`: Custom Artisan commands for bulk operations
- `app/Policies/`: Authorization policies for resource access

### Database Design

Key relationships:
- Dealerships have many contacts, stores, progress entries, and dealer emails
- Users can be assigned to multiple dealerships (many-to-many)
- Email templates can be customized per dealership
- PDF attachments use polymorphic relationships for flexibility
- Contact tagging syncs with external Mailcoach service

### Testing Strategy

Uses Pest PHP testing framework with:
- Feature tests for authentication and core functionality
- Unit tests for isolated component testing
- SQLite in-memory database for test performance
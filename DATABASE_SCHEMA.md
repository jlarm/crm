# Database Schema

This document outlines the complete database schema for the CRM application.

## Core Business Tables

### dealerships
Primary entity representing automotive/RV/motorsports/maritime dealerships.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id | Owner/creator of dealership |
| name | varchar(255) | | Dealership name |
| address | varchar(255) | nullable | Street address |
| city | varchar(255) | nullable | City |
| state | varchar(255) | nullable | State |
| zip_code | varchar(255) | nullable | ZIP/postal code |
| phone | varchar(255) | nullable | Phone number |
| email | varchar(255) | nullable | Email address |
| current_solution_name | varchar(255) | nullable | Current software solution |
| current_solution_use | varchar(255) | nullable | How they use current solution |
| notes | text | nullable | General notes |
| status | varchar(255) | | Status (active, inactive, etc.) |
| rating | varchar(255) | | Rating (Hot, Warm, Cold) |
| type | varchar(255) | | Type (Automotive, RV, Motorsports, Maritime, Association) |
| in_development | boolean | default: false | Development flag |
| dev_status | varchar(255) | nullable | Development status enum |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)

---

### contacts
Individual contacts associated with dealerships.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| dealership_id | bigint unsigned | FK → dealerships.id | Associated dealership |
| name | varchar(255) | | Contact name |
| email | varchar(255) | nullable | Email address |
| phone | varchar(255) | nullable | Phone number |
| position | varchar(255) | nullable | Job position/title |
| linkedin_link | varchar(255) | nullable | LinkedIn profile URL |
| primary_contact | boolean | default: false | Is primary contact |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (dealership_id) → dealerships(id)

---

### stores
Individual store locations belonging to dealerships.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id | Associated user |
| dealership_id | bigint unsigned | FK → dealerships.id | Parent dealership |
| name | varchar(255) | | Store name |
| address | varchar(255) | nullable | Street address |
| city | varchar(255) | nullable | City |
| state | varchar(255) | nullable | State |
| zip_code | varchar(255) | nullable | ZIP/postal code |
| phone | varchar(255) | nullable | Phone number |
| current_solution_name | varchar(255) | nullable | Current software solution |
| current_solution_use | varchar(255) | nullable | How they use current solution |
| notes | text | nullable | Store-specific notes |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)
- FOREIGN KEY (dealership_id) → dealerships(id)

---

### progresses
Activity log entries for dealership interactions and progress tracking.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id | User who created entry |
| dealership_id | bigint unsigned | FK → dealerships.id | Associated dealership |
| contact_id | bigint unsigned | FK → contacts.id, nullable | Associated contact (if any) |
| progress_category_id | bigint unsigned | FK → progress_categories.id, nullable | Category of progress |
| details | text | | Progress details/notes |
| date | date | nullable | Date of activity |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)
- FOREIGN KEY (dealership_id) → dealerships(id)
- FOREIGN KEY (contact_id) → contacts(id)
- FOREIGN KEY (progress_category_id) → progress_categories(id)

---

### progress_categories
Categories for progress entries.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| name | varchar(255) | | Category name |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)

---

## Email System Tables

### dealer_emails
Scheduled marketing emails sent to dealerships.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id | User who created email |
| dealership_id | bigint unsigned | FK → dealerships.id | Target dealership |
| dealer_email_template_id | bigint unsigned | FK → dealer_email_templates.id, nullable | Template used |
| customize_email | boolean | default: false | Whether email is customized |
| customize_attachment | boolean | default: false | Whether attachment is customized |
| recipients | json | | JSON array of recipient emails |
| attachment | varchar(255) | nullable | Legacy attachment path |
| subject | varchar(255) | nullable | Email subject (if customized) |
| message | text | nullable | Email body (if customized) |
| start_date | date | nullable | When to start sending |
| last_sent | date | nullable | Last sent date |
| next_send_date | date | nullable | Next scheduled send date |
| frequency | integer | nullable | Send frequency in days |
| paused | boolean | default: false | Whether sending is paused |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)
- FOREIGN KEY (dealership_id) → dealerships(id)
- FOREIGN KEY (dealer_email_template_id) → dealer_email_templates(id)

---

### dealer_email_templates
Reusable email templates for dealer communications.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| name | varchar(255) | | Template name |
| subject | varchar(255) | | Email subject |
| body | text | | Email body content |
| attachment_path | varchar(255) | nullable | Path to attachment file |
| attachment_name | varchar(255) | nullable | Display name for attachment |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)

---

### sent_emails
Log of all sent emails for tracking purposes.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id | User who sent email |
| dealership_id | bigint unsigned | FK → dealerships.id | Target dealership |
| recipient | varchar(255) | | Recipient email address |
| message_id | varchar(255) | nullable, indexed | Mailgun message ID |
| subject | varchar(255) | nullable | Email subject |
| tracking_data | json | nullable | Email tracking data |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (message_id)
- FOREIGN KEY (user_id) → users(id)
- FOREIGN KEY (dealership_id) → dealerships(id)

---

### email_tracking_events
Email tracking events from Mailgun webhooks.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| sent_email_id | bigint unsigned | FK → sent_emails.id, cascade delete | Associated sent email |
| event_type | varchar(255) | indexed | Event type (delivered, opened, clicked, bounced, complained, unsubscribed) |
| message_id | varchar(255) | indexed | Mailgun message ID |
| recipient_email | varchar(255) | indexed | Recipient email address |
| url | varchar(255) | nullable | URL for click events |
| user_agent | varchar(255) | nullable | User agent string |
| ip_address | varchar(255) | nullable | IP address |
| mailgun_data | json | nullable | Full Mailgun webhook data |
| event_timestamp | timestamp | indexed | When event occurred |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (sent_email_id, event_type)
- INDEX (message_id)
- INDEX (recipient_email)
- INDEX (event_timestamp)
- FOREIGN KEY (sent_email_id) → sent_emails(id) ON DELETE CASCADE

---

### pdf_attachments
PDF file attachments for emails (polymorphic).

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| file_name | varchar(255) | | Original file name |
| file_path | varchar(255) | | Storage path |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)

---

### attachables
Polymorphic pivot table for attaching PDFs to various models.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| pdf_attachment_id | bigint unsigned | FK → pdf_attachments.id | PDF attachment |
| attachable_id | bigint unsigned | | Attachable model ID |
| attachable_type | varchar(255) | | Attachable model type |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (attachable_type, attachable_id)
- FOREIGN KEY (pdf_attachment_id) → pdf_attachments(id)

---

### reminders
Scheduled reminder system for users.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| user_id | bigint unsigned | FK → users.id, constrained | User who owns reminder |
| dev_rel | boolean | nullable | Development-related flag |
| title | varchar(255) | | Reminder title |
| message | text | | Reminder message |
| start_date | date | | When to start reminders |
| last_sent | date | nullable | Last sent date |
| sending_frequency | unsigned integer | | Frequency in days |
| pause | boolean | default: false | Whether paused |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- FOREIGN KEY (user_id) → users(id)

---

## Contact Management Tables

### tags
Tags for categorizing contacts (syncs with Mailcoach).

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| name | varchar(255) | | Tag name |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)

---

### contact_tag
Many-to-many pivot table for contact tags.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| contact_id | bigint unsigned | FK → contacts.id | Contact |
| tag_id | bigint unsigned | FK → tags.id | Tag |

**Indexes:**
- FOREIGN KEY (contact_id) → contacts(id)
- FOREIGN KEY (tag_id) → tags(id)

---

## User & Authentication Tables

### users
System users with role-based permissions.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| name | varchar(255) | | User's full name |
| email | varchar(255) | unique | Email address (login) |
| phone | varchar(255) | nullable | Phone number |
| email_verified_at | timestamp | nullable | Email verification timestamp |
| password | varchar(255) | | Hashed password |
| timezone | varchar(255) | nullable | User's timezone |
| two_factor_secret | text | nullable | 2FA secret |
| two_factor_recovery_codes | text | nullable | 2FA recovery codes |
| two_factor_confirmed_at | timestamp | nullable | 2FA confirmation timestamp |
| remember_token | varchar(100) | nullable | Remember me token |
| current_team_id | bigint unsigned | nullable | Current team (Jetstream) |
| profile_photo_path | varchar(2048) | nullable | Profile photo path |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |
| deleted_at | timestamp | nullable | Soft delete timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)

---

### dealership_user
Many-to-many pivot table for user-dealership assignments.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| dealership_id | bigint unsigned | FK → dealerships.id, constrained | Dealership |
| user_id | bigint unsigned | FK → users.id, constrained | User |

**Indexes:**
- FOREIGN KEY (dealership_id) → dealerships(id)
- FOREIGN KEY (user_id) → users(id)

---

### password_reset_tokens
Password reset token storage.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| email | varchar(255) | PK | User email |
| token | varchar(255) | | Reset token |
| created_at | timestamp | nullable | Creation timestamp |

**Indexes:**
- PRIMARY KEY (email)

---

### personal_access_tokens
Laravel Sanctum API tokens.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| tokenable_type | varchar(255) | | Tokenable model type |
| tokenable_id | bigint unsigned | | Tokenable model ID |
| name | varchar(255) | | Token name |
| token | varchar(64) | unique | Token hash |
| abilities | text | nullable | Token abilities |
| last_used_at | timestamp | nullable | Last usage timestamp |
| expires_at | timestamp | nullable | Expiration timestamp |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (token)
- INDEX (tokenable_type, tokenable_id)

---

### sessions
Session storage.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | varchar(255) | PK | Session ID |
| user_id | bigint unsigned | nullable, indexed | Associated user |
| ip_address | varchar(45) | nullable | IP address |
| user_agent | text | nullable | User agent string |
| payload | longtext | | Session data |
| last_activity | integer | indexed | Last activity timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (last_activity)

---

## Permission System Tables (Spatie)

### permissions
Available permissions in the system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| name | varchar(255) | | Permission name |
| guard_name | varchar(255) | | Guard name |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (name, guard_name)

---

### roles
User roles in the system.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| team_id | bigint unsigned | nullable, indexed | Team ID (if teams enabled) |
| name | varchar(255) | | Role name |
| guard_name | varchar(255) | | Guard name |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (team_id, name, guard_name) OR UNIQUE (name, guard_name)
- INDEX (team_id)

---

### model_has_permissions
Polymorphic table for assigning permissions directly to models.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| permission_id | bigint unsigned | FK → permissions.id, cascade delete | Permission |
| model_type | varchar(255) | | Model type |
| model_id | bigint unsigned | indexed | Model ID |
| team_id | bigint unsigned | nullable, indexed | Team ID (if teams enabled) |

**Indexes:**
- PRIMARY KEY (team_id?, permission_id, model_id, model_type)
- INDEX (model_id, model_type)
- INDEX (team_id)
- FOREIGN KEY (permission_id) → permissions(id) ON DELETE CASCADE

---

### model_has_roles
Polymorphic table for assigning roles to models.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| role_id | bigint unsigned | FK → roles.id, cascade delete | Role |
| model_type | varchar(255) | | Model type |
| model_id | bigint unsigned | indexed | Model ID |
| team_id | bigint unsigned | nullable, indexed | Team ID (if teams enabled) |

**Indexes:**
- PRIMARY KEY (team_id?, role_id, model_id, model_type)
- INDEX (model_id, model_type)
- INDEX (team_id)
- FOREIGN KEY (role_id) → roles(id) ON DELETE CASCADE

---

### role_has_permissions
Pivot table for role-permission relationships.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| permission_id | bigint unsigned | FK → permissions.id, cascade delete | Permission |
| role_id | bigint unsigned | FK → roles.id, cascade delete | Role |

**Indexes:**
- PRIMARY KEY (permission_id, role_id)
- FOREIGN KEY (permission_id) → permissions(id) ON DELETE CASCADE
- FOREIGN KEY (role_id) → roles(id) ON DELETE CASCADE

---

## System/Support Tables

### activity_log
Comprehensive activity logging (Spatie Activity Log).

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| log_name | varchar(255) | nullable, indexed | Log category |
| description | text | | Activity description |
| subject_type | varchar(255) | nullable | Subject model type |
| subject_id | bigint unsigned | nullable | Subject model ID |
| causer_type | varchar(255) | nullable | Causer model type |
| causer_id | bigint unsigned | nullable | Causer model ID |
| properties | json | nullable | Additional properties |
| event | varchar(255) | nullable | Event name |
| batch_uuid | varchar(255) | nullable | Batch UUID |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (log_name)
- INDEX (subject_type, subject_id)
- INDEX (causer_type, causer_id)

---

### notifications
Database-driven notifications.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | char(36) | PK | UUID primary key |
| type | varchar(255) | | Notification class |
| notifiable_type | varchar(255) | indexed | Notifiable model type |
| notifiable_id | bigint unsigned | indexed | Notifiable model ID |
| data | text | | Notification data |
| read_at | timestamp | nullable | Read timestamp |
| created_at | timestamp | | Creation timestamp |
| updated_at | timestamp | | Last update timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (notifiable_type, notifiable_id)

---

### jobs
Queue jobs table.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| queue | varchar(255) | indexed | Queue name |
| payload | longtext | | Job payload |
| attempts | unsigned tinyint | | Number of attempts |
| reserved_at | unsigned integer | nullable | Reserved timestamp |
| available_at | unsigned integer | | Available timestamp |
| created_at | unsigned integer | | Creation timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (queue)

---

### failed_jobs
Failed queue jobs.

| Column | Type | Attributes | Description |
|--------|------|------------|-------------|
| id | bigint unsigned | PK, auto-increment | Primary key |
| uuid | varchar(255) | unique | Job UUID |
| connection | text | | Connection name |
| queue | text | | Queue name |
| payload | longtext | | Job payload |
| exception | longtext | | Exception details |
| failed_at | timestamp | default: CURRENT_TIMESTAMP | Failure timestamp |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (uuid)

---

### telescope_entries
Laravel Telescope debugging entries.

Standard Laravel Telescope schema with entries for requests, commands, jobs, logs, queries, etc.

---

## Entity Relationship Overview

### Core Relationships

1. **Dealerships** → Many **Contacts** (dealership_id)
2. **Dealerships** → Many **Stores** (dealership_id)
3. **Dealerships** → Many **Progresses** (dealership_id)
4. **Dealerships** → Many **DealerEmails** (dealership_id)
5. **Dealerships** → Many **SentEmails** (dealership_id)
6. **Dealerships** ↔ Many **Users** (dealership_user pivot)

### User Relationships

7. **Users** → Many **Dealerships** (creator - user_id)
8. **Users** ↔ Many **Dealerships** (assignees - dealership_user pivot)
9. **Users** → Many **Progresses** (user_id)
10. **Users** → Many **DealerEmails** (user_id)
11. **Users** → Many **Reminders** (user_id)
12. **Users** → Many **Stores** (user_id)

### Email System Relationships

13. **DealerEmailTemplates** → Many **DealerEmails** (dealer_email_template_id)
14. **DealerEmails** → Many **Attachables** (polymorphic)
15. **PdfAttachments** → Many **Attachables** (pdf_attachment_id)
16. **SentEmails** → Many **EmailTrackingEvents** (sent_email_id)

### Contact Relationships

17. **Contacts** ↔ Many **Tags** (contact_tag pivot)
18. **Contacts** → Many **Progresses** (contact_id, nullable)

### Progress Relationships

19. **ProgressCategories** → Many **Progresses** (progress_category_id)

### Permission Relationships

20. **Roles** → Many **Permissions** (role_has_permissions)
21. **Models** (polymorphic) ↔ **Roles** (model_has_roles)
22. **Models** (polymorphic) ↔ **Permissions** (model_has_permissions)

---

## Key Design Patterns

1. **Soft Deletes**: users table
2. **Polymorphic Relations**: attachables, activity_log, notifications, personal_access_tokens
3. **Many-to-Many Pivots**: dealership_user, contact_tag, role_has_permissions, model_has_roles, model_has_permissions
4. **JSON Storage**: dealer_emails.recipients, tracking data, activity properties
5. **Scheduled Operations**: dealer_emails and reminders with frequency, start_date, last_sent, next_send_date
6. **Comprehensive Indexing**: Email tracking, message IDs, relationships
7. **Cascade Deletes**: email_tracking_events → sent_emails

---

## Notes

- All timestamps use Laravel's default `created_at` and `updated_at` columns
- Foreign key constraints are enforced where explicitly defined
- JSON columns are used for flexible data storage (recipients, tracking data, properties)
- The schema supports multi-tenancy through team_id in permission tables
- Email tracking integrates with Mailgun webhooks for delivery, open, click tracking
- Activity logging is comprehensive across all major models

---

## Recommended Schema Improvements (Laravel Best Practices)

### 1. Naming Conventions

**Current Issues:**
- `progresses` - Unconventional plural form
- `dev_rel`, `dev_status` - Abbreviated column names reduce readability
- `attachables` - Pivot table name could be more descriptive

**Recommendations:**
```php
// Consider renaming
progresses → progress_entries (or keep singular "progress" with custom table name)
dev_rel → development_related
dev_status → development_status
attachables → pdf_attachment_attachables (more explicit)
```

### 2. Soft Deletes for Data Integrity

**Current:** Only `users` table has soft deletes

**Recommended:** Add soft deletes to critical business entities:
```php
// Add to migrations
$table->softDeletes();
```

**Tables that should have soft deletes:**
- `dealerships` - Preserve historical data and relationships
- `contacts` - Maintain email history integrity
- `stores` - Keep location history
- `dealer_email_templates` - Don't break existing email references
- `tags` - Prevent orphaned relationships
- `progress_categories` - Maintain categorization history

**Benefits:**
- Preserve referential integrity
- Enable "undo" functionality
- Maintain audit trails
- Prevent cascade deletion issues

### 3. Enum Columns vs String Columns

**Current:** Status, rating, type fields use generic `varchar(255)`

**Recommended:** Use database enums or lookup tables for better data integrity

**Option A: Database Enums (Laravel 10+)**
```php
// In migration
$table->enum('status', ['active', 'inactive', 'pending', 'archived'])->default('active');
$table->enum('rating', ['hot', 'warm', 'cold'])->default('warm');
$table->enum('type', ['automotive', 'rv', 'motorsports', 'maritime', 'association']);
$table->enum('dev_status', ['prospecting', 'demo_scheduled', 'demo_completed', 'negotiating', 'won', 'lost'])->nullable();
```

**Option B: Lookup Tables (More Flexible)**
```php
// Better for user-configurable options
Schema::create('dealership_statuses', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('color')->nullable(); // For UI display
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Then in dealerships table
$table->foreignId('status_id')->constrained('dealership_statuses');
```

**Benefits:**
- Prevents invalid data entry
- Self-documenting schema
- Enables dropdown population from database
- Supports localization

### 4. JSON Column Normalization

**Current:** `dealer_emails.recipients` uses JSON

**Issue:** Difficult to query, index, or maintain referential integrity

**Recommended:** Create proper pivot table
```php
Schema::create('dealer_email_recipients', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dealer_email_id')->constrained()->onDelete('cascade');
    $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null');
    $table->string('email'); // Denormalized for when contact is deleted
    $table->string('name')->nullable();
    $table->enum('type', ['to', 'cc', 'bcc'])->default('to');
    $table->timestamp('sent_at')->nullable();
    $table->timestamps();

    $table->index(['dealer_email_id', 'email']);
});
```

**Benefits:**
- Query recipients efficiently
- Track individual recipient status
- Maintain contact relationship
- Support CC/BCC functionality

### 5. Missing Indexes for Query Performance

**Recommended Indexes:**

```php
// dealerships table
$table->index('status'); // Frequently filtered
$table->index('rating'); // Frequently filtered
$table->index('type'); // Frequently filtered
$table->index('in_development'); // Boolean filters are fast
$table->index(['state', 'city']); // Geographic queries
$table->index('created_at'); // Sorting by date

// contacts table
$table->index(['dealership_id', 'primary_contact']); // Find primary contacts
$table->index('email'); // Email lookups

// progresses table
$table->index('date'); // Date range queries
$table->index(['dealership_id', 'date']); // Activity timeline
$table->index('created_at'); // Recent activity

// dealer_emails table
$table->index('next_send_date'); // Scheduled email queries
$table->index(['paused', 'next_send_date']); // Active email queue
$table->index('last_sent'); // Reporting

// stores table
$table->index(['dealership_id', 'state']); // Location queries
```

### 6. Foreign Key Constraints Consistency

**Current:** Some foreign keys lack explicit constraints or cascade rules

**Recommended:** Explicitly define all foreign key behavior

```php
// Good examples (consistent approach)
$table->foreignId('dealership_id')
    ->constrained()
    ->onUpdate('cascade')
    ->onDelete('restrict'); // Prevent orphaned records

$table->foreignId('user_id')
    ->constrained()
    ->onUpdate('cascade')
    ->onDelete('cascade'); // Remove dependent records

// For nullable foreign keys
$table->foreignId('contact_id')
    ->nullable()
    ->constrained()
    ->onUpdate('cascade')
    ->onDelete('set null'); // Preserve record but clear reference
```

**Recommended Cascade Rules:**

| Relationship | On Delete | Reasoning |
|-------------|-----------|-----------|
| dealerships → contacts | CASCADE | Contacts belong to dealership |
| dealerships → stores | CASCADE | Stores belong to dealership |
| dealerships → progresses | RESTRICT | Prevent accidental data loss |
| dealerships → dealer_emails | RESTRICT | Preserve email history |
| users → progresses | RESTRICT | Maintain audit trail |
| dealer_email_templates → dealer_emails | RESTRICT | Don't break email references |
| sent_emails → email_tracking_events | CASCADE | Tracking is dependent data |

### 7. Pivot Table Timestamps

**Current:** `dealership_user` and `contact_tag` lack timestamps

**Recommended:** Add timestamps to track relationship changes

```php
Schema::create('dealership_user', function (Blueprint $table) {
    $table->foreignId('dealership_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->timestamps(); // Track when assignment was made

    $table->primary(['dealership_id', 'user_id']);
});

Schema::create('contact_tag', function (Blueprint $table) {
    $table->foreignId('contact_id')->constrained();
    $table->foreignId('tag_id')->constrained();
    $table->timestamps(); // Track when tag was added

    $table->primary(['contact_id', 'tag_id']);
});
```

**Benefits:**
- Audit trail for assignments
- "Tagged on" / "Assigned on" display in UI
- Historical reporting

### 8. Missing Unique Constraints

**Recommended Unique Constraints:**

```php
// tags table - prevent duplicate tags
$table->unique('name');

// contacts table - prevent duplicate emails per dealership
$table->unique(['dealership_id', 'email']);

// progress_categories table - prevent duplicate categories
$table->unique('name');

// dealer_email_templates table - prevent duplicate template names
$table->unique('name');

// dealership_user pivot - already should be composite primary
$table->primary(['dealership_id', 'user_id']);
```

### 9. Column Length Optimization

**Current:** Many `varchar(255)` columns that don't need that much space

**Recommended:** Right-size varchar columns for better performance

```php
// More appropriate lengths
$table->string('state', 2); // US state codes
$table->string('zip_code', 10); // US ZIP codes
$table->string('phone', 20); // Phone numbers
$table->string('email', 191); // Email addresses (MySQL index limit)
$table->string('rating', 20); // 'hot', 'warm', 'cold'
$table->string('status', 50); // Status values
```

**Benefits:**
- Reduced storage size
- Faster indexing
- Better memory usage
- Implicit validation

### 10. Additional Useful Columns

**Recommended Additions:**

```php
// dealerships table
$table->timestamp('last_contact_at')->nullable(); // Quick filter for stale leads
$table->timestamp('converted_at')->nullable(); // Track conversion date
$table->string('source', 100)->nullable(); // Lead source tracking
$table->decimal('lifetime_value', 10, 2)->nullable(); // Business metrics

// contacts table
$table->string('mobile_phone', 20)->nullable(); // Separate mobile
$table->string('direct_line', 20)->nullable(); // Direct line
$table->boolean('email_bounced')->default(false); // Email deliverability
$table->boolean('unsubscribed')->default(false); // Marketing preferences
$table->timestamp('last_contacted_at')->nullable(); // Last outreach

// dealer_emails table
$table->integer('total_sent')->default(0); // Track send count
$table->timestamp('completed_at')->nullable(); // Campaign completion

// progresses table
$table->enum('outcome', ['positive', 'neutral', 'negative'])->nullable(); // Outcome tracking
$table->boolean('follow_up_required')->default(false); // Action flag
$table->timestamp('follow_up_at')->nullable(); // Scheduled follow-up
```

### 11. Database-Level Defaults

**Current:** Some defaults only in application code

**Recommended:** Set defaults at database level for data integrity

```php
// Examples
$table->boolean('paused')->default(false);
$table->boolean('in_development')->default(false);
$table->boolean('primary_contact')->default(false);
$table->integer('total_sent')->default(0);
$table->string('status')->default('active');
$table->string('rating')->default('warm');
```

**Benefits:**
- Data consistency even with direct DB access
- Self-documenting schema
- Protection against null values

### 12. Polymorphic Column Optimization

**Current:** Polymorphic `*_type` columns use default `varchar(255)`

**Recommended:** Explicitly set appropriate length

```php
// In attachables table
$table->string('attachable_type', 100); // Fully qualified class names rarely exceed 100
$table->morphs('attachable'); // Or use this helper which sets appropriate lengths

// Add index for better performance
$table->index(['attachable_type', 'attachable_id']);
```

### 13. UUID Considerations

**Current:** Uses auto-incrementing integers

**Consider:** UUIDs for public-facing identifiers

```php
// For security and distributed systems
Schema::create('dealerships', function (Blueprint $table) {
    $table->id(); // Internal ID
    $table->uuid('uuid')->unique(); // Public identifier
    // ... rest of columns
});

// Or use UUIDs as primary keys (Laravel 9+)
Schema::create('dealerships', function (Blueprint $table) {
    $table->uuid('id')->primary();
    // ... rest of columns
});
```

**Benefits:**
- Non-sequential IDs (security)
- Distributed system compatibility
- Offline record creation
- Prevents enumeration attacks

### 14. Full-Text Search Optimization

**Recommended:** Add full-text indexes for search functionality

```php
// dealerships table
DB::statement('ALTER TABLE dealerships ADD FULLTEXT search(name, address, city, notes)');

// contacts table
DB::statement('ALTER TABLE contacts ADD FULLTEXT search(name, email, position)');

// progresses table
DB::statement('ALTER TABLE progresses ADD FULLTEXT search(details)');
```

**Usage:**
```php
// In queries
Dealership::whereRaw('MATCH(name, address, city, notes) AGAINST(?)', [$searchTerm])
    ->get();
```

### 15. Partitioning Considerations

**For Large Tables:** Consider partitioning by date for performance

```php
// email_tracking_events - could grow very large
// Partition by month
ALTER TABLE email_tracking_events
PARTITION BY RANGE (YEAR(event_timestamp) * 100 + MONTH(event_timestamp)) (
    PARTITION p202401 VALUES LESS THAN (202402),
    PARTITION p202402 VALUES LESS THAN (202403),
    // ... etc
);
```

**Benefits:**
- Faster queries on recent data
- Easier archival of old data
- Better maintenance

---

## Summary Priority Recommendations

### High Priority (Immediate Benefits)
1. ✅ Add soft deletes to core tables (dealerships, contacts, stores)
2. ✅ Add missing indexes for frequently queried columns
3. ✅ Define explicit foreign key cascade rules
4. ✅ Add unique constraints where appropriate
5. ✅ Use enums for status/rating/type fields

### Medium Priority (Quality of Life)
6. ✅ Add timestamps to pivot tables
7. ✅ Normalize JSON recipients column
8. ✅ Right-size varchar column lengths
9. ✅ Add database-level defaults
10. ✅ Rename abbreviated columns

### Low Priority (Nice to Have)
11. ✅ Add UUID columns for public identifiers
12. ✅ Add full-text search indexes
13. ✅ Consider table partitioning for large tables
14. ✅ Add business metric columns (lifetime_value, last_contact_at, etc.)

### Migration Strategy

When implementing these changes:

1. **Create new migrations** - Don't modify existing ones
2. **Test thoroughly** - Especially foreign key constraints
3. **Backup data** - Before adding constraints
4. **Deploy incrementally** - One improvement at a time
5. **Monitor performance** - Verify indexes improve queries
6. **Update models** - Sync Eloquent models with schema changes

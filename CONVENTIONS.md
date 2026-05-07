# Hotel Booking & Management System — Project Conventions

## 1. Project Overview

A full-featured **Hotel Booking & Management System** built with Laravel. The system handles:

- **User Management** — registration, authentication, profiles, and role-based access control
- **Role & Permission Management** — granular permissions for admin, staff, and guest roles
- **Room Management** — room types, amenities, pricing, availability, and media uploads
- **Booking Management** — reservations with date selection, guest info, and status workflows
- **Check-In / Check-Out (User Side)** — self-service or front-desk-assisted guest flow

---

## 2. Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Framework | Laravel | 13.x |
| PHP | PHP | 8.3+ |
| Frontend | Livewire + Flux | Livewire 4.x, Flux 2.x |
| Styling | Tailwind CSS | 4.x (via `@tailwindcss/vite`) |
| Build Tool | Vite | 8.x |
| Database | SQLite (dev) / MySQL (prod) | — |
| Auth | Laravel Fortify | 1.x |
| Media | Spatie Media Library | 11.x |
| Activity Log | Spatie Activity Log | 4.x |
| Testing | Pest | 4.x |
| Code Style | Laravel Pint | 1.x |

---

## 3. Architecture & Directory Structure

The project follows a **Service–Repository** pattern on top of Laravel's standard structure.

```
app/
├── Actions/           # Single-purpose action classes (e.g. Fortify auth actions)
│   └── Fortify/       # CreateNewUser, ResetUserPassword
├── Concerns/          # Shared validation rule traits (PasswordValidationRules, etc.)
├── Console/
│   └── Commands/      # Custom Artisan commands (MakeLivewireCommand scaffold)
├── Enums/             # PHP 8.1 backed enums (BookingStatus, RoomStatus, etc.)
├── Http/
│   └── Controllers/   # Traditional HTTP controllers (minimal — prefer Livewire)
├── Livewire/          # Livewire full-page components
│   ├── Actions/       # Shared Livewire actions (Logout)
│   ├── Bookings/      # Index, Form, Detail
│   ├── Settings/      # Profile, Security, Appearance, DeleteUserForm
│   └── <Module>/      # Future modules follow the same pattern
├── Models/            # Eloquent models (extend BaseModel)
├── Providers/         # Service providers
├── Repositories/      # Query/read logic — one per model
├── Rules/             # Validation rule classes — one per model
├── Services/          # Write/business logic — one per model
└── Trait/             # Reusable traits (HasPagination, HasRunningNo, etc.)

database/
├── factories/         # Model factories for testing
├── migrations/        # Chronological migration files
└── seeders/           # Database seeders

resources/views/
├── components/        # Blade components
├── flux/              # Flux UI overrides
├── layouts/           # App layout templates
├── livewire/          # Livewire component views (mirrors Livewire/ structure)
│   ├── auth/
│   ├── bookings/      # index.blade.php, form.blade.php, detail.blade.php
│   └── settings/
└── partials/          # Shared blade partials

routes/
├── web.php            # Primary routes (Livewire full-page components)
├── settings.php       # Settings-related routes
└── console.php        # Console/scheduled routes

stub/livewire/         # Code-generation stubs for the MakeLivewire command
├── class/             # PHP class stubs (index, form, detail, wizard)
├── view/              # Blade view stubs
├── enum.stub
├── migration.stub
├── model.stub
├── repository.stub
├── routes.stub
├── rules.stub
└── service.stub
```

---

## 4. Coding Conventions

### 4.1 General PHP

- **PSR-12** via Laravel Pint (`pint.json` config).
- Run `composer lint` before committing.
- Use **strict types** and **typed properties/parameters/returns** everywhere.
- Use **PHP 8.1+ enums** for any fixed set of values (statuses, types).

### 4.2 Naming Conventions

| Item | Convention | Example |
|---|---|---|
| Model | Singular PascalCase | `Room`, `Booking`, `User` |
| Migration | `create_<table>_table` | `create_rooms_table` |
| Enum | PascalCase + `Status`/`Type` suffix | `BookingStatus`, `RoomType` |
| Service | `<Model>Service` | `BookingService`, `RoomService` |
| Repository | `<Model>Repository` | `BookingRepository` |
| Rules | `<Model>Rules` | `BookingRules`, `RoomRules` |
| Livewire Component | `App\Livewire\<Module>\<Action>` | `App\Livewire\Rooms\Index` |
| Livewire View | `livewire.<module>.<action>` | `livewire.rooms.index` |
| Route name | `<module>.<action>` | `rooms.index`, `bookings.form` |
| Trait | PascalCase verb phrase | `HasPagination`, `HasRunningNo` |
| Database columns | snake_case | `check_in_date`, `room_type_id` |

### 4.3 Models

- All domain models **extend `BaseModel`** (not `Model` directly).
- `BaseModel` provides: media handling, activity logging, date conversion helpers, running number generation.
- Use `$fillable` arrays explicitly.
- Use `casts()` method for enums, dates, and JSON columns.
- Use `$loggableAttributes` to define which fields are tracked by the activity log.

```php
class Room extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'room_number', 'room_type_id', 'status', 'price_per_night'];

    protected array $loggableAttributes = ['name', 'status', 'price_per_night'];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'price_per_night' => 'decimal:2',
        ];
    }
}
```

### 4.4 Enums

- Use PHP backed string enums.
- Always include `color()` and `label()` methods for UI rendering.

```php
enum RoomStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case MAINTENANCE = 'maintenance';

    public function color(): string { /* ... */ }
    public function label(): string { /* ... */ }
}
```

### 4.5 Repositories (Read Layer)

- One repository per model.
- Contains **only query/read logic** — no writes.
- Standard methods: `getPaginatedData()`, `getStatusCount()`, `findWithDetails()`.
- Use `when()` for conditional filtering.

### 4.6 Services (Write Layer)

- One service per model.
- Contains **write/business logic**: `save()`, `delete()`, `updateStatus()`.
- Wrap multi-step writes in `DB::beginTransaction()`.
- Use `prepareData()` to normalize form input before persistence.

### 4.7 Rules (Validation)

- One rules class per model in `App\Rules\`.
- Expose `createRules()` and `updateRules(int $id)` as static methods.
- Called from Livewire Form components via `$this->validate()`.

### 4.8 Livewire Components

Each module has **three standard Livewire components**:

| Component | Purpose | Route Pattern |
|---|---|---|
| `Index` | Listing with filters, tabs, pagination, status actions, delete | `GET /<module>` |
| `Form` | Create & Edit (shared, `$id` param differentiates) | `GET /<module>/form/{id?}` |
| `Detail` | Read-only detail view | `GET /<module>/detail/{id}` |

**Component conventions:**

- Inject Repository and Service via `boot()` method.
- Use `AuthorizesRequests` trait; guard actions with `$this->authorize('<module>.<action>')`.
- Use `HasPagination` trait for listing pages.
- Dispatch `show-toast` events for success/error feedback.
- Use `navigate: true` on redirects for SPA-like navigation.

### 4.9 Routes

- Group by module with `prefix()` and `name()`.
- Livewire full-page components are registered directly as route targets.

```php
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/', RoomsIndex::class)->name('index');
    Route::get('/form/{id?}', RoomsForm::class)->name('form');
    Route::get('/detail/{id}', RoomsDetail::class)->name('detail');
});
```

### 4.10 Code Generation

Use the custom `MakeLivewireCommand` Artisan command to scaffold new modules:

```bash
php artisan make:livewire <ModuleName>
```

This generates all boilerplate files from stubs: Model, Enum, Migration, Service, Repository, Rules, Livewire components (Index/Form/Detail), Blade views, and route definitions.

---

## 5. Database Design

### 5.1 Common Column Conventions

Every table should include:

| Column | Type | Notes |
|---|---|---|
| `id` | `bigint` (auto-increment) | Primary key |
| `status` | `string` | Default `'draft'`, cast to enum |
| `created_by` | `foreignId` (nullable) | FK → `users.id`, `nullOnDelete()` |
| `updated_by` | `foreignId` (nullable) | FK → `users.id`, `nullOnDelete()` |
| `created_at` | `timestamp` | Via `$table->timestamps()` |
| `updated_at` | `timestamp` | Via `$table->timestamps()` |
| `deleted_at` | `timestamp` (nullable) | Via `$table->softDeletes()` |

### 5.2 Core Tables

```
users
├── id
├── name
├── email (unique)
├── email_verified_at
├── password
├── role_id (FK → roles.id)          ← TO ADD
├── phone                            ← TO ADD
├── remember_token
└── timestamps

roles                                ← NEW
├── id
├── name (e.g. admin, receptionist, guest)
├── code (running number: RL-0001)
├── permissions (JSON)
└── timestamps, soft_deletes

rooms                                ← NEW
├── id
├── room_number (unique)
├── name
├── room_type_id (FK → room_types.id)
├── floor
├── price_per_night (decimal 10,2)
├── max_occupancy
├── description (text)
├── status (available / occupied / maintenance / out_of_order)
├── created_by, updated_by
└── timestamps, soft_deletes

room_types                           ← NEW
├── id
├── name (e.g. Standard, Deluxe, Suite, Presidential)
├── description
├── base_price (decimal 10,2)
├── status
└── timestamps, soft_deletes

bookings
├── id
├── booking_code (running number: BK-0001)   ← TO ADD
├── user_id (FK → users.id)                  ← TO ADD
├── room_id (FK → rooms.id)                  ← TO ADD
├── name (guest name)
├── check_in_date (date)                     ← TO ADD
├── check_out_date (date)                    ← TO ADD
├── actual_check_in (datetime, nullable)     ← TO ADD
├── actual_check_out (datetime, nullable)    ← TO ADD
├── num_guests (integer)                     ← TO ADD
├── special_requests (text, nullable)        ← TO ADD
├── total_price (decimal 10,2)               ← TO ADD
├── status (draft / confirmed / checked_in / checked_out / cancelled)
├── created_by, updated_by
└── timestamps, soft_deletes
```

### 5.3 Date Handling

- Store dates in `Y-m-d` format in the database.
- Display dates in `d-m-Y` format in the UI.
- Use `BaseModel::convertDateForDatabase()` and `convertDateForDisplay()` helpers.

---

## 6. Module Specifications

### 6.1 User Management (Admin Side)

**Permissions:** `user.read`, `user.create`, `user.update`, `user.delete`

| Feature | Description |
|---|---|
| List Users | Paginated table with search (name/email), role filter, status tabs |
| Create/Edit User | Form: name, email, password, role assignment, phone |
| View User Detail | Profile info, assigned role, booking history |
| Activate/Deactivate | Status toggle with activity logging |
| Delete | Soft delete with confirmation modal |

### 6.2 Role & Permission Management

**Permissions:** `role.read`, `role.create`, `role.update`, `role.delete`

| Feature | Description |
|---|---|
| List Roles | Paginated table, status tabs |
| Create/Edit Role | Name, code (auto-generated RL-XXXX), permission checkboxes |
| View Role Detail | Assigned permissions, users in this role |

**Default Roles:**

| Role | Key Permissions |
|---|---|
| `admin` | All permissions |
| `receptionist` | booking.*, room.read, user.read, checkin.*, checkout.* |
| `guest` | booking.create (own), booking.read (own), checkin.self, checkout.self |

### 6.3 Room Management

**Permissions:** `room.read`, `room.create`, `room.update`, `room.delete`

| Feature | Description |
|---|---|
| List Rooms | Grid/table view, filter by type/status/floor, availability calendar |
| Create/Edit Room | Room number, name, type, floor, price, max occupancy, description, photos (Spatie Media) |
| View Room Detail | Full info, current booking status, upcoming reservations, photo gallery |
| Status Management | Available → Occupied → Maintenance → Out of Order |

### 6.4 Booking Management

**Permissions:** `booking.read`, `booking.create`, `booking.update`, `booking.delete`

**Status Workflow:**

```
Draft → Confirmed → Checked In → Checked Out
                  ↘ Cancelled
```

| Feature | Description |
|---|---|
| List Bookings | Paginated table, status tabs (All/Draft/Confirmed/Checked In/Checked Out/Cancelled), search by guest name or booking code |
| Create Booking | Select room (with availability check), guest info, check-in/out dates, number of guests, special requests, auto-calculate total price |
| Edit Booking | Update details (only while Draft or Confirmed) |
| View Booking Detail | Full reservation info, room details, guest info, activity log timeline |
| Cancel Booking | With remarks, logs activity |
| Delete Booking | Soft delete, admin only |

### 6.5 Check-In / Check-Out (User Side)

| Feature | Description |
|---|---|
| My Bookings | Guest sees their own bookings (filtered by `user_id`) |
| Self Check-In | Guest confirms arrival, sets `actual_check_in` timestamp, status → `checked_in` |
| Self Check-Out | Guest confirms departure, sets `actual_check_out` timestamp, status → `checked_out` |
| Booking Summary | Post-checkout summary with stay duration and total charges |

---

## 7. Enum Definitions

### BookingStatus

```php
enum BookingStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case CANCELLED = 'cancelled';
}
```

### RoomStatus

```php
enum RoomStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case MAINTENANCE = 'maintenance';
    case OUT_OF_ORDER = 'out_of_order';
}
```

### RoomTypeStatus

```php
enum RoomTypeStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case DEACTIVATED = 'deactivated';
}
```

### UserStatus

```php
enum UserStatus: string
{
    case ACTIVE = 'active';
    case DEACTIVATED = 'deactivated';
}
```

---

## 8. Running Number Formats

| Source | Format | Example |
|---|---|---|
| `user` | `US-XXXX` | `US-0001` |
| `role` | `RL-XXXX` | `RL-0001` |
| `booking` | `BK-XXXX` | `BK-0001` |
| `room` | `RM-XXXX` | `RM-0001` |

Register new sources in `HasRunningNo::generateRunningNo()`.

---

## 9. Permission Naming Convention

Format: `<module>.<action>`

```
user.read       user.create       user.update       user.delete
role.read       role.create       role.update       role.delete
room.read       room.create       room.update       room.delete
booking.read    booking.create    booking.update    booking.delete
checkin.manage  checkout.manage
checkin.self    checkout.self
```

---

## 10. Development Workflow

### 10.1 Starting the Dev Server

```bash
composer dev
# Runs concurrently: php artisan serve, queue:listen, npm run dev (Vite)
```

### 10.2 Creating a New Module

```bash
php artisan make:livewire <ModuleName>
```

Then manually:
1. Register routes in `routes/web.php`
2. Add permissions to the role seeder
3. Add navigation link to the sidebar layout

### 10.3 Running Tests

```bash
composer test
# Runs: config:clear → pint --test → php artisan test (Pest)
```

### 10.4 Code Formatting

```bash
composer lint          # Auto-fix
composer lint:check    # Check only (CI)
```

### 10.5 Migrations

```bash
php artisan migrate              # Run pending migrations
php artisan migrate:fresh --seed # Reset & seed (dev only)
```

---

## 11. Git Conventions

### Branch Naming

```
feature/<module>-<description>    → feature/rooms-crud
bugfix/<module>-<description>     → bugfix/booking-date-validation
hotfix/<description>              → hotfix/login-redirect
```

### Commit Messages

```
feat(rooms): add room CRUD with media upload
fix(booking): correct date overlap validation
refactor(services): extract price calculation
chore(deps): update livewire to 4.2
```

---

## 12. Environment Configuration

Key `.env` variables:

```env
APP_NAME="Hotel Booking System"
DB_CONNECTION=sqlite          # Use mysql for production
SESSION_DRIVER=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local         # Use s3 for production media storage
```

---

## 13. Media Handling

- Use **Spatie Media Library** for all file uploads (room photos, guest documents).
- All models inheriting `BaseModel` automatically support media collections.
- Default thumbnail conversion: 640px width, JPG format.
- Use collection names to organize: `'room_photos'`, `'guest_documents'`.
- Helper methods available: `getFirstMediaUrlInCollection()`, `getAllMediaUrlInCollection()`, `getMediaSummary()`.

---

## 14. Activity Logging

- All models inheriting `BaseModel` automatically log create/update/delete events via Spatie Activity Log.
- Define `$loggableAttributes` on each model to control which fields are tracked.
- Use `$model->logActivity($event, $description, $properties)` for custom events (e.g., check-in, check-out, status changes).
- Only dirty (changed) attributes are logged; empty logs are suppressed.

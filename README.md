# Laravel ZKTeco ADMS

A production-ready Laravel package for integrating **ZKTeco biometric devices** using the **ADMS (Push SDK)** protocol.

This package is extracted from a real-world production attendance system and preserves the original Laravel implementation while making it reusable across multiple Laravel applications.

---

# Features

- 🚀 ADMS (Push SDK) Integration
- 📡 Real-time Attendance Synchronization
- 👥 User Synchronization
- 🖐 Fingerprint Template Synchronization
- 😀 Face Template Synchronization
- 💾 Biometric Data Management
- 🖥 Device Registration & Heartbeat
- 📥 Device Command Queue
- 🔄 Command Response Tracking
- ⏱ Automatic Attendance Synchronization
- 🏭 Multi-device Support
- ⚡ Laravel 11 & Laravel 12 Compatible

---

# Requirements

## RequirementVersion

- PHP8.2+
  =Laravel11.x +

# Installation

## 1. Install Package

```
composer require rithy/zkteco-adms
```

---

## 2. Publish Configuration

```
php artisan vendor:publish --tag=zkteco-adms-config
```

---

## 3. Publish Database Migrations

```
php artisan vendor:publish --tag=zkteco-adms-migrations
```

---

## 4. Publish Views

```
php artisan vendor:publish --tag=zkteco-adms-views
```

---

## 5. Run Migrations

```
php artisan migrate
```

---

## 6. Configure Scheduler (Optional)

The package provides real-time attendance ingestion from device callbacks.

The scheduler is optional, but recommended to ensure attendance sync stays consistent.
Use it as a safety net for cases like temporary network issues, delayed device pushes, or missed callbacks.

The package provides the following Artisan command:

```
php artisan zkteco:sync-attendance
```

Register it in your application's `routes/console.php`:

```
use Illuminate\Support\Facades\Schedule;

Schedule::command('zkteco:sync-attendance')
    ->everyThirtyMinutes()
    ->name('sync-attendance')
    ->withoutOverlapping();
```

Finally, ensure Laravel's scheduler is running by adding the standard cron entry on your server:

```
* * * * * cd /path/to/your/application && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Configure CSRF Exclusion

Since ZKTeco devices cannot generate Laravel CSRF tokens, exclude the ADMS endpoint in `bootstrap/app.php`:

```
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'iclock/*',
    ]);
})
```

---

# Configuration

After publishing, edit:

```
config/zkteco-adms.php
```

Example:

```
return [

    'routes' => [

        'enabled' => true,

        'middleware' => [
            'web',
        ],

    ],

];
```

---

# Routes

The package automatically registers these endpoints:

MethodEndpointDescriptionGET`/iclock/cdata`Device HandshakePOST`/iclock/cdata`Attendance UploadGET`/iclock/getrequest`Device PollingPOST`/iclock/devicecmd`Command ResponseVerify:

```
php artisan route:list | grep iclock
```

---

# Database Tables

The package creates the following tables:

- `zk_devices`
- `zk_users`
- `zk_biodatas`
- `zk_attendances`
- `zk_commands`

---

# Supported Data

## Attendance

- PIN
- Timestamp
- Verify Type
- Status

## Users

- User ID
- Name
- Privilege
- Password
- Card Number

## Biometrics

- Fingerprint Templates
- Face Templates

## Device Commands

- Attendance Sync
- User Sync
- Biometric Sync
- Restart Device
- Refresh Data
- Delete User
- Add User

---

# Package Structure

```
rithy-zkteco-adms/
├── config/
├── database/
│   └── migrations/
├── resources/
├── routes/
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       └── SyncZktecoAttendanceCommand.php
│   ├── Contracts/
│   ├── Events/
│   ├── Exceptions/
│   ├── Facades/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Jobs/
│   ├── Models/
│   ├── Services/
│   ├── Support/
│   └── ZktecoAdmsServiceProvider.php
├── tests/
├── composer.json
└── README.md
```

---

# Typical Data Flow

```
ZKTeco Device
      │
      │ ADMS Push
      ▼
Laravel ZKTeco ADMS Package
      │
      ├── Register Device
      ├── Receive Attendance
      ├── Receive Users
      ├── Receive Biometrics
      ├── Queue Commands
      └── Process Responses
      │
      ▼
Your Laravel Application
```

---

# Multi-device Support

Supports unlimited ZKTeco devices.

Example:

```
Head Office
├── Device 1
├── Device 2
└── Device 3

Factory A
├── Device 4
├── Device 5
└── Device 6
```

Each device is uniquely identified by its Serial Number (SN).

---

# Production Deployment Checklist

- Enable HTTPS.
- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Configure the scheduler cron.
- Configure queue workers if applicable.
- Exclude `iclock/*` from CSRF validation.
- Cache configuration and routes.
  Optimize the application:

```
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan queue:restart
```

---

# Contributing

Contributions are welcome.

1. Fork the repository.
2. Create a feature branch.
3. Commit your changes.
4. Open a Pull Request.

---

# License

MIT License

---

# Author

**Rithy Sam**

Laravel Developer

Email: rithysam.sr@gmail.com

Cambodia 🇰🇭

**# Acknowledgements**

Inspired by the work on the adms-server-ZKTeco project:

[https://github.com/saifulcoder/adms-server-ZKTeco/tree/main](https://github.com/saifulcoder/adms-server-ZKTeco/tree/main)

Special thanks to the Laravel community and to ZKTeco for the ADMS Push SDK protocol.

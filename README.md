# Reminder Web Application

## 📢 Brief Overview

A comprehensive web application for managing reminders and tasks with advanced notification features. The app supports multi-channel notifications (Email, SMS, WhatsApp), recurring reminders, categories, notification logs, and is fully localized for Arabic RTL interfaces.

### Key Features
- **User Authentication & Profile Management** (with Arabic RTL and email verification)
- **Reminder & Task Management** (CRUD, frequency options, advance notice, multi-channel notifications, snooze, duplicate, recurring reminders)
- **Dashboard** (quick stats, reminders overview, recent activity)
- **Multiple Views** (List, Calendar, optional Kanban)
- **Category Management** (system & custom categories, color/icon)
- **Notification System** (Email, SMS, WhatsApp, with templates)
- **Notification Logs** (view, filter, resend)
- **Background Scheduler** (for notifications & recurring reminders)
- **Settings** (user preferences, global notification, integrations)
- **Comprehensive Arabic RTL Layout & Translations**

## 🚀 Installation Guide

### 1. Requirements
- PHP >= 8.2
- Composer
- Node.js >= 18.x & npm
- MySQL >= 8.0
- Redis

### 2. Clone the Repository
```bash
git clone https://github.com/your-username/reminder.git
cd reminder
```

### 3. Install Backend Dependencies
```bash
composer install
```

### 4. Install Frontend Dependencies
```bash
npm install
```

### 5. Environment Setup
- Copy `.env.example` to `.env`
```bash
cp .env.example .env
```
- Set your database, Redis, email (SMTP), and Twilio/SMS config in the `.env` file (see below):
  - `QUEUE_CONNECTION=redis`
  - `DB_CONNECTION=mysql`
  - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
  - `TWILIO_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_PHONE_NUMBER`, `TWILIO_WHATSAPP_NUMBER`

### 6. Database Migration & Seed
```bash
php artisan migrate --seed
```

### 7. Build & Run Frontend
```bash
npm run build     # for production
# OR
npm run dev       # for development
```

### 8. Run Application
- Start queue and scheduler:
```bash
php artisan queue:work
php artisan schedule:work
```
- Serve the app:
```bash
php artisan serve
```

### 9. Access
- Visit: [http://localhost:8000](http://localhost:8000)

----

## 🔔 Notification Channels
- **Email**: Configurable via SMTP in `.env`.
- **SMS & WhatsApp**: Requires [Twilio](https://www.twilio.com/) credentials.

## 📆 Recurring Reminders
The app supports reminders with custom repetition logic (Daily, Weekly, Monthly, Yearly, or Custom intervals).

## 🌐 Languages & RTL
- Default language: **Arabic** (`RTL` layout).
- All layouts, components, and forms are localized in Arabic.

## 📝 Additional Notes
- **Searchable selects** and **Date & time pickers** are used for all relevant forms (fully support Arabic localization).
- Kanban view feature is planned/optional and not enabled by default.
- Notification logs let you monitor, filter, and resend failed notifications.


## 🦉 Credits
- Built with [Laravel](https://laravel.com/), [Vue 3](https://vuejs.org/), [Inertia.js](https://inertiajs.com/), [Tailwind CSS](https://tailwindcss.com/), and [@vuepic/vue-datepicker](https://vuepic.com/datepicker/).

## 📄 License
This project is open-source and available under the [MIT license](LICENSE).


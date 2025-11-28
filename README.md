# UCLMVenueReservation — Quick start (SQLite)

This repository is a small PHP app that uses an SQLite database file (`database.sqlite`) in the project root. The instructions below show how to set up the database, seed initial data, run the app locally (development-only), and inspect the SQLite file on Windows PowerShell.

## Prerequisites

- PHP 7+ installed and on your PATH.
- PDO_SQLITE / sqlite3 PHP extensions enabled (common in standard PHP builds).
- (Optional) DB Browser for SQLite for a GUI: https://sqlitebrowser.org/

Quick checks (PowerShell):

```powershell
php -v
php -m | Select-String -Pattern "sqlite"
```

You should see `pdo_sqlite` or `sqlite3` listed.

## Project root and DB path

The app opens the SQLite file at:

`<project-root>/database.sqlite`

In your case, the full path is typically:

`C:\Users\Chrystal\OneDrive\Desktop\Venue Reservation\UCLMVenueReservation\database.sqlite`

## Initialize the database and seed data (PowerShell)

Open PowerShell and run from the project root:

```powershell
cd "C:\Users\Chrystal\OneDrive\Desktop\Venue Reservation\UCLMVenueReservation"

# Create database, tables, and seed default data
php setup_database.php

# Optional: seed buildings and venues
php seed_venues.php
```

Expected messages:
- `Database tables created and seeded successfully.` (from `setup_database.php`)
- `Buildings seeded.` and `Venues seeded successfully.` (from `seed_venues.php`)

### Default admin user

`setup_database.php` seeds a default admin user:
- Username: `admin`
- Password: `admin123`

Change this password after first login in a production environment.

## Run the app (development)

You can run PHP's built-in web server from the project root for development:

```powershell
php -S localhost:8000 -t .
```

Then open your browser to: `http://localhost:8000`

If you prefer a local Apache+PHP stack, place the project in your webserver document root (e.g., `htdocs` in XAMPP) and ensure `config.php` can open the `database.sqlite` file.

## Inspecting the SQLite database

CLI (if `sqlite3` is installed):

```powershell
sqlite3 "C:\Users\Chrystal\OneDrive\Desktop\Venue Reservation\UCLMVenueReservation\database.sqlite"
# inside sqlite3 prompt
.tables
SELECT * FROM User;
.exit
```

GUI:
- Open `database.sqlite` with DB Browser for SQLite. Use Browse Data / Execute SQL to view tables and rows.

## Common tasks & notes

- Recreate the database from scratch:

```powershell
Remove-Item -Path ".\database.sqlite" -Force
php setup_database.php
php seed_venues.php
```

- File locking: OneDrive can sometimes lock files while syncing. If you see file lock errors when the app writes to `database.sqlite`, try moving the project to a non-synced folder (e.g., `C:\projects`) while developing.

- If you get PDO errors about missing drivers, enable `extension=pdo_sqlite` and/or `extension=sqlite3` in your `php.ini`, or install a PHP build with SQLite enabled.

## Troubleshooting

- Permission/file lock errors: check OneDrive, anti-virus, or Windows file permissions.
- Routes returning 404 when using the built-in server: use `-t .` to set document root; for more complex routing use Apache or adjust app routing accordingly.

---

If you'd like, I can also:
- Add a tiny `README` badge or sample screenshots.
- Run `php -m` and `php setup_database.php` here to confirm the environment (tell me if you want me to run these commands now).


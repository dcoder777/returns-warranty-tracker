# Return & Warranty Tracker

A simple PHP + MySQL web app to log product returns, track warranty expiry status, and search records by serial number or invoice.

## Features

- Log returns with product details, reason, return date, and warranty expiry date.
- Automatically classify warranty status as **Active**, **Expiring Soon** (30 days), or **Expired**.
- Search records by serial number or invoice number.
- Responsive UI with mobile-friendly form layout and scrollable table.
- Secure input handling via server-side validation, output escaping, and prepared statements.

## Setup

1. Create the database/table:
   ```bash
   mysql -u root -p < schema.sql
   ```
2. Set database credentials via environment variables (optional defaults are in `db.php`):
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
3. Start PHP dev server:
   ```bash
   php -S 0.0.0.0:8000
   ```
4. Open `http://localhost:8000`

## Security notes

- All inserts/selects use prepared statements.
- User-rendered values are escaped with `htmlspecialchars`.
- Input length and date format are validated on the server.

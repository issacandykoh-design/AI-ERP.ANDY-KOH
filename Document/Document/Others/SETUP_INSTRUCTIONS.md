# Craveva Server Setup Instructions

## Prerequisites

This Laravel application requires:
- **PHP 8.1 or higher**
- **Composer** (PHP dependency manager)
- **MySQL/MariaDB** (for database)

## Quick Start

### Option 1: Using XAMPP (Recommended for Windows)

1. **Download and Install XAMPP**
   - Visit: https://www.apachefriends.org/download.html
   - Download XAMPP for Windows (PHP 8.1+)
   - Install to `C:\xampp`

2. **Add PHP to PATH** (Optional but recommended)
   - Add `C:\xampp\php` to your system PATH
   - Or use the provided batch file which will find it automatically

3. **Start the Server**
   - Double-click `start_server.bat` in the project root
   - Or run: `C:\xampp\php\php.exe artisan serve`

### Option 2: Using Laragon (Lightweight Alternative)

1. **Download and Install Laragon**
   - Visit: https://laragon.org/download/
   - Install Laragon (includes PHP, MySQL, Composer)

2. **Start Laragon**
   - Laragon will automatically start Apache/MySQL
   - Point Laragon to this project directory

3. **Start the Server**
   - Use Laragon's terminal or run: `php artisan serve`

### Option 3: Using WAMP

1. **Download and Install WAMP**
   - Visit: https://www.wampserver.com/en/download-wampserver-64bits/
   - Install WAMP Server

2. **Start the Server**
   - Update `start_server.bat` with your WAMP PHP path
   - Or add PHP to PATH

## Setup Steps

### 1. Install Composer Dependencies

```bash
composer install
```

If composer is not installed:
- Download from: https://getcomposer.org/download/
- Or use: `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
- Then: `php composer-setup.php`

### 2. Configure Environment

The `.env` file should already exist. If not:
```bash
copy .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=craveva
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations (if needed)

```bash
php artisan migrate
```

### 5. Start the Development Server

**Using the batch file:**
```bash
start_server.bat
```

**Or manually:**
```bash
php artisan serve
```

The server will start at: **http://localhost:8000**

## Landing Page

The landing page is automatically served at the root URL (`/`):
- **Main landing**: http://localhost:8000/
- **Web version**: http://localhost:8000/?view=web
- **Mobile version**: http://localhost:8000/?view=mobile

The landing page files are located in `public/landing/` and are served statically.

## Troubleshooting

### PHP Not Found
- Make sure PHP 8.1+ is installed
- Add PHP to your system PATH
- Or update `start_server.bat` with your PHP path

### Composer Dependencies Missing
```bash
composer install
```

### Database Connection Error
- Check your `.env` database settings
- Make sure MySQL/MariaDB is running
- Verify database exists

### Port Already in Use
If port 8000 is busy, use a different port:
```bash
php artisan serve --port=8001
```

## Project Structure

- `public/landing/` - Landing page static files
- `routes/web.php` - Main web routes (line 133 serves landing page)
- `app/` - Laravel application code
- `resources/` - Views and assets
- `config/` - Configuration files

## Additional Notes

- The landing page is a pre-built static site served from `public/landing/index.html`
- It automatically detects mobile vs desktop and loads appropriate bundles
- All CTA buttons are intercepted and routed to signup/WhatsApp/email links
- The "AI Function" link is automatically injected into navigation


# Server Troubleshooting Guide

## If Connection Failed

### Quick Fix
1. **Check if server is running:**
   ```powershell
   netstat -ano | findstr ":8000"
   ```

2. **Start the server:**
   - Double-click: `start_server_final.bat`
   - Or run: `C:\xampp\php\php.exe artisan serve`

3. **Check for errors:**
   - Look at the command window where server is running
   - Check for PHP errors or database connection issues

### Common Issues

#### 1. Port 8000 Already in Use
**Solution:** Use a different port
```bash
php artisan serve --port=8001
```

#### 2. Database Connection Error
**Solution:** Check `.env` file database settings
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=craveva
DB_USERNAME=root
DB_PASSWORD=
```

#### 3. PHP Not Found
**Solution:** Make sure XAMPP PHP is available
```bash
C:\xampp\php\php.exe --version
```

#### 4. Missing Dependencies
**Solution:** Run composer install
```bash
composer install
```

#### 5. Cache Issues
**Solution:** Clear Laravel caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Check Server Logs
The server window will show errors. Common issues:
- Database connection failed
- Missing .env file
- Permission errors
- Missing PHP extensions

### Restart Server
1. Close the server window (Ctrl+C)
2. Run `start_server_final.bat` again
3. Wait 3-5 seconds for server to start
4. Try accessing http://localhost:8000


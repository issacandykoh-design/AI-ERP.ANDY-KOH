# Quick Start Guide

## 🚀 **Starting Your Local Server**

### ⚠️ **IMPORTANT: Use Port 8000!**

**XAMPP Apache is running on port 80**, so Laravel uses **port 8000** to avoid conflicts.

### **Option 1: Double-Click (Easiest)**

1. **Double-click:** `START_HERE.bat` (or `start_localhost.bat`)
2. Server starts automatically on port 8000
3. Visit: **`http://localhost:8000`** ← Use port 8000!

### **Option 2: XAMPP Users (Quick Start)**

1. **Double-click:** `start_localhost_xampp.bat`
2. Server starts automatically
3. Visit: **`http://localhost:8000`** ← Use port 8000!

### ❌ **Don't Use:**
- `http://localhost` ← This shows XAMPP dashboard
- Use `http://localhost:8000` instead!

### **Option 3: Manual Start**

Open terminal in project folder and run:
```bash
php artisan serve
```

Then visit: `http://localhost:8000`

## 🎯 **Accessing the Application**

### **Login Page**
- **Main App:** `http://localhost:8000` ← Landing page
- **Login:** `http://localhost:8000/login` ← Regular login
- **Superadmin Login:** `http://localhost:8000/super-admin-login` ← Super admin login

### ⚠️ **Important:**
- **Use port 8000:** `http://localhost:8000`
- **NOT port 80:** `http://localhost` (shows XAMPP page)

### **Super Admin Panel**
After login, go to:
- **Front Settings → Theme Settings** - Manage all frontend configurations

## ⚙️ **Managing Frontend**

**Super Admin → Front Settings → Theme Settings**

All frontend settings are managed here:
- ✅ Theme selection (Theme 1, 2, or 3)
- ✅ Primary color
- ✅ Login theme
- ✅ Homepage setup
- ✅ Background settings

**No file editing needed!** Everything is saved to database.

## 🔧 **Troubleshooting**

### **Server Won't Start?**
1. Make sure PHP is installed (XAMPP/WAMP)
2. Check if port 8000 is available
3. Try: `php artisan serve --port=8080`

### **Can't Access Localhost?**
1. Make sure server is running (check terminal)
2. Try: `http://127.0.0.1:8000`
3. Check firewall settings

### **Database Connection Error?**
1. Check `.env` file has correct database settings
2. Make sure MySQL is running (if using XAMPP/WAMP)
3. Verify database exists

## 📝 **Next Steps**

1. ✅ Start server using `start_localhost.bat`
2. ✅ Visit `http://localhost:8000/login`
3. ✅ Login to Super Admin
4. ✅ Go to **Front Settings → Theme Settings**
5. ✅ Configure your frontend

---

**That's it!** Everything is managed through the admin panel. 🎉


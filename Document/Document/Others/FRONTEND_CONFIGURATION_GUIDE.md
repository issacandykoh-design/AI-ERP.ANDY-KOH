# Frontend Configuration Guide

## 🎨 Managing Frontend Settings

Since you're using the ERP system platform, all frontend configurations are managed through the **Super Admin Panel**. No manual file editing is required.

## 📍 **Where to Configure**

**Super Admin → Front Settings → Theme Settings**

This is your **single source of truth** for all frontend configurations.

## ⚙️ **Available Settings**

### **1. Theme Selection**
- **Theme 1**: Classic single-page design
- **Theme 2**: Multi-page design with modern UI
- **Theme 3**: Modern React landing page

**How to Change:**
1. Go to Theme Settings
2. Select your preferred theme from dropdown
3. Click "Update"

### **2. Primary Color**
- Sets your brand color
- Applied to Theme 2 and Theme 3
- Automatically injected as CSS variables

**How to Change:**
1. Click the color picker
2. Select your brand color
3. Click "Update"

### **3. Login Theme**
- Choose Theme 1 or Theme 2 for login page
- Only available when Theme 2 or Theme 3 is selected

### **4. Homepage Setup**
- **Default Landing**: Shows your landing page
- **Signup**: Redirects to signup page
- **Login**: Redirects to login page
- **Custom URL**: Redirects to external URL

### **5. Homepage Background**
- **Default**: Uses default background
- **Color**: Set custom background color
- **Image**: Upload background image
- **Image + Color**: Combine both

### **6. Default Language**
- Sets the default language for frontend
- Affects all frontend content

### **7. Disable Frontend**
- When enabled, all frontend routes redirect to login
- Useful for maintenance or internal-only access

## 🔄 **How Changes Work**

1. **All settings are saved to database** (`global_settings` and `front_details` tables)
2. **Changes take effect immediately** after clicking "Update"
3. **Cache is automatically cleared** when settings are saved
4. **No file editing required** - everything is managed through the admin panel

## 📝 **Best Practices**

1. **Always use the admin panel** - Don't edit files manually
2. **Test changes** - Preview before going live
3. **Backup before major changes** - Export database if needed
4. **Use Theme 3 for modern look** - Best for marketing/landing pages
5. **Keep primary color consistent** - Matches your brand

## 🛠️ **Artisan Commands (Optional)**

For advanced users, these commands are available:

```bash
# Set Theme 3 (if needed)
php artisan theme3:setup

# Verify Theme 3 configuration
php artisan theme3:verify
```

**Note:** These are optional. The admin panel is the recommended way to manage settings.

## ❓ **Troubleshooting**

### **Settings Not Saving?**
- Check browser console for errors
- Clear browser cache (Ctrl+F5)
- Run: `php artisan optimize:clear`

### **Changes Not Showing?**
- Hard refresh browser (Ctrl+F5)
- Clear Laravel cache: `php artisan cache:clear`
- Check if frontend is disabled

### **Theme 3 Not Loading?**
- Verify landing page exists: `public/landing/index.html`
- Check homepage setting is "Default Landing"
- Ensure frontend is not disabled

## 📞 **Support**

All frontend configurations are managed through:
**Super Admin → Front Settings → Theme Settings**

No manual file editing needed! 🎉

---

**Last Updated:** 2025-11-22


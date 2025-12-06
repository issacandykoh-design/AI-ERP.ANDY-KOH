# How to Use Laravel Boost in Cursor

## ✅ Setup Complete!

Laravel Boost is already installed and configured for your project. The MCP server runs automatically when Cursor is open, so you're ready to start using it!

## 🚀 How to Use Laravel Boost in Cursor

### 1. **Just Start Chatting!**

Laravel Boost works automatically in the background. When you use Cursor's AI chat (Composer), Boost provides enhanced context about your Laravel application.

**Example prompts you can use:**

```
"Create a UserController with index and show methods following Laravel conventions"
```

```
"Show me all routes that use the 'auth' middleware"
```

```
"Generate a migration to add a 'status' column to the users table"
```

```
"Create a Form Request class for user registration with validation rules"
```

### 2. **What Boost Gives You Automatically**

When you ask questions or request code, Boost automatically provides:

- ✅ **Application Context**: Knows your Laravel version (10.x), PHP version (8.2.12), and installed packages
- ✅ **Database Schema**: Can inspect your database structure
- ✅ **Route Information**: Can analyze your routes
- ✅ **Laravel Best Practices**: Follows Laravel conventions automatically
- ✅ **Version-Specific Documentation**: Uses correct APIs for Laravel 10

### 3. **Boost's 15 Specialized Tools**

Boost provides these tools automatically when needed:

1. **Application Info** - Reads PHP & Laravel versions, packages, models
2. **Database Schema** - Inspects your complete database structure
3. **Database Queries** - Executes queries directly
4. **Route Inspector** - Analyzes your application's routes
5. **Artisan Commands** - Lists available Artisan commands
6. **Tinker Integration** - Executes code in your Laravel context
7. **Configuration Access** - Gets configuration values
8. **Documentation Search** - Queries Laravel docs specific to your versions
9. **Error Tracking** - Reads application logs and browser errors
10. **Model Analysis** - Understands your Eloquent models
11. **Service Provider Info** - Knows your registered providers
12. **Middleware Analysis** - Understands your middleware stack
13. **Event & Listener Info** - Knows your events and listeners
14. **Observer Analysis** - Understands your model observers
15. **Package Information** - Knows your installed Composer packages

### 4. **Practical Examples**

#### Example 1: Creating a Controller
**You ask:**
> "Create a ProductController with CRUD operations"

**Boost automatically:**
- Uses Laravel 10 conventions
- Follows your project's structure
- Uses Form Requests for validation
- Returns proper responses
- Uses Eloquent relationships correctly

#### Example 2: Database Queries
**You ask:**
> "Show me all users who registered in the last month"

**Boost can:**
- Query your database directly
- Use proper Eloquent methods
- Follow your model relationships
- Use Carbon for date handling

#### Example 3: Route Analysis
**You ask:**
> "What routes are using the 'admin' middleware?"

**Boost can:**
- Inspect your routes file
- Analyze middleware usage
- Show route groups and patterns

#### Example 4: Error Debugging
**You ask:**
> "Why am I getting a 500 error on the login page?"

**Boost can:**
- Read your Laravel logs
- Check browser console errors
- Analyze the error stack trace
- Suggest fixes based on your codebase

### 5. **Best Practices for Using Boost**

1. **Be Specific**: The more context you give, the better Boost can help
   - ✅ Good: "Create a UserController that lists active users ordered by name"
   - ❌ Less helpful: "Make a controller"

2. **Reference Your Code**: Mention existing files when relevant
   - ✅ Good: "Create a similar controller to UserController but for Products"
   - ❌ Less helpful: "Create a controller"

3. **Ask About Your App**: Boost knows your specific setup
   - ✅ Good: "How do I add authentication using Fortify?"
   - ✅ Good: "Show me how Cashier is configured in this app"

4. **Use Laravel Terminology**: Boost understands Laravel concepts
   - ✅ Good: "Create a Form Request for user registration"
   - ✅ Good: "Add a scope to the User model for active users"

### 6. **Verifying Boost is Working**

You can verify Boost is active by asking:

```
"What Laravel version is this project using?"
```

```
"What packages are installed in this project?"
```

```
"Show me the database schema for the users table"
```

If Boost is working, you'll get accurate, project-specific answers!

### 7. **Updating Boost Guidelines**

To update to the latest guidelines:

```bash
php artisan boost:update
```

### 8. **Troubleshooting**

**If Boost doesn't seem to be working:**

1. **Restart Cursor**: Close and reopen Cursor to ensure MCP server connects
2. **Check boost.json**: Should exist in your project root
3. **Verify Installation**: Run `php artisan list | grep boost` to see available commands
4. **Check MCP Server**: The server should start automatically, but you can manually start it with `php artisan boost:mcp`

### 9. **What Makes Boost Special**

Unlike generic AI assistants, Boost:

- ✅ **Knows YOUR codebase**: Understands your specific models, routes, and structure
- ✅ **Follows YOUR conventions**: Matches your existing code style
- ✅ **Uses YOUR versions**: Generates code compatible with Laravel 10, PHP 8.2
- ✅ **Respects YOUR packages**: Knows what packages you have installed
- ✅ **Follows Laravel best practices**: Always generates Laravel-idiomatic code

## 🎯 Quick Start Checklist

- [x] Laravel Boost installed
- [x] MCP server configured
- [x] Guidelines updated
- [ ] **Start using it!** Just chat with Cursor's AI and Boost will enhance responses automatically

## 💡 Pro Tips

1. **Ask for explanations**: "Explain how this route works" - Boost can analyze your actual routes
2. **Request refactoring**: "Refactor this controller to use Form Requests" - Boost knows your structure
3. **Debug with context**: "Why is this query slow?" - Boost can analyze your database schema
4. **Learn Laravel**: "Show me how to use Eloquent relationships" - Boost provides version-specific examples

---

**You're all set!** Just start chatting with Cursor's AI assistant, and Laravel Boost will automatically enhance every interaction with deep knowledge of your Laravel application. 🚀


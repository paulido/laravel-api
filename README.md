# Laravel Auth API (Laravel 11 + Swagger)

**Laravel Auth API** is a full-featured authentication API built with **Laravel 11** and **Laravel Breeze**.  
It includes user registration, login, password reset, and automatically generated API documentation using **Swagger** (via L5 Swagger).

This project is a great starting point for building secure RESTful APIs with authentication in Laravel.

## 🔐 Features

-   ✅ User Registration
-   ✅ Login with email and password
-   ✅ Logout
-   ✅ Password reset request
-   ✅ Send password reset link via email
-   ✅ User listing endpoint
-   ✅ Auto-generated Swagger documentation

---

## 🚀 Technologies Used

-   [Laravel 11](https://laravel.com/)
-   [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze)
-   [L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger)
-   RESTful API with JSON responses

---

## ⚙️ Getting Started

1. **Configure your database in `.env`**

2. **Install dependencies**
```bash
composer install
```
3. Generate the Swagger documentation

```bash
php artisan l5-swagger:generate
```
4. Start the Laravel development server
```bash
php artisan serve
```
📘 Access the Swagger UI
Visit: http://localhost:8000/api/documentation

This page contains all available API endpoints with request/response examples.

🤝 Contributing

Contributions are welcome!
Feel free to submit pull requests or open issues for improvements or bug fixes.

🛡️ License

This project is open-source and available under the MIT license.

🏷️ Tags

- `laravel`
- `laravel11`
- `auth`
- `api`
- `swagger`
- `breeze`
- `authentication`
- `rest-api`
- `laravel-auth`


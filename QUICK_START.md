# 🚀 Quick Start Guide

## One-Command Setup (after cloning)

### Prerequisites Check
```bash
# Check if you have the required versions
node --version    # Should be 18.0.0+
php --version     # Should be 8.1+
composer --version
mysql --version   # or mariadb --version
```

### 1. Backend Setup (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup
```bash
# Create database (run in MySQL)
CREATE DATABASE student_tracker;

# Update .env with your database credentials
# Then run:
php artisan migrate:fresh --seed
```

### 3. Frontend Setup (Vue.js)
```bash
cd ../frontend
npm install
# or: pnpm install (faster)
```

### 4. Start Both Servers
```bash
# Terminal 1 (Backend)
cd backend
php artisan serve

# Terminal 2 (Frontend)
cd frontend
npm run dev
```

## 🌐 Access URLs

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api

## 🔑 Test Accounts

| Role    | Email                | Password   |
|---------|---------------------|------------|
| Admin   | admin@school.com    | admin123   |
| Teacher | teacher1@school.com | teacher123 |
| Student | student1@school.com | student123 |

## 📦 Package Managers

### Recommended: pnpm (faster than npm)
```bash
# Install pnpm globally
npm install -g pnpm

# Use pnpm instead of npm
cd frontend
pnpm install
pnpm dev
```

### Alternative: npm
```bash
cd frontend
npm install
npm run dev
```

## 🔧 Development Scripts

### Backend (Laravel)
```bash
php artisan serve           # Start server
php artisan migrate:fresh   # Reset database
php artisan db:seed         # Add sample data
php artisan cache:clear     # Clear cache
```

### Frontend (Vue.js)
```bash
npm run dev       # Development server
npm run build     # Production build
npm run preview   # Preview build
```

## ⚡ Performance Tips

1. **Use pnpm instead of npm** for faster installs
2. **Enable MySQL query cache** in production
3. **Use Laravel's built-in cache** for better performance
4. **Optimize Vite build** with proper chunking

## 🐛 Quick Fixes

### Database Issues
```bash
php artisan migrate:fresh --seed
```

### Frontend Issues
```bash
rm -rf node_modules package-lock.json
npm install
```

### Permission Issues (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

### Clear Everything
```bash
# Backend
php artisan config:clear && php artisan cache:clear

# Frontend
rm -rf node_modules && npm install
```

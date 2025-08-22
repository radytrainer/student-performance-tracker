# Student Performance Tracker - Setup Guide

## 📋 Prerequisites

Before setting up the project, ensure you have the following installed:

### System Requirements
- **Node.js** (v18.0.0 or higher) - [Download here](https://nodejs.org/)
- **npm** (comes with Node.js) or **pnpm** (recommended for better performance)
- **PHP** (v8.1 or higher) - [Download here](https://www.php.net/downloads)
- **Composer** (PHP dependency manager) - [Download here](https://getcomposer.org/download/)
- **MySQL** (v8.0 or higher) or **MariaDB** (v10.4 or higher)
- **Git** - [Download here](https://git-scm.com/downloads)

### Optional but Recommended
- **phpMyAdmin** or **MySQL Workbench** for database management
- **Visual Studio Code** with PHP and Vue.js extensions

## 🚀 Installation Steps

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/student-performance-tracker.git
cd student-performance-tracker
```

### 2. Backend Setup (Laravel API)

#### Navigate to backend directory
```bash
cd backend
```

#### Install PHP dependencies
```bash
composer install
```

#### Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### Configure Database
Edit the `.env` file and update the database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Create Database
Create a MySQL database named `student_tracker`:
```sql
CREATE DATABASE student_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Run Database Migrations and Seeders
```bash
# Run migrations to create tables
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Or run both at once
php artisan migrate:fresh --seed
```

#### Start Backend Server
```bash
php artisan serve
```
The API will be available at `http://localhost:8000`

### 3. Frontend Setup (Vue.js Application)

#### Navigate to frontend directory (in a new terminal)
```bash
cd frontend
```

#### Install Node.js dependencies
```bash
# Using npm
npm install

# Or using pnpm (recommended for better performance)
pnpm install
```

#### Start Development Server
```bash
# Using npm
npm run dev

# Or using pnpm
pnpm dev
```
The frontend will be available at `http://localhost:3000`

## 🔐 Default Login Credentials

After running the seeders, you can use these accounts:

### Admin Account
- **Email**: `admin@school.com`
- **Password**: `admin123`

### Teacher Accounts
- **Email**: `teacher1@school.com`
- **Password**: `teacher123`
- **Email**: `teacher2@school.com`
- **Password**: `teacher123`

### Student Accounts
- **Email**: `student1@school.com`
- **Password**: `student123`
- **Email**: `student2@school.com`
- **Password**: `student123`

## 📁 Project Structure

```
student-performance-tracker/
├── frontend/                 # Vue.js frontend application
│   ├── src/
│   │   ├── components/      # Reusable Vue components
│   │   ├── views/          # Page components
│   │   ├── stores/         # Pinia state management
│   │   ├── api/            # API service files
│   │   ├── composables/    # Vue composables
│   │   └── router/         # Vue Router configuration
│   ├── public/             # Static assets
│   └── package.json        # Frontend dependencies
├── backend/                # Laravel backend API
│   ├── app/               # Laravel application code
│   ├── database/          # Migrations, seeders, factories
│   ├── routes/            # API routes
│   ├── config/            # Configuration files
│   └── composer.json      # Backend dependencies
└── README.md
```

## 🛠️ Development Commands

### Backend Commands
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Create new migration
php artisan make:migration create_table_name

# Create new seeder
php artisan make:seeder TableNameSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run tests
php artisan test
```

### Frontend Commands
```bash
# Start development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Install new package
npm install package-name
# or
pnpm add package-name
```

## 🔧 Environment Variables

### Backend (.env)
Make sure these are configured in your `backend/.env` file:
```env
APP_NAME="Student Performance Tracker"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password

FRONTEND_URL=http://localhost:3000
```

### Frontend
The frontend automatically connects to the backend at `http://localhost:8000`. If you need to change this, update the API base URL in `frontend/src/api/axiosConfig.js`.

## 🗄️ Database Schema

The application includes these main tables:
- **users** - Authentication and basic user info
- **students** - Student-specific data
- **teachers** - Teacher-specific data
- **classes** - Class management
- **subjects** - Subject definitions
- **grades** - Student grades and assessments
- **attendances** - Daily attendance tracking
- **performance_alerts** - Automated performance alerts
- **student_feedback** - Student feedback on teachers
- **teacher_feedback** - Teacher feedback to students

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Error
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists

#### Composer Install Fails
- Check PHP version (needs 8.1+)
- Run `composer install --ignore-platform-reqs` if platform requirements fail

#### Frontend Build Errors
- Delete `node_modules` and `package-lock.json`
- Run `npm install` again
- Check Node.js version (needs 18+)

#### Permission Errors (Laravel)
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
```

#### Clear All Caches
```bash
# Backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Frontend
rm -rf node_modules package-lock.json
npm install
```

## 📱 Production Deployment

## Google Sheets Integration

1. Create OAuth 2.0 Client (Web) in Google Cloud Console
   - Authorized redirect URI: http://localhost:8000/api/google/oauth/callback
   - Authorized JavaScript origins: http://localhost:3000, http://localhost:8000

2. Add to backend/.env
```
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/google/oauth/callback
FRONTEND_URL=http://localhost:3000
APP_URL=http://localhost:8000
```

3. Backend install and cache clear
```
cd backend
composer install
php artisan config:clear && php artisan cache:clear
php artisan migrate
php artisan serve
```

4. Use in app
- Admin/Teacher: go to Data Import -> Connect Google -> Preview -> Import

## PDF Reports
- Installed barryvdh/laravel-dompdf for server-side PDF generation
- After `composer install`, run `php artisan storage:link` to serve PDFs saved to storage/app/public/reports

### Backend Deployment
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`

### Frontend Deployment
1. Run `npm run build`
2. Deploy the `dist/` folder to your web server
3. Configure server to serve `index.html` for all routes (SPA mode)

## 🤝 Contributing

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make your changes
3. Commit: `git commit -m "Add your feature"`
4. Push: `git push origin feature/your-feature`
5. Create a Pull Request

## 📝 Additional Notes

- The application uses **Laravel Sanctum** for API authentication
- **Vue Router** handles frontend routing with role-based guards
- **Pinia** manages global state (user authentication, etc.)
- **Tailwind CSS** provides styling
- **Chart.js** powers the analytics dashboard
- **FullCalendar** manages schedule displays

## 🆘 Need Help?

If you encounter any issues:
1. Check this setup guide
2. Review the troubleshooting section
3. Check Laravel and Vue.js documentation
4. Create an issue in the GitHub repository

---

**Happy Coding! 🎉**

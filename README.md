# 🎓 Student Performance Tracker

A comprehensive Laravel-based student performance tracking system with role-based access, analytics dashboard, grade management, attendance tracking, and feedback systems.

## ✨ Features

### 🔐 Role-Based System
- **Admin**: User management, system settings, comprehensive reports
- **Teacher**: Class management, grade entry, attendance tracking, student feedback
- **Student**: View grades, attendance, reports, submit teacher feedback

### 📊 Performance Analytics
- Interactive dashboard with real-time charts
- Grade trend analysis and performance alerts
- Attendance tracking with calendar views
- Comprehensive reporting system

### 🎯 Key Capabilities
- **Grade Management**: Multiple assessment types (exams, quizzes, assignments, projects)
- **Attendance System**: Daily tracking with status options (present, absent, late, excused)
- **Feedback System**: Bidirectional feedback between students and teachers
- **Data Import/Export**: CSV/Excel support for bulk operations
- **Real-time Notifications**: Performance alerts and system notifications
- **PDF Reports**: Exportable report cards and analytics

## 🛠️ Tech Stack

| Frontend | Backend | Database | Tools |
|----------|---------|----------|-------|
| Vue 3 | Laravel 10 | MySQL 8.0+ | Vite |
| Pinia (State) | Sanctum (Auth) | | Tailwind CSS |
| Vue Router | RESTful API | | Chart.js |
| Axios | Eloquent ORM | | FullCalendar |

## 📁 Project Structure

```
student-performance-tracker/
├── frontend/                 # Vue.js SPA
│   ├── src/
│   │   ├── components/      # Reusable components
│   │   ├── views/           # Page components (Admin/Teacher/Student)
│   │   ├── stores/          # Pinia state management
│   │   ├── api/             # API service layer
│   │   ├── composables/     # Vue composition functions
│   │   └── router/          # Route definitions & guards
│   └── package.json
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/# API controllers
│   │   ├── Models/          # Eloquent models
│   │   └── Middleware/      # Auth & CORS middleware
│   ├── database/
│   │   ├── migrations/      # Database schema
│   │   └── seeders/         # Sample data
│   ├── routes/api.php       # API routes
│   └── composer.json
├── SETUP_GUIDE.md           # Detailed setup instructions
├── QUICK_START.md           # Quick reference
└── README.md
```

## 🚀 Quick Start

### Prerequisites
- **Node.js** 18.0.0+
- **PHP** 8.1+
- **Composer**
- **MySQL** 8.0+

### Installation
```bash
# 1. Clone repository
git clone https://github.com/your-username/student-performance-tracker.git
cd student-performance-tracker

# 2. Backend setup
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# 3. Frontend setup
cd ../frontend
npm install  # or pnpm install

# 4. Start development servers
# Terminal 1: Backend
cd backend && php artisan serve

# Terminal 2: Frontend  
cd frontend && npm run dev
```

### Access Points
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000

### Test Accounts
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@school.com | admin123 |
| Teacher | teacher1@school.com | teacher123 |
| Student | student1@school.com | student123 |

## 📚 Documentation

- 📖 **[Complete Setup Guide](SETUP_GUIDE.md)** - Detailed installation instructions
- ⚡ **[Quick Start Guide](QUICK_START.md)** - Fast setup reference
- 🔐 **[backend/ROLE_BASED_ACCESS_CONTROL.md](backend/ROLE_BASED_ACCESS_CONTROL.md)** - Permission system
- 🗄️ **[backend/AGENT.md](backend/AGENT.md)** - Database schema & development notes

## 🎯 Development

### Commands
```bash
# Backend (Laravel)
php artisan serve              # Start development server
php artisan migrate:fresh      # Reset database  
php artisan db:seed           # Add sample data
php artisan test              # Run tests

# Frontend (Vue.js)  
npm run dev                   # Start development server
npm run build                 # Build for production
npm run preview               # Preview production build
```

### Google Sheets & PDFs
- Google Sheets import for Admin/Teacher: connect, preview, and import students
- Server-side PDF generation for Student Reports (storage/app/public/reports)

#### Import from Google Sheets (quick steps)
1. Configure backend `.env` with `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI` (see backend/config/services.php). Restart backend.
2. In the app, go to Admin → Data Import or Teacher → Data Import, click “Connect Google,” complete consent.
3. Paste a Google Sheet URL or ID. Optionally set Sheet name and Range, click Preview to confirm headers.
4. Select a Default Class and click “Import from Google.”

Required headers: `first_name`, `last_name`, `email`, `date_of_birth`, `gender` (optional: `address`, `parent_name`, `parent_phone`, `class_id`).

Sample template: docs/student_import_template.csv

### Key Features Implemented
- ✅ **Authentication & Authorization**: Role-based access with Laravel Sanctum
- ✅ **Grade Management**: CRUD operations for student grades
- ✅ **Attendance Tracking**: Daily attendance with calendar integration
- ✅ **Performance Analytics**: Real-time charts and dashboards
- ✅ **User Management**: Admin panel for managing users
- ✅ **Feedback System**: Student-teacher feedback mechanism
- ✅ **Data Export**: PDF and Excel export capabilities
- ✅ **Responsive Design**: Mobile-friendly interface

## 🔧 Environment Setup

### Required Environment Variables

**Backend (.env)**:
```env
APP_NAME="Student Performance Tracker"
DB_CONNECTION=mysql
DB_DATABASE=student_tracker
DB_USERNAME=your_username
DB_PASSWORD=your_password
FRONTEND_URL=http://localhost:3000
```

**Frontend**: Automatically configured to connect to `http://localhost:8000`

## 🐛 Troubleshooting

### Common Issues
- **Database connection error**: Verify MySQL is running and credentials are correct
- **Composer install fails**: Ensure PHP 8.1+ is installed
- **Frontend build errors**: Delete `node_modules` and reinstall

### Quick Fixes
```bash
# Reset database
php artisan migrate:fresh --seed

# Clear Laravel cache
php artisan cache:clear && php artisan config:clear

# Reinstall frontend dependencies  
rm -rf node_modules package-lock.json && npm install
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🌟 Acknowledgments

- Laravel framework for robust backend API
- Vue.js for reactive frontend
- Chart.js for beautiful data visualizations
- Tailwind CSS for modern styling
- FullCalendar for schedule management

---

**Built with ❤️ for educational institutions**

For detailed setup instructions, see [SETUP_GUIDE.md](SETUP_GUIDE.md)

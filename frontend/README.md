# Frontend

Vue 3 + Vite SPA for Student Performance Tracker.

## API Base URL
Set in `.env`:
```
VITE_API_BASE_URL=http://localhost:8000/api
```

## Google Sheets Import (UI)
- Teacher: Reports → Data Import
- Admin: Admin → Data Import

Steps:
1) Click “Connect Google” (OAuth via backend), complete consent.
2) Paste Google Sheet URL or ID. Optionally set Sheet name and Range and click Preview.
3) Select Default Class and click “Import from Google”.

Required headers: `first_name`, `last_name`, `email`, `date_of_birth`, `gender` (optional: `address`, `parent_name`, `parent_phone`, `class_id`).

Sample template: docs/student_import_template.csv

## PDF Reports

Generate student reports as server-side PDFs and download them from the app.

Steps (My Reports page):
1) Open the Reports/My Reports view.
2) Pick Report Type, Time Period, and select Format = PDF.
3) Click Generate. Your browser downloads the PDF (blob). The entry appears in Recent Reports.
4) Later, you can re-download any completed report via the Download action.

Notes:
- The UI for this flow lives in [TeacherReports.vue](file:///c:/Users/Dell/Desktop/student-performance-tracker/frontend/src/views/teacher/TeacherReports.vue).
- API calls are in [reports.js](file:///c:/Users/Dell/Desktop/student-performance-tracker/frontend/src/api/reports.js).
- The backend stores PDFs under storage/app/public/reports so Recent Reports can serve them later.

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

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

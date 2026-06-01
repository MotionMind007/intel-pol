# Political Intelligence Platform MVP

MVP ini mengikuti PRD: React frontend, Laravel 11 backend, FastAPI AI service, PostgreSQL, Redis, Docker Compose, dan Nginx reverse proxy.

## Fitur Awal

- Login token berbasis Laravel Sanctum.
- Role: Super Admin, Admin, Analyst, Viewer.
- Landing page modul dengan satu modul aktif: Screening Tokoh.
- Generate screening tokoh dari React ke Laravel, lalu Laravel memanggil FastAPI internal endpoint.
- FastAPI mengembalikan JSON laporan 12 bagian.
- Hasil tersimpan di `screening_reports` dan dirender sebagai laporan satu kolom.
- Agent Settings hanya muncul dan bisa diakses oleh Super Admin.
- API key provider disimpan encrypted dan hanya dikembalikan sebagai masked value.

## Akun Demo

- Super Admin: `superadmin@example.com` / `password`
- Analyst: `analyst@example.com` / `password`

## Local Development

Terminal 1:

```bash
cd ai-service
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
$env:INTERNAL_SERVICE_TOKEN="local-dev-token"
uvicorn app.main:app --reload --port 8000
```

Terminal 2:

```bash
cd backend
php artisan migrate:fresh --seed
php artisan serve --port=8001
```

Terminal 3:

```bash
cd backend
php artisan queue:work database --queue=screening --timeout=0
```

Terminal 4:

```bash
cd frontend
npm run dev
```

Buka URL Vite yang muncul, biasanya `http://localhost:5173`.

## Docker

Generate `APP_KEY`, lalu isi root `.env`.

```bash
cd backend
php artisan key:generate --show
```

```bash
docker compose up --build
docker compose exec backend php artisan migrate:fresh --seed
```

Buka `http://localhost:8080`.

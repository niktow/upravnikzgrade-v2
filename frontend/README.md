# Frontend (React + Vite)

## Purpose
This folder contains the new React SPA that consumes Laravel API from `../api`.

## Local setup
1. Copy `.env.example` to `.env`.
2. Set `VITE_API_BASE_URL` (example: `https://api.example.rs/api/v1`).
3. Install dependencies:
   - `npm install`
4. Start dev server:
   - `npm run dev`

## Build
- `npm run build`
- Upload `dist/` to your static web root.

## Notes
- React Router is configured with SPA fallback expectation (`index.html`).
- API requests are centralized in `src/lib/api.ts`.

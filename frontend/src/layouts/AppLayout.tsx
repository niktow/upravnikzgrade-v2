import { Link, Outlet } from "react-router-dom";

export function AppLayout() {
  return (
    <div className="min-h-screen">
      <header className="border-b border-slate-200 bg-white/80 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <h1 className="text-lg font-semibold text-slate-900">Upravnik Portal</h1>
          <nav className="flex gap-4 text-sm text-slate-700">
            <Link to="/login" className="hover:text-brand-700">
              Login
            </Link>
          </nav>
        </div>
      </header>
      <main className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  );
}

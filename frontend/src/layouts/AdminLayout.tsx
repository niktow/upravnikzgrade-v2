import { NavLink, Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";

export function AdminLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate("/login", { replace: true });
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <header className="border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <div>
            <h1 className="text-lg font-semibold text-slate-900">Admin Portal</h1>
            <p className="text-xs text-slate-500">{user?.email}</p>
          </div>
          <nav className="hidden items-center gap-2 md:flex">
            <NavLink
              to="/admin"
              end
              className={({ isActive }) =>
                `rounded-md px-3 py-2 text-sm ${isActive ? "bg-slate-900 text-white" : "text-slate-700 hover:bg-slate-100"}`
              }
            >
              Dashboard
            </NavLink>
            <NavLink
              to="/admin/units"
              className={({ isActive }) =>
                `rounded-md px-3 py-2 text-sm ${isActive ? "bg-slate-900 text-white" : "text-slate-700 hover:bg-slate-100"}`
              }
            >
              Units
            </NavLink>
          </nav>
          <button
            type="button"
            onClick={() => void handleLogout()}
            className="rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100"
          >
            Odjava
          </button>
        </div>
      </header>
      <main className="mx-auto max-w-6xl px-4 py-6">
        <Outlet />
      </main>
    </div>
  );
}

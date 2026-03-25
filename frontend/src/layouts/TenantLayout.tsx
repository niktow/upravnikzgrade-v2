import { Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";

export function TenantLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate("/login", { replace: true });
  };

  return (
    <div className="min-h-screen bg-indigo-50/40">
      <header className="border-b border-indigo-200 bg-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
          <div>
            <h1 className="text-lg font-semibold text-indigo-900">Tenant Portal</h1>
            <p className="text-xs text-indigo-700">{user?.name}</p>
          </div>
          <button
            type="button"
            onClick={() => void handleLogout()}
            className="rounded-md border border-indigo-300 px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-100"
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

import { Navigate, Outlet } from "react-router-dom";
import { PageLoader } from "../components/PageLoader";
import { useAuth } from "./AuthContext";
import type { UserRole } from "./types";

interface RequireAuthProps {
  roles?: UserRole[];
}

export function RequireAuth({ roles }: RequireAuthProps) {
  const { user, loading } = useAuth();

  if (loading) {
    return <PageLoader message="Provera sesije..." />;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (roles && !roles.includes(user.role)) {
    return <Navigate to="/unauthorized" replace />;
  }

  return <Outlet />;
}

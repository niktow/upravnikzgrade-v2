import { Navigate } from "react-router-dom";
import { PageLoader } from "../components/PageLoader";
import { useAuth } from "../auth/AuthContext";

export function HomeRedirectPage() {
  const { user, loading } = useAuth();

  if (loading) {
    return <PageLoader message="Ucitavanje portala..." />;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (user.is_tenant) {
    return <Navigate to="/tenant" replace />;
  }

  return <Navigate to="/admin" replace />;
}

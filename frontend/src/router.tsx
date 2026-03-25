import { createBrowserRouter } from "react-router-dom";
import { RequireAuth } from "./auth/RequireAuth";
import { AppLayout } from "./layouts/AppLayout";
import { AdminLayout } from "./layouts/AdminLayout";
import { TenantLayout } from "./layouts/TenantLayout";
import { AdminHomePage } from "./pages/AdminHomePage";
import { AdminUnitsPage } from "./pages/AdminUnitsPage";
import { HomeRedirectPage } from "./pages/HomeRedirectPage";
import { LoginPage } from "./pages/LoginPage";
import { NotFoundPage } from "./pages/NotFoundPage";
import { TenantHomePage } from "./pages/TenantHomePage";
import { UnauthorizedPage } from "./pages/UnauthorizedPage";

export const router = createBrowserRouter([
  {
    path: "/login",
    element: <AppLayout />,
    children: [
      {
        index: true,
        element: <LoginPage />
      }
    ],
  },
  {
    path: "/",
    element: <HomeRedirectPage />,
  },
  {
    element: <RequireAuth roles={["admin", "manager"]} />,
    children: [
      {
        path: "/admin",
        element: <AdminLayout />,
        children: [
          {
            index: true,
            element: <AdminHomePage />,
          },
          {
            path: "units",
            element: <AdminUnitsPage />,
          },
        ],
      },
    ],
  },
  {
    element: <RequireAuth roles={["tenant"]} />,
    children: [
      {
        path: "/tenant",
        element: <TenantLayout />,
        children: [
          {
            index: true,
            element: <TenantHomePage />,
          },
        ],
      },
    ],
  },
  {
    path: "/unauthorized",
    element: <UnauthorizedPage />,
  },
  {
    path: "*",
    element: <NotFoundPage />,
  }
]);

import { createContext, type ReactNode, useContext, useEffect, useMemo, useState } from "react";
import { apiGet, apiPost, fetchCsrfCookie } from "../lib/api";
import type { AuthUser, LoginPayload } from "./types";

const AUTH_USER_STORAGE_KEY = "upravnik.auth.user";

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  login: (payload: LoginPayload) => Promise<AuthUser>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(() => {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY);
    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as AuthUser;
    } catch {
      localStorage.removeItem(AUTH_USER_STORAGE_KEY);
      return null;
    }
  });
  const [loading, setLoading] = useState(true);

  const saveUser = (nextUser: AuthUser | null) => {
    setUser(nextUser);
    if (nextUser) {
      localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(nextUser));
      return;
    }

    localStorage.removeItem(AUTH_USER_STORAGE_KEY);
  };

  const refreshUser = async () => {
    try {
      const response = await apiGet<{ user: AuthUser }>("/auth/me");
      saveUser(response.user);
    } catch {
      saveUser(null);
    }
  };

  useEffect(() => {
    const init = async () => {
      await refreshUser();
      setLoading(false);
    };

    void init();
  }, []);

  const login = async (payload: LoginPayload) => {
    await fetchCsrfCookie();
    const response = await apiPost<{ user: AuthUser }>("/auth/login", payload);
    saveUser(response.user);
    return response.user;
  };

  const logout = async () => {
    await fetchCsrfCookie();
    await apiPost("/auth/logout", {});
    saveUser(null);
  };

  const value = useMemo<AuthContextValue>(
    () => ({ user, loading, login, logout, refreshUser }),
    [user, loading]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error("useAuth must be used inside AuthProvider");
  }

  return context;
}

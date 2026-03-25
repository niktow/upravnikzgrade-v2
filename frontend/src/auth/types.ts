export type UserRole = "admin" | "manager" | "tenant" | string;

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  is_admin: boolean;
  is_manager: boolean;
  is_tenant: boolean;
}

export interface LoginPayload {
  email: string;
  password: string;
  remember?: boolean;
}

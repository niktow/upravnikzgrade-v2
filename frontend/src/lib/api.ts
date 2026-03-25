const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export function getApiOrigin(): string {
  return new URL(API_BASE_URL).origin;
}

function readCookie(name: string): string | null {
  const parts = document.cookie.split(";").map((item) => item.trim());
  const match = parts.find((item) => item.startsWith(`${name}=`));
  if (!match) {
    return null;
  }

  return decodeURIComponent(match.split("=")[1]);
}

export async function fetchCsrfCookie(): Promise<void> {
  await fetch(`${getApiOrigin()}/sanctum/csrf-cookie`, {
    credentials: "include",
    headers: {
      Accept: "application/json"
    }
  });
}

async function request<T>(path: string, method: string, body?: unknown): Promise<T> {
  const headers: Record<string, string> = {
    Accept: "application/json"
  };

  if (method !== "GET") {
    headers["Content-Type"] = "application/json";
    const xsrfToken = readCookie("XSRF-TOKEN");
    if (xsrfToken) {
      headers["X-XSRF-TOKEN"] = xsrfToken;
    }
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    method,
    credentials: "include",
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined
  });

  if (!response.ok) {
    const responseBody = await response.text();
    try {
      const parsed = JSON.parse(responseBody) as { message?: string };
      throw new Error(parsed.message || `API request failed: ${response.status}`);
    } catch {
      throw new Error(responseBody || `API request failed: ${response.status}`);
    }
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}

export function apiGet<T>(path: string): Promise<T> {
  return request<T>(path, "GET");
}

export function apiPost<T>(path: string, payload: unknown): Promise<T> {
  return request<T>(path, "POST", payload);
}

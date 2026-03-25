import { type ChangeEvent, type FormEvent, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../auth/AuthContext";

export function LoginPage() {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);
    setSubmitting(true);

    try {
      const user = await login({ email, password });
      if (user.is_tenant) {
        navigate("/tenant", { replace: true });
      } else {
        navigate("/admin", { replace: true });
      }
    } catch (error) {
      if (error instanceof Error && error.message) {
        setError(error.message);
      } else {
        setError("Neuspesna prijava. Proverite email i lozinku.");
      }
    } finally {
      setSubmitting(false);
    }
  };

  const handleEmailChange = (event: ChangeEvent<HTMLInputElement>) => {
    setEmail(event.target.value);
  };

  const handlePasswordChange = (event: ChangeEvent<HTMLInputElement>) => {
    setPassword(event.target.value);
  };

  return (
    <section className="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 className="mb-4 text-xl font-semibold text-slate-900">Prijava</h2>
      <form className="space-y-3" onSubmit={handleSubmit}>
        <div>
          <label className="mb-1 block text-sm text-slate-700" htmlFor="email">
            Email
          </label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={handleEmailChange}
            className="w-full rounded-md border border-slate-300 px-3 py-2 outline-none ring-brand-500 focus:ring"
            placeholder="ime@domen.rs"
            required
          />
        </div>
        <div>
          <label className="mb-1 block text-sm text-slate-700" htmlFor="password">
            Lozinka
          </label>
          <input
            id="password"
            type="password"
            value={password}
            onChange={handlePasswordChange}
            className="w-full rounded-md border border-slate-300 px-3 py-2 outline-none ring-brand-500 focus:ring"
            placeholder="********"
            required
          />
        </div>
        {error ? <p className="text-sm text-red-600">{error}</p> : null}
        <button
          type="submit"
          disabled={submitting}
          className="w-full rounded-md bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700"
        >
          {submitting ? "Prijava..." : "Prijavi se"}
        </button>
      </form>
    </section>
  );
}

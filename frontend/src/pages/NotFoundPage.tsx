import { Link } from "react-router-dom";

export function NotFoundPage() {
  return (
    <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 className="text-xl font-semibold text-slate-900">Stranica nije pronadjena</h2>
      <p className="mt-2 text-slate-600">
        Proverite URL ili se vratite na pocetnu stranicu.
      </p>
      <Link to="/" className="mt-4 inline-block text-sm font-medium text-brand-700 hover:underline">
        Nazad na dashboard
      </Link>
    </section>
  );
}

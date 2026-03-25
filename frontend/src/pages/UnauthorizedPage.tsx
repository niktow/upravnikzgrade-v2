import { Link } from "react-router-dom";

export function UnauthorizedPage() {
  return (
    <section className="rounded-xl border border-amber-200 bg-amber-50 p-6">
      <h2 className="text-xl font-semibold text-amber-900">Nemate pristup ovoj stranici</h2>
      <p className="mt-2 text-amber-800">Vasa uloga nema dozvolu za trazeni portal.</p>
      <Link to="/" className="mt-4 inline-block text-sm font-semibold text-amber-900 hover:underline">
        Nazad na pocetnu
      </Link>
    </section>
  );
}

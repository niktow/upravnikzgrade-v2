export function DashboardPage() {
  return (
    <section className="space-y-4">
      <h2 className="text-2xl font-semibold text-slate-900">Dashboard</h2>
      <p className="text-slate-600">
        Pocetna React struktura je spremna. Sledeci korak je povezivanje sa Laravel API endpoint-ima.
      </p>
      <div className="grid gap-4 md:grid-cols-3">
        <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p className="text-sm text-slate-500">Jedinice</p>
          <p className="text-xl font-semibold">--</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p className="text-sm text-slate-500">Dugovanja</p>
          <p className="text-xl font-semibold">--</p>
        </div>
        <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
          <p className="text-sm text-slate-500">Transakcije</p>
          <p className="text-xl font-semibold">--</p>
        </div>
      </div>
    </section>
  );
}

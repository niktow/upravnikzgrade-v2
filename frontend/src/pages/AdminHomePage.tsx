import { useQuery } from "@tanstack/react-query";
import { apiGet } from "../lib/api";

interface AdminDashboardResponse {
  period: {
    from: string;
    to: string;
  };
  stats: {
    total_units: number;
    active_units: number;
    total_owners: number;
    monthly_charges: number;
    monthly_payments: number;
    total_balance: number;
  };
}

export function AdminHomePage() {
  const dashboardQuery = useQuery({
    queryKey: ["admin-dashboard"],
    queryFn: () => apiGet<AdminDashboardResponse>("/admin/dashboard"),
  });

  return (
    <section className="space-y-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <h2 className="text-2xl font-semibold text-slate-900">Admin dashboard</h2>
      {dashboardQuery.isLoading ? <p className="text-slate-600">Ucitavanje podataka...</p> : null}
      {dashboardQuery.isError ? <p className="text-red-600">Greska pri ucitavanju dashboard podataka.</p> : null}

      {dashboardQuery.data ? (
        <>
          <p className="text-sm text-slate-500">
            Period: {dashboardQuery.data.period.from} - {dashboardQuery.data.period.to}
          </p>
          <div className="grid gap-3 md:grid-cols-3">
            <article className="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Jedinice</p>
              <p className="text-xl font-semibold text-slate-900">{dashboardQuery.data.stats.total_units}</p>
              <p className="text-xs text-slate-500">Aktivne: {dashboardQuery.data.stats.active_units}</p>
            </article>
            <article className="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Vlasnici</p>
              <p className="text-xl font-semibold text-slate-900">{dashboardQuery.data.stats.total_owners}</p>
            </article>
            <article className="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Ukupni bilans</p>
              <p className="text-xl font-semibold text-slate-900">
                {dashboardQuery.data.stats.total_balance.toFixed(2)} RSD
              </p>
            </article>
            <article className="rounded-lg border border-slate-200 bg-white p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Mesecna zaduzenja</p>
              <p className="text-lg font-semibold text-slate-900">
                {dashboardQuery.data.stats.monthly_charges.toFixed(2)} RSD
              </p>
            </article>
            <article className="rounded-lg border border-slate-200 bg-white p-3">
              <p className="text-xs uppercase tracking-wide text-slate-500">Mesecne uplate</p>
              <p className="text-lg font-semibold text-slate-900">
                {dashboardQuery.data.stats.monthly_payments.toFixed(2)} RSD
              </p>
            </article>
          </div>
        </>
      ) : null}
    </section>
  );
}

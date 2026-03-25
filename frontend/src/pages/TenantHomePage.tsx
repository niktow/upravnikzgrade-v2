import { useQuery } from "@tanstack/react-query";
import { apiGet } from "../lib/api";

interface TenantUnitItem {
  id: number;
  identifier: string;
  type: string;
  housing_community: string | null;
  owner_names: string[];
  current_balance: number;
}

interface TenantDashboardResponse {
  stats: {
    units_count: number;
    total_balance: number;
  };
  units: TenantUnitItem[];
}

export function TenantHomePage() {
  const dashboardQuery = useQuery({
    queryKey: ["tenant-dashboard"],
    queryFn: () => apiGet<TenantDashboardResponse>("/tenant/dashboard"),
  });

  return (
    <section className="space-y-2 rounded-xl border border-indigo-200 bg-white p-6 shadow-sm">
      <h2 className="text-2xl font-semibold text-indigo-900">Tenant dashboard</h2>
      {dashboardQuery.isLoading ? <p className="text-indigo-700">Ucitavanje podataka...</p> : null}
      {dashboardQuery.isError ? <p className="text-red-600">Greska pri ucitavanju podataka.</p> : null}

      {dashboardQuery.data ? (
        <>
          <div className="grid gap-3 sm:grid-cols-2">
            <article className="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
              <p className="text-xs uppercase tracking-wide text-indigo-700">Broj jedinica</p>
              <p className="text-xl font-semibold text-indigo-900">{dashboardQuery.data.stats.units_count}</p>
            </article>
            <article className="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
              <p className="text-xs uppercase tracking-wide text-indigo-700">Trenutni bilans</p>
              <p className="text-xl font-semibold text-indigo-900">
                {dashboardQuery.data.stats.total_balance.toFixed(2)} RSD
              </p>
            </article>
          </div>

          <div className="space-y-2 pt-2">
            <h3 className="text-sm font-semibold uppercase tracking-wide text-indigo-700">Moje jedinice</h3>
            {dashboardQuery.data.units.length === 0 ? (
              <p className="text-sm text-indigo-700">Nema povezanih jedinica.</p>
            ) : (
              <div className="space-y-2">
                {dashboardQuery.data.units.map((unit) => (
                  <article key={unit.id} className="rounded-lg border border-indigo-100 p-3">
                    <p className="font-medium text-indigo-900">{unit.identifier}</p>
                    <p className="text-sm text-indigo-700">
                      {unit.type} | {unit.housing_community ?? "Bez zajednice"}
                    </p>
                    <p className="text-sm text-indigo-800">Bilans: {unit.current_balance.toFixed(2)} RSD</p>
                  </article>
                ))}
              </div>
            )}
          </div>
        </>
      ) : null}
    </section>
  );
}

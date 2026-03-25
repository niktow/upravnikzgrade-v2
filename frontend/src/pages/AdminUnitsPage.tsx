import { useMemo, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { apiGet } from "../lib/api";

interface UnitItem {
  id: number;
  identifier: string;
  type: string;
  is_active: boolean;
  area: number | null;
  occupant_count: number | null;
  housing_community: string | null;
  owner_names: string[];
  current_balance: number;
}

interface AdminUnitsResponse {
  data: UnitItem[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    search: string;
  };
}

export function AdminUnitsPage() {
  const [searchInput, setSearchInput] = useState("");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);

  const queryString = useMemo(() => {
    const params = new URLSearchParams();
    params.set("page", String(page));
    params.set("per_page", "20");

    if (search.trim() !== "") {
      params.set("search", search.trim());
    }

    return params.toString();
  }, [page, search]);

  const unitsQuery = useQuery({
    queryKey: ["admin-units", page, search],
    queryFn: () => apiGet<AdminUnitsResponse>(`/admin/units?${queryString}`),
    placeholderData: (previousData) => previousData,
  });

  const submitSearch = () => {
    setPage(1);
    setSearch(searchInput);
  };

  return (
    <section className="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h2 className="text-2xl font-semibold text-slate-900">Units</h2>
          <p className="text-sm text-slate-600">Globalna pretraga po oznaci, tipu, vlasniku i zajednici.</p>
        </div>
        <div className="flex w-full gap-2 md:w-auto">
          <input
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            onKeyDown={(event) => {
              if (event.key === "Enter") {
                submitSearch();
              }
            }}
            className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none ring-brand-500 focus:ring md:w-72"
            placeholder="Pretraga..."
          />
          <button
            type="button"
            onClick={submitSearch}
            className="rounded-md bg-brand-500 px-3 py-2 text-sm font-medium text-white hover:bg-brand-700"
          >
            Trazi
          </button>
        </div>
      </div>

      {unitsQuery.isLoading ? <p className="text-sm text-slate-500">Ucitavanje...</p> : null}
      {unitsQuery.isError ? <p className="text-sm text-red-600">Greska pri ucitavanju jedinica.</p> : null}

      {unitsQuery.data ? (
        <>
          <div className="overflow-x-auto">
            <table className="min-w-full border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-slate-200 text-slate-600">
                  <th className="px-2 py-2 font-medium">Jedinica</th>
                  <th className="px-2 py-2 font-medium">Tip</th>
                  <th className="px-2 py-2 font-medium">Vlasnici</th>
                  <th className="px-2 py-2 font-medium">Zajednica</th>
                  <th className="px-2 py-2 font-medium">Bilans</th>
                  <th className="px-2 py-2 font-medium">Status</th>
                </tr>
              </thead>
              <tbody>
                {unitsQuery.data.data.map((unit) => (
                  <tr key={unit.id} className="border-b border-slate-100">
                    <td className="px-2 py-2 text-slate-900">{unit.identifier}</td>
                    <td className="px-2 py-2 text-slate-700">{unit.type}</td>
                    <td className="px-2 py-2 text-slate-700">{unit.owner_names.join(", ") || "-"}</td>
                    <td className="px-2 py-2 text-slate-700">{unit.housing_community ?? "-"}</td>
                    <td className="px-2 py-2 text-slate-900">{unit.current_balance.toFixed(2)} RSD</td>
                    <td className="px-2 py-2 text-slate-700">{unit.is_active ? "Aktivna" : "Neaktivna"}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="flex flex-wrap items-center justify-between gap-3 pt-2 text-sm text-slate-600">
            <p>
              Ukupno: <span className="font-medium text-slate-900">{unitsQuery.data.meta.total}</span> | Strana {" "}
              <span className="font-medium text-slate-900">{unitsQuery.data.meta.current_page}</span> od{" "}
              <span className="font-medium text-slate-900">{unitsQuery.data.meta.last_page}</span>
            </p>
            <div className="flex gap-2">
              <button
                type="button"
                disabled={unitsQuery.data.meta.current_page <= 1}
                onClick={() => setPage((value) => Math.max(1, value - 1))}
                className="rounded-md border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Prethodna
              </button>
              <button
                type="button"
                disabled={unitsQuery.data.meta.current_page >= unitsQuery.data.meta.last_page}
                onClick={() =>
                  setPage((value) =>
                    Math.min(unitsQuery.data?.meta.last_page ?? value, value + 1)
                  )
                }
                className="rounded-md border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-50"
              >
                Sledeca
              </button>
            </div>
          </div>
        </>
      ) : null}
    </section>
  );
}

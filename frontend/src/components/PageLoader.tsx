interface PageLoaderProps {
  message?: string;
}

export function PageLoader({ message = "Ucitavanje podataka..." }: PageLoaderProps) {
  return (
    <div className="flex min-h-[40vh] items-center justify-center">
      <div className="rounded-xl border border-slate-200 bg-white px-6 py-4 text-sm text-slate-600 shadow-sm">
        {message}
      </div>
    </div>
  );
}

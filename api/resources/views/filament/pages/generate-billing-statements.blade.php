<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generateStatements">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>

    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Informacije o generisanju računa
        </x-slot>
        
        <x-slot name="description">
            Ova stranica omogućava generisanje mesečnih računa za sve ili specifične jedinice.
        </x-slot>

        <div class="prose dark:prose-invert max-w-none">
            <h3>Kako funkcioniše:</h3>
            <ul>
                <li><strong>Period:</strong> Odaberite mesec i godinu za koju želite da generišete račune</li>
                <li><strong>Stambena zajednica:</strong> Možete odabrati specifičnu zajednicu ili ostaviti prazno za sve zajednice</li>
                <li><strong>Jedinica:</strong> Možete odabrati specifičnu jedinicu za generisanje pojedinačnog računa</li>
            </ul>

            <h3>Gde se čuvaju generisani računi:</h3>
            <p>Računi se automatski čuvaju u: <code>storage/app/statements/{godina}/{mesec}/</code></p>

            <div class="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg mt-4">
                <p class="text-sm text-blue-800 dark:text-blue-200 mb-0">
                    <strong>Napomena:</strong> Za pojedinačnu jedinicu, račun će biti preuzet direktno. 
                    Za više jedinica, računi će biti sačuvani u storage folderu.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>

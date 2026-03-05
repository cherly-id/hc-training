<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
        <footer class="mt-auto py-10 text-center border-t border-slate-50">
    <div class="flex flex-col items-center justify-center space-y-2">
        {{-- Garis dekoratif kecil untuk kesan mahal --}}
        <div class="w-8 h-[2px] bg-slate-100 rounded-full mb-2"></div>
        
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] italic">
            &copy; 2026 <span class="text-blue-600 mx-1">•</span> 
            <span class="text-slate-600">CHERLY OCTIFA NIRWANA</span>
        </p>
        
        <p class="text-[9px] font-bold text-blue-300 uppercase tracking-widest leading-none">
            Human Capital Training Management System 
        </p>
    </div>
</footer>
    </flux:main>
</x-layouts::app.sidebar>
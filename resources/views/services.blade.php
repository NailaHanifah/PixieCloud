<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Paket Layanan - PixieCloud</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..700;1,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-[#F9F6F0] text-[#3D4A3E] min-h-screen flex flex-col justify-between">

    <nav class="bg-[#FAF8F5] border-b border-[#F3ECE0] px-6 py-4">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="/" class="font-serif text-xl font-bold tracking-wide text-[#2C3E2B]">PixieCloud.</a>
            <div class="flex items-center gap-6 text-sm">
                <a href="/services" class="font-semibold text-[#2C3E2B] border-b-2 border-[#2C3E2B] pb-1">Paket Sewa</a>
                <a href="/dashboard" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Kredensial</a>
                <a href="/storage" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Penyimpanan</a>
                
                <span class="text-xs px-3 py-1 bg-[#F4F1EA] rounded-full text-[#5C6B5D] font-medium">
                    Status: {{ $activeSubscription ? 'Aktif 🟢' : 'Nonaktif 🔴' }}
                </span>
                
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-800 font-medium transition pb-1">Keluar Portal</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-5xl mx-auto px-6 py-12 flex-grow flex flex-col items-center justify-center">
        
        @if(session('error'))
            <div class="mb-6 w-full max-w-4xl p-4 bg-red-100 border border-red-300 text-red-800 text-xs font-medium rounded-xl">
                🔴 {{ session('error') }}
            </div>
        @endif

        <div class="text-center max-w-2xl mb-12">
            <h1 class="font-serif text-3xl text-[#2C3E2B] mb-2">Tentukan Kapasitas Ruang Virtual</h1>
            <p class="text-xs text-[#8C8275]">Pilih paket alokasi penyimpanan modular terisolasi untuk infrastruktur objek data Anda.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 w-full max-w-4xl mx-auto items-stretch">
            
            <div class="bg-[#FAF8F5] rounded-[2rem] p-8 border border-[#E6DFD3] flex flex-col justify-between shadow-sm">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#8C8275] block mb-2">Pixie Plan</span>
                    <h2 class="font-serif text-2xl text-[#2C3E2B] mb-4">500 MB Storage</h2>
                    <ul class="space-y-3 text-xs text-[#3D4A3E] mb-8">
                        <li class="flex items-center gap-2">🌱 <span>1 Bucket Utama</span></li>
                        <li class="flex items-center gap-2">🌱 <span>Bandwidth Standar</span></li>
                    </ul>
                </div>
                
                <form action="{{ route('services.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_id" value="1">
                    @if($activeSubscription && $activeSubscription->service_id == 1)
                        <button type="button" disabled class="w-full text-center bg-gray-200 text-gray-400 py-3 rounded-xl text-xs font-medium cursor-not-allowed">Paket Aktif Saat Ini</button>
                    @else
                        <button type="submit" class="w-full text-center bg-[#2C3E2B] text-[#F9F6F0] py-3 rounded-xl text-xs font-medium hover:bg-[#3D4A3E] transition">Aktifkan Instan</button>
                    @endif
                </form>
            </div>

            <div class="bg-[#FAF8F5] rounded-[2rem] p-8 border-2 border-[#D3A297] flex flex-col justify-between shadow-md relative">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#8C626F] block mb-2">Griffin Plan</span>
                    <h2 class="font-serif text-2xl text-[#2C3E2B] mb-4">5 GB Storage</h2>
                    <ul class="space-y-3 text-xs text-[#3D4A3E] mb-8">
                        <li class="flex items-center gap-2">🌸 <span>3 Bucket Terpisah</span></li>
                        <li class="flex items-center gap-2">🌸 <span>Prioritas Throughput</span></li>
                    </ul>
                </div>
                
                <form action="{{ route('services.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_id" value="2">
                    @if($activeSubscription && $activeSubscription->service_id == 2)
                        <button type="button" disabled class="w-full text-center bg-gray-200 text-gray-400 py-3 rounded-xl text-xs font-medium cursor-not-allowed">Paket Aktif Saat Ini</button>
                    @else
                        <button type="submit" class="w-full text-center bg-[#D3A297] text-[#3D4A3E] py-3 rounded-xl text-xs font-medium hover:bg-[#C59287] transition">Aktifkan Instan 🌿</button>
                    @endif
                </form>
            </div>

            <div class="bg-[#FAF8F5] rounded-[2rem] p-8 border border-[#E6DFD3] flex flex-col justify-between shadow-sm">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-[#8C8275] block mb-2">Dragon Plan</span>
                    <h2 class="font-serif text-2xl text-[#2C3E2B] mb-4">50 GB Storage</h2>
                    <ul class="space-y-3 text-xs text-[#3D4A3E] mb-8">
                        <li class="flex items-center gap-2">🌿 <span>10 Bucket Terpisah</span></li>
                        <li class="flex items-center gap-2">🌿 <span>High-Speed Objek Pull</span></li>
                    </ul>
                </div>
                
                <form action="{{ route('services.upgrade') }}" method="POST">
                    @csrf
                    <input type="hidden" name="service_id" value="3">
                    @if($activeSubscription && $activeSubscription->service_id == 3)
                        <button type="button" disabled class="w-full text-center bg-gray-200 text-gray-400 py-3 rounded-xl text-xs font-medium cursor-not-allowed">Paket Aktif Saat Ini</button>
                    @else
                        <button type="submit" class="w-full text-center bg-[#2C3E2B] text-[#F9F6F0] py-3 rounded-xl text-xs font-medium hover:bg-[#3D4A3E] transition">Aktifkan Instan</button>
                    @endif
                </form>
            </div>
            
        </div>
    </main>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>
</body>
</html>
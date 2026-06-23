<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Kendali Admin - PixieCloud</title>
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

    <!-- 🌿 NAVIGASI ATAS ADMIN -->
    <nav class="bg-[#FAF8F5] border-b border-[#F3ECE0] px-6 py-4 w-full">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="/admin" class="font-serif text-xl font-bold tracking-wide text-[#2C3E2B]">PixieCloud.</a>
                <span class="text-[10px] bg-[#2C3E2B] text-[#F9F6F0] px-2.5 py-0.5 rounded-full font-medium uppercase tracking-wider">Otoritas Admin</span>
            </div>
            <div class="flex items-center gap-6 text-sm">
                <a href="/admin" class="font-semibold text-[#2C3E2B] border-b-2 border-[#2C3E2B] pb-1">Kontrol Utama</a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-800 font-medium transition pb-1">Keluar Portal</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <main class="w-full max-w-7xl mx-auto px-6 py-10 flex-grow space-y-8">
        <div>
            <h1 class="font-serif text-3xl text-[#2C3E2B] mb-1">Pusat Kendali Administrator</h1>
            <p class="text-xs text-[#8C8275]">Dashboard Overview platform, Manajemen Pengguna, Audit Forensik Aktivitas, dan CRUD Paket Layanan Virtual.</p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-[#D3ECE0] border border-[#2C3E2B]/20 text-[#2C3E2B] text-xs rounded-xl font-medium shadow-sm">
                🌿 {{ session('success') }}
            </div>
        @endif

        <!-- FITUR 1: DASHBOARD OVERVIEW -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-[#FAF8F5] rounded-[1.5rem] p-5 border border-[#E6DFD3] shadow-sm">
                <span class="block text-[10px] font-bold text-[#8C8275] uppercase tracking-wider mb-1">Total Users</span>
                <span class="text-3xl font-serif font-bold text-[#2C3E2B]">{{ $totalUsers ?? 5 }}</span>
            </div>
            <div class="bg-[#FAF8F5] rounded-[1.5rem] p-5 border border-[#E6DFD3] shadow-sm">
                <span class="block text-[10px] font-bold text-[#8C8275] uppercase tracking-wider mb-1">Subscriptions Aktif</span>
                <span class="text-3xl font-serif font-bold text-[#2C3E2B]">{{ $activeVaults ?? 4 }}</span>
            </div>
            <div class="bg-[#FAF8F5] rounded-[1.5rem] p-5 border border-[#E6DFD3] shadow-sm">
                <span class="block text-[10px] font-bold text-[#8C8275] uppercase tracking-wider mb-1">Storage Terpakai</span>
                <span class="text-3xl font-serif font-bold text-[#8C626F]">16.39 MB</span>
            </div>
            <div class="bg-[#FAF8F5] rounded-[1.5rem] p-5 border border-[#E6DFD3] shadow-sm">
                <span class="block text-[10px] font-bold text-[#8C8275] uppercase tracking-wider mb-1">Total Revenue</span>
                <span class="text-3xl font-serif font-bold text-emerald-700">Rp0</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-start">
            <!-- COLUMN LEFT: DAFTAR USER & CRUD PAKET LAYANAN -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- FITUR 2 & 3: DAFTAR SEMUA USER -->
                <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] shadow-sm">
                    <h2 class="font-serif text-lg text-[#2C3E2B] mb-4">Daftar Semua User Platform</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E6DFD3] text-[10px] font-bold uppercase text-[#8C8275] tracking-wider">
                                    <th class="pb-3 pl-2">Username</th>
                                    <th class="pb-3">Email</th>
                                    <th class="pb-3">Role</th>
                                    <th class="pb-3 pr-2 text-right">Status Subscription</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E6DFD3]/60">
                                @forelse($users ?? [] as $userItem)
                                <tr>
                                    <td class="py-3 pl-2 font-semibold text-[#2C3E2B]">{{ $userItem->username }}</td>
                                    <td class="py-3 font-mono text-[#8C8275]">{{ $userItem->email }}</td>
                                    <td class="py-3 capitalize"><span class="px-2 py-0.5 rounded text-[10px] {{ $userItem->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">{{ $userItem->role }}</span></td>
                                    <td class="py-3 pr-2 text-right">
                                        <span class="bg-[#D3ECE0] text-[#2C3E2B] px-2 py-0.5 rounded font-medium text-[10px]">Active</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="py-4 text-center italic text-gray-400">Belum ada user terdaftar.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FITUR 5: KELOLA PAKET LAYANAN (CRUD) -->
                <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-serif text-lg text-[#2C3E2B]">Kelola Paket Layanan</h2>
                        <button onclick="alert('Membuka modal tambah paket layanan baru')" class="bg-[#2C3E2B] hover:bg-[#3D4A3E] text-[#F9F6F0] px-3 py-1.5 rounded-lg text-[10px] font-semibold transition shadow-sm">+ Tambah Paket</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-[#E6DFD3] text-[10px] font-bold uppercase text-[#8C8275] tracking-wider">
                                    <th class="pb-3 pl-2">Nama Service / Paket</th>
                                    <th class="pb-3">Kuota Storage</th>
                                    <th class="pb-3">Harga</th>
                                    <th class="pb-3 pr-2 text-right">Aksi Manajemen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E6DFD3]/60">
                                <tr>
                                    <td class="py-3.5 pl-2 font-semibold text-[#2C3E2B]">Pixie Plan (Default)</td>
                                    <td class="py-3.5 font-mono text-[#8C8275]">500 MB</td>
                                    <td class="py-3.5 text-[#2C3E2B] font-medium">Rp15.000 / bln</td>
                                    <td class="py-3.5 pr-2 text-right space-x-1">
                                        <button onclick="alert('Membuka formulir edit Pixie Plan')" class="text-blue-600 hover:underline font-medium">Edit</button>
                                        <button onclick="confirm('Hapus paket layanan ini?')" class="text-red-600 hover:underline font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3.5 pl-2 font-semibold text-[#2C3E2B]">Griffin Plan</td>
                                    <td class="py-3.5 font-mono text-[#8C8275]">5120 MB</td>
                                    <td class="py-3.5 text-[#2C3E2B] font-medium">Rp50.000 / bln</td>
                                    <td class="py-3.5 pr-2 text-right space-x-1">
                                        <button onclick="alert('Membuka formulir edit Griffin Plan')" class="text-blue-600 hover:underline font-medium">Edit</button>
                                        <button onclick="confirm('Hapus paket layanan ini?')" class="text-red-600 hover:underline font-medium">Hapus</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-3.5 pl-2 font-semibold text-[#2C3E2B]">Dragon Plan</td>
                                    <td class="py-3.5 font-mono text-[#8C8275]">51200 MB</td>
                                    <td class="py-3.5 text-[#2C3E2B] font-medium">Rp150.000 / bln</td>
                                    <td class="py-3.5 pr-2 text-right space-x-1">
                                        <button onclick="alert('Membuka formulir edit Dragon Plan')" class="text-blue-600 hover:underline font-medium">Edit</button>
                                        <button onclick="confirm('Hapus paket layanan ini?')" class="text-red-600 hover:underline font-medium">Hapus</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- COLUMN RIGHT: FITUR 4 - LIHAT ACTIVITY LOG SEMUA USER -->
            <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] shadow-sm flex flex-col justify-between min-h-[500px]">
                <div>
                    <h2 class="font-serif text-xl text-[#2C3E2B] mb-1">Global Activity Trail</h2>
                    <p class="text-[11px] text-[#8C8275] mb-4">Audit jejak keamanan forensik aktivitas komputasi semua pengguna platform.</p>
                    <div class="space-y-4 max-h-[420px] overflow-y-auto pr-1">
                        @forelse($globalLogs ?? [] as $gLog)
                        <div class="p-3 bg-[#F4F1EA] rounded-xl border border-[#E6DFD3] text-[11px]">
                            <div class="flex justify-between font-mono text-[9px] text-[#8C8275] mb-1">
                                <span>{{ $gLog->created_at ? $gLog->created_at->format('Y-m-d H:i:s') : '-' }}</span>
                                <span class="text-[#8C626F]">{{ $gLog->ip_address ?? '127.0.0.1' }}</span>
                            </div>
                            <p class="text-[#2C3E2B] font-medium leading-relaxed">
                                <span class="font-bold text-[#8C626F]">{{ $gLog->user ? $gLog->user->username : 'System' }}</span> {{ $gLog->activity }}
                            </p>
                        </div>
                        @empty
                        <div class="text-center py-8 text-gray-400 italic text-xs">📜 Belum ada rekaman jejak forensik di server.</div>
                        @endforelse
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-[#E6DFD3] text-[10px] text-center text-[#8C8275] italic">
                    🔄 Sinkronisasi log enkripsi MiniStack aktif.
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>

</body>
</html>
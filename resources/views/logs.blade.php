<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - PixieCloud</title>
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
                <a href="/services" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Paket Sewa</a>
                <a href="/dashboard" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Kredensial</a>
                <a href="/storage" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Penyimpanan</a>
                <a href="/logs" class="font-semibold text-[#2C3E2B] border-b-2 border-[#2C3E2B] pb-1">Riwayat</a>

                <span class="text-xs px-3 py-1 bg-[#F4F1EA] rounded-full text-[#5C6B5D] font-medium">
                    Vault: {{ $bucket ? Str::lower($bucket->bucket_name) : 'Belum Terbuat' }}
                </span>
                
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-800 font-medium transition pb-1">Keluar Portal</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-4xl mx-auto px-6 py-10 flex-grow">
        <div class="mb-8">
            <h1 class="font-serif text-2xl text-[#2C3E2B] mb-1">Linimasa PixieCloud</h1>
            <p class="text-xs text-[#8C8275]">Rekaman riwayat operasi dan mutasi data pada infrastruktur penyimpanan virtual Anda.</p>
        </div>

        <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E6DFD3] text-[#5C6B5D] uppercase tracking-wider text-[10px] font-semibold">
                            <th class="pb-3 font-medium">Waktu Peristiwa (WITA)</th>
                            <th class="pb-3 font-medium">Deskripsi Operasi Kluster</th>
                            <th class="pb-3 font-medium text-right">IP Address Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F4F1EA]">
                        @forelse($logs as $log)
                            <tr class="hover:bg-[#F4F1EA]/50 transition">
                                <td class="py-3.5 text-[#8C8275] font-mono whitespace-nowrap">
                                    {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                                <td class="py-3.5 font-medium text-[#2C3E2B] pr-4">
                                    ⚙️ {{ $log->activity }}
                                </td>
                                <td class="py-3.5 text-right text-[#8C626F] font-mono whitespace-nowrap">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-xs text-gray-400 italic bg-[#FAF8F5]">
                                    📜 Belum ada rekaman jejak telemetri pada kluster vault ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Audit Trail System
    </footer>
</body>
</html>
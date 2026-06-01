<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Layanan - PixieCloud</title>
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

    <nav class="bg-[#FAF8F5] border-b border-[#F3ECE0] px-6 py-4 w-full">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <a href="/" class="font-serif text-xl font-bold tracking-wide text-[#2C3E2B] shrink-0">PixieCloud.</a>
            
            <div class="flex items-center gap-6 text-sm">
                <a href="/services" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Paket Sewa</a>
                <a href="/dashboard" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Kredensial</a>
                <a href="/storage" class="text-[#8C8275] hover:text-[#2C3E2B] transition pb-1">Penyimpanan</a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                class="text-red-600 hover:text-red-800 font-medium transition pb-1">Keluar Portal</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-6xl mx-auto px-6 py-10 flex-grow">
        <div class="mb-8">
            <h1 class="font-serif text-3xl text-[#2C3E2B] mb-1">Infrastruktur Data Anda</h1>
            <p class="text-xs text-[#8C8275]">Manajemen otentikasi identitas akses dan ringkasan kuota sewa virtual cloud.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 items-start">
            
            <div class="md:col-span-2 bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] flex flex-col justify-between shadow-sm min-h-[350px]">
                <div>
                    <h2 class="font-serif text-lg text-[#2C3E2B] mb-2">IAM Cloud Credentials</h2>
                    <p class="text-xs text-[#8C8275] leading-relaxed mb-6">Gunakan sepasang kunci akses virtual privat di bawah ini sebagai paspor enkripsi otentikasi untuk melakukan komunikasi unggah/unduh file secara terisolasi.</p>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#5C6B5D] mb-1.5">Cloud Access Key ID</label>
                            <div class="flex items-center bg-[#F4F1EA] px-4 py-2.5 rounded-xl border border-[#E6DFD3] text-xs font-mono text-[#2C3E2B] select-all w-full overflow-x-auto">
                                {{ $credential ? $credential->ministack_access_key : 'Kunci belum diterbitkan' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold uppercase tracking-wider text-[#5C6B5D] mb-1.5">Cloud Secret Access Key</label>
                            <div class="flex items-center justify-between bg-[#F4F1EA] px-4 py-2.5 rounded-xl border border-[#E6DFD3] text-xs font-mono text-[#2C3E2B] w-full">
                                <span id="secret-key-placeholder" class="tracking-widest text-gray-400 select-none">••••••••••••••••••••••••••••••••</span>
                                <span id="secret-key-value" class="hidden select-all break-all"></span>
                                
                                @if($credential)
                                    <button onclick="openRevealModal()" id="btn-reveal" class="text-[10px] bg-[#E6DFD3] hover:bg-[#D3ECE0] text-[#5C6B5D] px-2.5 py-1 rounded uppercase font-semibold transition shrink-0 ml-2">
                                        Buka Kunci
                                    </button>
                                @else
                                    <span class="text-[10px] bg-gray-200 text-gray-400 px-2 py-0.5 rounded uppercase font-semibold shrink-0 ml-2">Terkunci</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8 pt-4 border-t border-[#E6DFD3] text-xs text-[#8C8275] italic">🔒 Kunci mutlak menempel pada paket sewa aktif.</div>
            </div>

            <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] shadow-sm flex flex-col justify-between min-h-[350px]">
                <div>
                    <h2 class="font-serif text-lg text-[#2C3E2B] mb-1">Ringkasan Alokasi</h2>
                    <span class="text-[10px] font-semibold uppercase bg-[#D3A297]/30 text-[#8C626F] px-2.5 py-0.5 rounded-full inline-block mb-6">
                        {{ $activeSubscription ? $activeSubscription->service->name : 'Tanpa Paket aktif' }}
                    </span>
                    <div class="mb-6">
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="text-[#5C6B5D] font-medium">Kapasitas Penyimpanan</span>
                            <span class="font-semibold text-[#2C3E2B]">{{ $usedStorageMB }} MB / {{ $bucket ? $bucket->allocated_size_mb : 0 }} MB</span>
                        </div>
                        <div class="w-full bg-[#F4F1EA] h-2.5 rounded-full overflow-hidden border border-[#E6DFD3]">
                            <div class="bg-[#2C3E2B] h-full transition-all duration-500" style="width: {{ $usagePercentage }}%"></div>
                        </div>
                    </div>
                    <div class="space-y-3 pt-2 border-t border-[#E6DFD3] text-xs">
                        <div class="flex justify-between">
                            <span class="text-[#8C8275]">Nama Vault:</span>
                            <span class="font-mono text-[#2C3E2B]">{{ $bucket ? Str::lower($bucket->bucket_name) : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-[#8C8275]">Jumlah File:</span>
                            <span class="font-medium">{{ $totalFiles }} Berkas</span>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <a href="/storage" class="block w-full text-center bg-[#2C3E2B] text-[#F9F6F0] py-3 rounded-xl text-xs font-medium hover:bg-[#3D4A3E] transition">Kelola Berkas Objek →</a>
                </div>
            </div>

        </div>
    </main>

    <div id="reveal-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#FAF8F5] rounded-[2rem] border border-[#E6DFD3] max-w-sm w-full p-6 shadow-2xl">
            <h3 class="font-serif text-xl text-[#2C3E2B] mb-2">Verifikasi Otoritas</h3>
            <p class="text-xs text-[#8C8275] mb-4">Demi etika data privat, masukkan kata sandi akun Anda untuk membongkar gerbang dekripsi Secret Key.</p>
            
            <div class="space-y-4">
                <div>
                    <input type="password" id="confirm-password" class="w-full px-4 py-2.5 rounded-xl bg-[#F4F1EA] border border-[#E6DFD3] text-[#3D4A3E] focus:outline-none focus:border-[#2C3E2B] text-sm" placeholder="Masukkan kata sandi Anda">
                    <span id="error-message" class="text-[11px] text-red-600 font-medium mt-1 hidden block"></span>
                </div>
                <div class="flex gap-3 justify-end text-xs font-medium">
                    <button onclick="closeRevealModal()" class="px-4 py-2.5 rounded-xl text-[#8C8275] hover:text-[#2C3E2B]">Batal</button>
                    <button onclick="submitReveal()" class="px-5 py-2.5 bg-[#2C3E2B] text-[#F9F6F0] rounded-xl hover:bg-[#3D4A3E]">Buka Gerbang</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>

    <script>
        function openRevealModal() {
            document.getElementById('reveal-modal').classList.remove('hidden');
            document.getElementById('confirm-password').focus();
        }

        function closeRevealModal() {
            document.getElementById('reveal-modal').classList.add('hidden');
            document.getElementById('confirm-password').value = '';
            document.getElementById('error-message').classList.add('hidden');
        }

        function submitReveal() {
            const password = document.getElementById('confirm-password').value;
            const errorElement = document.getElementById('error-message');

            if (!password) {
                errorElement.innerText = 'Kata sandi wajib diisi.';
                errorElement.classList.remove('hidden');
                return;
            }

            fetch("{{ route('dashboard.reveal') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ password: password })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Otentikasi gagal.');
                return data;
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('secret-key-placeholder').classList.add('hidden');
                    const valueContainer = document.getElementById('secret-key-value');
                    valueContainer.innerText = data.secret_key;
                    valueContainer.classList.remove('hidden');
                    
                    document.getElementById('btn-reveal').classList.add('hidden');
                    closeRevealModal();
                }
            })
            .catch(err => {
                errorElement.innerText = err.message;
                errorElement.classList.remove('hidden');
            });
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Penyimpanan - PixieCloud</title>
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
                <a href="/storage" class="font-semibold text-[#2C3E2B] border-b-2 border-[#2C3E2B] pb-1">Penyimpanan</a>

                <span class="text-xs px-3 py-1 bg-[#F4F1EA] rounded-full text-[#5C6B5D] font-medium">
                    Vault: {{ $bucket ? Str::lower($bucket->bucket_name) : 'Belum Terbuat' }}
                </span>
                
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-red-600 hover:text-red-800 font-medium transition pb-1">Keluar Portal</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-4xl mx-auto px-6 py-10 flex-grow">
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 text-xs font-medium rounded-xl">
                🟢 {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 text-xs font-medium rounded-xl">
                🔴 {{ session('error') }}
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-8 items-start">
            
            <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] md:col-span-1">
                <h2 class="font-serif text-lg text-[#2C3E2B] mb-2">Unggah Berkas Baru</h2>
                <p class="text-xs text-[#8C8275] leading-relaxed mb-6">Sistem akan memvalidasi kecocokan kunci IAM Anda sebelum berkas didorong masuk.</p>

                <form action="{{ route('storage.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <div class="border-2 border-dashed border-[#E6DFD3] rounded-xl p-5 text-center bg-[#F4F1EA] hover:bg-[#E6DFD3]/30 transition relative">
                            <input type="file" name="object_file" id="object_file" class="hidden" required onchange="displayFileName()">
                            <label for="object_file" class="cursor-pointer text-xs text-[#8C626F] font-medium block">
                                📁 <span id="file-label-text" class="underline block mt-1">Cari dari Perangkat</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-[#2C3E2B] text-[#F9F6F0] py-3 rounded-xl text-xs font-medium hover:bg-[#3D4A3E] transition shadow-sm">
                        Kirim ke Server Virtual 🌿
                    </button>
                </form>
            </div>

            <div class="bg-[#FAF8F5] rounded-[2rem] p-6 border border-[#E6DFD3] md:col-span-2 overflow-hidden">
                <div class="mb-6">
                    <h2 class="font-serif text-lg text-[#2C3E2B] mb-0.5">Daftar Objek Virtual</h2>
                    <p class="text-xs text-[#8C8275]">Metadata arsip dokumen yang berhasil divalidasi oleh sistem MiniStack.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-[#E6DFD3] text-[#5C6B5D] uppercase tracking-wider text-[10px] font-semibold">
                                <th class="pb-3 font-medium">Nama Objek</th>
                                <th class="pb-3 font-medium">Ukuran</th>
                                <th class="pb-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4F1EA]">
                            @forelse($objects as $object)
                                <tr class="hover:bg-[#F4F1EA]/50 transition">
                                    <td class="py-3.5 font-medium text-[#2C3E2B] break-all max-w-[200px] pr-2">
                                        <button onclick="openPreviewModal('{{ $object->object_key }}', '{{ route('storage.download', $object->id) }}?stream=true', '{{ $object->content_type }}')" 
                                                class="text-left font-medium text-[#2C3E2B] hover:text-[#8C626F] hover:underline cursor-pointer">
                                            {{ $object->object_key }}
                                        </button>
                                    </td>
                                    <td class="py-3.5 text-[#8C8275] whitespace-nowrap">
                                        {{ round($object->file_size_bytes / (1024 * 1024), 2) }} MB
                                    </td>
                                    <td class="py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('storage.download', $object->id) }}" target="_blank" class="text-[#8C626F] hover:underline font-medium">
                                            Unduh
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-xs text-gray-400 italic bg-[#FAF8F5]">
                                        📭 Belum ada objek data privat di dalam Vault ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <div id="preview-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-[#FAF8F5] rounded-[2rem] border border-[#E6DFD3] max-w-2xl w-full p-6 shadow-2xl flex flex-col max-h-[85vh]">
            <div class="flex justify-between items-start mb-4 border-b border-[#E6DFD3] pb-3">
                <div>
                    <h3 class="font-serif text-lg text-[#2C3E2B] mb-0.5">Object Data Inspection</h3>
                    <p id="preview-filename" class="text-xs font-mono text-[#8C8275] break-all"></p>
                </div>
                <button onclick="closePreviewModal()" class="text-[#8C8275] hover:text-red-600 text-xs font-semibold p-1 cursor-pointer">✕ Tutup</button>
            </div>
            
            <div id="preview-content" class="flex-grow overflow-auto bg-[#F4F1EA] rounded-xl p-4 flex items-center justify-center min-h-[300px]">
                </div>
        </div>
    </div>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>

    <script>
        function displayFileName() {
            const fileInput = document.getElementById('object_file');
            const labelText = document.getElementById('file-label-text');
            if (fileInput.files.length > 0) {
                labelText.innerText = "📄 " + fileInput.files[0].name;
                labelText.classList.remove('underline');
                labelText.classList.add('text-green-700', 'font-semibold');
            }
        }

        // 🌿 FUNGSI MEMBUKA MODAL INSPEKSI FILE SECARA LIVE
        function openPreviewModal(filename, url, mimeType) {
            document.getElementById('preview-modal').classList.remove('hidden');
            document.getElementById('preview-filename').innerText = filename;
            
            const contentContainer = document.getElementById('preview-content');
            contentContainer.innerHTML = ''; // Hapus sisa renderan lama

            // Jika berkas berupa gambar, tampilkan elemen <img> asli
            if (mimeType.startsWith('image/')) {
                contentContainer.innerHTML = `<img src="${url}" class="max-w-full max-h-[50vh] object-contain rounded-lg shadow-sm">`;
            } 
            // Jika berkas berupa PDF, muat menggunakan tag <embed>
            else if (mimeType === 'application/pdf') {
                contentContainer.innerHTML = `<embed src="${url}" type="application/pdf" class="w-full h-[50vh] rounded-lg">`;
            } 
            // Jika ekstensi lain (seperti txt, json, php) tampilkan blok simulasi terenkripsi aman
            else {
                contentContainer.innerHTML = `
                    <div class="text-center p-6">
                        <span class="text-3xl block mb-2">📄</span>
                        <p class="text-xs text-[#2C3E2B] font-mono mb-2">[Encrypted Secure Object Block]</p>
                        <a href="${url}" target="_blank" class="text-xs bg-[#2C3E2B] text-white px-4 py-2 rounded-lg hover:bg-[#3D4A3E] transition inline-block">Buka Tab Baru untuk Inspeksi</a>
                    </div>`;
            }
        }

        function closePreviewModal() {
            document.getElementById('preview-modal').classList.add('hidden');
            document.getElementById('preview-content').innerHTML = '';
        }
    </script>
</body>
</html>
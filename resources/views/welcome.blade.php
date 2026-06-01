<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PixieCloud</title>
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
            <div class="font-serif text-2xl font-bold tracking-wide text-[#2C3E2B]">PixieCloud.</div>
            <div class="flex gap-6 items-center text-sm font-medium">
                <a href="/login" class="text-[#8C8275] hover:text-[#2C3E2B] transition">Masuk Portal</a>
                <a href="/register" class="bg-[#2C3E2B] text-[#F9F6F0] px-5 py-2 rounded-full hover:bg-[#3D4A3E] transition shadow-sm">Mulai Sesi</a>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-5xl mx-auto px-6 py-12 flex-grow flex flex-col items-center justify-center text-center">
        <div class="w-full h-[450px] rounded-[2rem] overflow-hidden relative shadow-xl border-4 border-[#F3ECE0] mb-12">
            <img src="{{ asset('background.jpg') }}" alt="Cottage Garden" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-[#2C3E2B]/80 via-[#2C3E2B]/30 to-transparent flex flex-col justify-end p-8 md:p-12 text-left">
                <span class="text-[#E6DFD3] text-xs uppercase tracking-widest font-semibold mb-2">Simulasi Object Storage</span>
                <h1 class="font-serif text-3xl md:text-5xl text-[#F9F6F0] max-w-2xl leading-tight mb-4">
                    Titip Data Komputasi Aman, Sehangat Rumah Di Tengah Kebun.
                </h1>
                <p class="text-[#E6DFD3] max-w-md text-sm md:text-base font-light leading-relaxed mb-6">
                    Sistem infrastruktur cloud mandiri dengan pembatasan kuota ruang virtual terisolasi yang dibalut dengan estetika alam dongeng.
                </p>
                <div>
                    <a href="/login" class="inline-block bg-[#D3A297] text-[#3D4A3E] font-medium px-8 py-3.5 rounded-full hover:bg-[#C59287] transition shadow-md">
                        Buka Loker Penyimpanan 🌿
                    </a>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-12 text-left items-center max-w-4xl mx-auto py-6">
            <div>
                <h2 class="font-serif text-3xl text-[#2C3E2B] mb-4">Filosofi Ruang Virtual</h2>
                <p class="text-sm text-[#5C6B5D] leading-relaxed mb-4">
                    PixieCloud mengadopsi prinsip <span class="font-medium italic text-[#8C626F]">Multitenancy Resource Pooling</span>. Setiap pengguna diberikan hak akses satu paket sewa fungsional, membebaskan mereka membangun ruang-ruang penyimpanan modular terisolasi di dalam batas kuota global.
                </p>
                <p class="text-xs text-[#8C8275] italic">
                    "Setiap data yang tersimpan adalah benih digital yang kami jaga integritas jalurnya."
                </p>
            </div>
            <div class="flex justify-center">
                <div class="w-64 h-80 rounded-t-full border-8 border-[#F3ECE0] overflow-hidden shadow-lg">
                    <img src="https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?q=80&w=600" alt="Flowers" class="w-full h-full object-cover grayscale-[20%]">
                </div>
            </div>
        </div>
    </main>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>
</body>
</html>
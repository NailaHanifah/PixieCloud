<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Portal - PixieCloud</title>
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
            <a href="/" class="font-serif text-2xl font-bold tracking-wide text-[#2C3E2B]">PixieCloud.</a>
            <a href="/register" class="text-sm font-medium text-[#8C626F] hover:underline">Daftar Akun Baru →</a>
        </div>
    </nav>

    <div class="flex-grow flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-[#FAF8F5] rounded-[2rem] shadow-xl border border-[#F3ECE0] p-8 md:p-10 relative">
            <div class="absolute top-0 right-0 p-4 opacity-10 text-4xl select-none">🌿</div>
            <div class="text-center mb-8">
                <h2 class="font-serif text-3xl font-semibold text-[#2C3E2B]">Selamat Datang Kembali</h2>
                <p class="text-xs text-[#8C8275] mt-1">Silakan masukkan pasang kunci identitas Anda</p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium uppercase tracking-wider text-[#5C6B5D] mb-1.5">Alamat Email</label>
                    <input type="email" name="email" id="email" required 
                        class="w-full px-4 py-3 rounded-xl bg-[#F4F1EA] border border-[#E6DFD3] text-[#3D4A3E] focus:outline-none focus:border-[#2C3E2B] text-sm transition"
                        placeholder="nama@email.com">
                </div>
                <div>
                    <label for="password" class="block text-xs font-medium uppercase tracking-wider text-[#5C6B5D] mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" id="password" required 
                        class="w-full px-4 py-3 rounded-xl bg-[#F4F1EA] border border-[#E6DFD3] text-[#3D4A3E] focus:outline-none focus:border-[#2C3E2B] text-sm transition"
                        placeholder="••••••••">
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#2C3E2B] text-[#F9F6F0] font-medium py-3 rounded-xl hover:bg-[#3D4A3E] transition shadow-md text-sm">
                        Buka Gerbang Akses
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="w-full text-center py-6 text-xs text-[#8C8275] border-t border-[#F3ECE0]">
        &copy; 2026 PixieCloud Storage Platform
    </footer>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixieCloud</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        .font-serif { font-family: 'Cormorant Garamond', serif; }
        .font-sans { font-family: 'Plus Jakarta Sans', sans-serif; }
        :root {
            --parchment: #F7F4EB;
            --oatmeal: #EAE3D2;
            --sage: #7D8A72;
            --forest: #4A5343;
        }
        body { background-color: var(--parchment); color: var(--forest); }
    </style>
</head>
<body class="font-sans min-h-screen flex flex-col justify-between selection:bg-[#E3C7C3]">

    <header class="w-full max-w-6xl mx-auto px-6 py-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <span class="text-xl">🧚‍♀️</span>
            <span class="font-serif text-xl font-semibold tracking-wide text-[#4A5343]">PixieCloud</span>
        </div>
        <div>
            <a href="/login" class="bg-[#4A5343] text-[#F7F4EB] px-5 py-2 rounded-full text-sm font-medium hover:bg-[#7D8A72] transition duration-300">
                Masuk Portal
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto text-center px-6 my-auto space-y-6">
        <div class="inline-block border border-[#A89481]/40 bg-[#EAE3D2]/30 px-4 py-1 rounded-full">
            <span class="text-[10px] tracking-widest uppercase font-semibold text-[#A89481]">Cloud Storage</span>
        </div>
        
        <h1 class="font-serif text-5xl md:text-6xl font-normal leading-tight text-[#4A5343]">
            Infrastruktur Cloud Berbasis <br>
            <span class="italic text-[#7D8A72] font-semibold">Cottagecore Fairytale</span>
        </h1>
        
        <p class="text-sm md:text-base text-gray-600 max-w-lg mx-auto leading-relaxed">
            Kelola penyimpanan cloud secara otomatis!
        </p>

        <div class="pt-4">
            <a href="/login" class="bg-[#7D8A72] text-[#F7F4EB] px-8 py-3.5 rounded-xl shadow-sm hover:bg-[#4A5343] transition duration-300 text-sm font-medium tracking-wide">
                Mulai Sekarang
            </a>
        </div>
    </main>

    <footer class="w-full text-center py-6 text-[11px] text-[#A89481]">
        &copy; 2026 PixieCloud
    </footer>

</body>
</html>
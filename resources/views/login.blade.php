<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Portal - PixieCloud</title>
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
<body class="font-sans min-h-screen flex items-center justify-center p-4 selection:bg-[#E3C7C3]">

    <div class="bg-[#EAE3D2]/50 max-w-3xl w-full rounded-3xl shadow-xl border border-[#A89481]/20 overflow-hidden grid md:grid-cols-12 backdrop-blur-sm">
        
        <div class="md:col-span-5 bg-[#7D8A72] text-[#F7F4EB] p-8 flex flex-col justify-between relative">
            <div class="flex items-center gap-2">
                <span class="text-lg">✨</span>
                <span class="font-serif text-base tracking-wide font-semibold">PixieCloud</span>
            </div>

            <div class="my-auto py-6">
                <h3 class="font-serif text-2xl md:text-3xl font-normal leading-snug">
                    "Melangkah masuk ke dalam ruang penyimpanan virtual."
                </h3>
            </div>

            <div class="text-[9px] uppercase tracking-widest text-[#F7F4EB]/60">
                2026
            </div>
        </div>

        <div class="md:col-span-7 bg-[#F7F4EB] p-8 md:p-10 flex flex-col justify-center">
            
            <div class="mb-6">
                <h2 id="form-title" class="font-serif text-2xl md:text-3xl font-semibold text-[#4A5343]">Selamat Datang</h2>
                <p id="form-subtitle" class="text-xs text-[#A89481] mt-0.5">Gunakan akun Anda untuk masuk ke sistem</p>
            </div>

            <form id="auth-form" class="space-y-4" onsubmit="handleAuth(event)">
                
                <div id="username-field" class="hidden flex flex-col space-y-1">
                    <label class="text-[10px] font-semibold uppercase tracking-wider text-[#4A5343]/70">Nama Pengguna</label>
                    <input type="text" id="username" placeholder="Contoh: elaina" class="w-full px-4 py-2.5 rounded-xl bg-[#EAE3D2]/30 border border-[#A89481]/30 focus:outline-none focus:border-[#7D8A72] text-sm transition">
                </div>

                <div class="flex flex-col space-y-1">
                    <label class="text-[10px] font-semibold uppercase tracking-wider text-[#4A5343]/70">Alamat Email</label>
                    <input type="email" id="email" required placeholder="nama@email.com" class="w-full px-4 py-2.5 rounded-xl bg-[#EAE3D2]/30 border border-[#A89481]/30 focus:outline-none focus:border-[#7D8A72] text-sm transition">
                </div>

                <div class="flex flex-col space-y-1">
                    <div class="flex justify-between items-center">
                        <label class="text-[10px] font-semibold uppercase tracking-wider text-[#4A5343]/70">Kata Sandi</label>
                        <a href="#" id="forgot-link" class="text-xs text-[#A89481] hover:underline">Lupa Akun?</a>
                    </div>
                    <input type="password" id="password" required placeholder="••••••••" class="w-full px-4 py-2.5 rounded-xl bg-[#EAE3D2]/30 border border-[#A89481]/30 focus:outline-none focus:border-[#7D8A72] text-sm transition">
                </div>

                <button type="submit" id="btn-submit" class="w-full bg-[#7D8A72] text-[#F7F4EB] py-3 rounded-xl text-sm font-medium shadow-sm hover:bg-[#4A5343] transition duration-300 mt-4 cursor-pointer">
                    Masuk ke Portal
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-[#EAE3D2] text-center text-xs text-gray-500">
                <span id="switch-text">Belum memiliki akun?</span>
                <button onclick="toggleForm()" id="btn-switch" class="text-[#7D8A72] font-semibold hover:underline ml-1 cursor-pointer focus:outline-none">Buat Akun</button>
            </div>

        </div>
    </div>

    <script>
        let isLoginView = true;

        function toggleForm() {
            isLoginView = !isLoginView;
            const formTitle = document.getElementById('form-title');
            const formSubtitle = document.getElementById('form-subtitle');
            const usernameField = document.getElementById('username-field');
            const btnSubmit = document.getElementById('btn-submit');
            const switchText = document.getElementById('switch-text');
            const btnSwitch = document.getElementById('btn-switch');
            const forgotLink = document.getElementById('forgot-link');
            const usernameInput = document.getElementById('username');

            if (!isLoginView) {
                formTitle.innerText = "Buat Akun";
                formSubtitle.innerText = "Daftar untuk simulasi alokasi bucket cloud";
                usernameField.classList.remove('hidden');
                usernameInput.setAttribute('required', 'true');
                btnSubmit.innerText = "Daftar Akun Cloud";
                switchText.innerText = "Sudah memiliki akun?";
                btnSwitch.innerText = "Masuk sekarang";
                forgotLink.classList.add('hidden');
            } else {
                formTitle.innerText = "Selamat Datang";
                formSubtitle.innerText = "Gunakan akun Anda untuk masuk ke sistem";
                usernameField.classList.add('hidden');
                usernameInput.removeAttribute('required');
                btnSubmit.innerText = "Masuk ke Portal";
                switchText.innerText = "Belum memiliki akun?";
                btnSwitch.innerText = "Buat Akun";
                forgotLink.classList.remove('hidden');
            }
        }

        function handleAuth(event) {
            event.preventDefault();
            alert(isLoginView ? "✨ Autentikasi berhasil. Masuk ke halaman welcome..." : "🌱 Pendaftaran berhasil! Akun cloud baru telah siap.");
            window.location.href = "/";
        }
    </script>
</body>
</html>
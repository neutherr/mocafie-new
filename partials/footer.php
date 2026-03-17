<footer class="bg-gray-900 text-white pt-16 pb-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div>
                    <img src="assets/img/logo.svg" alt="Logo Mocafie" class="w-32 h-auto">
                    <p class="text-gray-300 mt-4 text-sm leading-relaxed">
                        Menghadirkan kebaikan tepung singkong lokal berkualitas internasional untuk kesehatan keluarga Anda.
                    </p>
                </div>

                <!-- Links -->
                <div>
                    <h4 class="font-bold text-lg mb-4 text-green-400">Tautan Cepat</h4>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="#home" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="#produk" class="hover:text-white transition-colors">Produk</a></li>
                        <li><a href="#tentang" class="hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="#resep" class="hover:text-white transition-colors">Resep</a></li>
                        <li><a href="#kontak" class="hover:text-white transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Marketplace -->
                <div>
                    <h4 class="font-bold text-lg mb-4 text-green-400">Marketplace</h4>
                    <div class="flex gap-4">
                        <!-- Shopee -->
                        <a href="belanja/" target="_blank" rel="noopener" aria-label="Shopee Mocafie" class="group flex items-center justify-center bg-gray-800 hover:bg-[#EE4D2D] p-2 rounded-xl transition-all h-10 w-10 shadow-md">
                            <span class="iconify text-2xl text-white" data-icon="simple-icons:shopee"></span>
                        </a>
                        <!-- Tokopedia -->
                        <a href="belanja/" target="_blank" rel="noopener" aria-label="Tokopedia Mocafie" class="group flex items-center justify-center bg-gray-800 hover:bg-white p-2 rounded-xl transition-all h-10 w-10 shadow-md">
                            <img src="assets/img/tokopedia.svg" alt="Tokopedia" class="w-6 h-6 object-contain brightness-0 invert transition-all group-hover:brightness-100 group-hover:invert-0" />
                        </a>
                        <!-- TikTok -->
                        <a href="belanja/" target="_blank" rel="noopener" aria-label="TikTok Shop Mocafie" class="group flex items-center justify-center bg-gray-800 hover:bg-black p-2 rounded-xl transition-all h-10 w-10 shadow-md">
                            <span class="iconify text-xl text-white" data-icon="simple-icons:tiktok"></span>
                        </a>
                    </div>
                </div>

<!-- Social -->
<div>
  <h4 class="font-bold text-lg mb-4 text-green-400">Ikuti Kami</h4>

  <div class="flex gap-4">
    <!-- Instagram -->
    <a href="https://www.instagram.com/mocafie.id" target="_blank" rel="noopener"
       aria-label="Instagram Mocafie"
       class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white">
      <span class="iconify text-xl" data-icon="mdi:instagram"></span>
    </a>

    <!-- Facebook -->
    <a href="https://www.facebook.com/mocafie.id" target="_blank" rel="noopener"
       aria-label="Facebook Mocafie"
       class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white">
      <span class="iconify text-xl" data-icon="mdi:facebook"></span>
    </a>

    <!-- YouTube -->
    <a href="https://www.youtube.com/@mocafie" target="_blank" rel="noopener"
       aria-label="YouTube Mocafie"
       class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-primary transition-colors text-white">
      <span class="iconify text-xl" data-icon="mdi:youtube"></span>
    </a>
  </div>
</div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
                <p>&copy; 2026 Mocafie Indonesia. All rights reserved.</p>
                <div class="mt-4 md:mt-0">
                    <span class="text-xs">100% Sehat dari Alam</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="fixed inset-y-0 right-0 z-[110] w-full max-w-sm bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
            <h3 class="font-serif text-xl font-bold flex items-center gap-2">
                <span class="iconify text-primary" data-icon="mdi:cart-outline"></span>
                Keranjang Belanja
            </h3>
            <button onclick="toggleCartSidebar()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors focus:outline-none">
                <span class="iconify text-xl" data-icon="mdi:close"></span>
            </button>
        </div>
        
        <!-- Cart Items Container -->
        <div id="cartItemsContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Items injected by JS -->
            <div class="flex flex-col items-center justify-center h-full text-gray-400 gap-3">
                <span class="iconify text-5xl opacity-50" data-icon="mdi:cart-remove"></span>
                <p>Keranjang Anda masih kosong</p>
                <button onclick="toggleCartSidebar()" class="mt-2 text-sm text-primary hover:underline font-medium focus:outline-none">Lanjut Belanja</button>
            </div>
        </div>
        
        <!-- Footer / Checkout Info -->
        <div class="border-t border-gray-100 p-4 bg-surface">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-600 font-medium">Subtotal</span>
                <span id="cartSubtotal" class="font-bold text-lg text-gray-900">Rp 0</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">*Belum termasuk ongkos kirim</p>
            <button id="btnProceedCheckout" onclick="proceedToCheckout()" class="w-full bg-primary hover:bg-green-800 text-white font-bold py-3.5 rounded-xl shadow-md transition-all flex justify-center items-center gap-2 disabled:bg-gray-300 disabled:cursor-not-allowed" disabled>
                Checkout Sekarang
                <span class="iconify" data-icon="mdi:arrow-right"></span>
            </button>
        </div>
    </div>
    
    <!-- Cart Overlay -->
    <div id="cartOverlay" onclick="toggleCartSidebar()" class="fixed inset-0 bg-black/50 z-[105] hidden opacity-0 transition-opacity duration-300 w-full h-full cursor-pointer"></div>

    <!-- Mobile Menu Script -->
    <script>
        const btn = document.querySelector("button.mobile-menu-button");
        const menu = document.querySelector(".mobile-menu");
        const hamburgerIcon = document.querySelector(".hamburger-icon");
        const closeIcon = document.querySelector(".close-icon");

        function toggleMenu() {
            menu.classList.toggle("hidden");
            hamburgerIcon.classList.toggle("hidden");
            closeIcon.classList.toggle("hidden");
        }

        btn.addEventListener("click", () => {
            toggleMenu();
        });

        // Close mobile menu when clicking a link
        document.querySelectorAll('.mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                if (!menu.classList.contains('hidden')) {
                    toggleMenu();
                }
            });
        });
    </script>
    <!-- Midtrans Snap (Production) -->
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="Mid-client-rJAtcwovWynZcefa" defer></script>
    <script src="assets/js/script.js?v=20260319" defer></script>
</body>
</html>

<nav class="fixed w-full z-50 bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="#">
                        <img src="assets/img/logopng.webp" alt="Mocafie Logo" width="128" height="128" fetchpriority="high" class="h-32 w-auto">
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#home" class="text-gray-600 hover:text-primary transition-colors font-medium">Home</a>
                    <a href="#produk" class="text-gray-600 hover:text-primary transition-colors font-medium">Produk Kami</a>
                    <a href="#tentang" class="text-gray-600 hover:text-primary transition-colors font-medium">Tentang Kami</a>
                    <a href="#galeri" class="text-gray-600 hover:text-primary transition-colors font-medium">Galeri Kami</a>
                    <a href="#resep" class="text-gray-600 hover:text-primary transition-colors font-medium">Resep</a>
                    <a href="#kontak" class="text-gray-600 hover:text-primary transition-colors font-medium">Hubungi Kami</a>
                    <a href="belanja/" target="_blank" class="text-gray-600 hover:text-primary transition-colors font-medium">Marketplace</a>
                    
                    <!-- Desktop Cart Icon -->
                    <button onclick="toggleCartSidebar()" class="relative p-2 text-gray-600 hover:text-primary transition-colors focus:outline-none ml-2" aria-label="Keranjang Belanja">
                        <span class="iconify text-2xl" data-icon="mdi:cart-outline"></span>
                        <span id="cartCountDesktop" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full hidden">0</span>
                    </button>
                </div>

                <!-- Mobile button & cart -->
                <div class="md:hidden flex items-center gap-2">
                    <!-- Mobile Cart Icon -->
                    <button onclick="toggleCartSidebar()" class="relative p-2 text-gray-600 hover:text-primary transition-colors focus:outline-none" aria-label="Keranjang Belanja">
                        <span class="iconify text-2xl" data-icon="mdi:cart-outline"></span>
                        <span id="cartCountMobile" class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full hidden">0</span>
                    </button>
                    <!-- Hamburger / Menu Button -->
                    <button aria-label="Toggle mobile menu" class="mobile-menu-button p-2 rounded-md text-gray-600 hover:text-primary hover:bg-gray-100 focus:outline-none transition-colors duration-200">
                        <svg class="hamburger-icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="close-icon h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Dropdown Menu -->
        <div class="mobile-menu hidden md:hidden bg-white border-t absolute top-20 left-0 w-full shadow-lg z-50">
            <a href="#home" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Home</a>
            <a href="#produk" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Produk Kami</a>
            <a href="#tentang" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Tentang Kami</a>
            <a href="#galeri" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Galeri Kami</a>
            <a href="#resep" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Resep</a>
            <a href="#kontak" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Hubungi Kami</a>
            <a href="belanja/" target="_blank" class="block py-4 px-6 text-sm hover:bg-surface hover:text-primary border-b border-gray-50">Marketplace</a>
        </div>
    </nav>
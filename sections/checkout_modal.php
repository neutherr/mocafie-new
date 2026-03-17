<!-- Checkout Modal -->
    <div id="checkoutModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300 opacity-0 px-4">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 p-6 md:p-8 relative max-h-[90vh] overflow-y-auto">
            <!-- Close Button -->
            <button onclick="closeCheckoutModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-full p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="font-serif text-2xl font-bold text-gray-900 mb-2">Form Pemesanan</h3>
            <p class="text-gray-600 text-sm mb-6">Silakan lengkapi detail pesanan Anda untuk diproses ke WhatsApp Admin.</p>
            
            <form id="checkoutForm" class="space-y-4" onsubmit="processCheckout(event)">
                <!-- Order Summary (Injected by JS) -->
                <div class="bg-surface rounded-xl p-4 mb-4" id="coOrderSummary">
                    <p class="text-sm text-gray-500 text-center">Memuat pesanan...</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" id="coName" required class="w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WhatsApp *</label>
                        <input type="tel" id="coPhone" required class="w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="0812...">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (Untuk Resi & Invoice) *</label>
                    <input type="email" id="coEmail" required class="w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="email@contoh.com">
                </div>
                
                <div class="grid grid-cols-1 gap-4 relative">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Kecamatan / Kota Tujuan *</label>
                        <input type="text" id="coDestinationSearch" required class="w-full rounded-lg border-gray-300 border px-4 py-2.5 outline-none focus:ring-2 focus:border-primary transition-all" placeholder="Ketik nama kecamatan atau kota (minimal 3 huruf)..." autocomplete="off" onkeyup="searchDestination(this.value)">
                        <input type="hidden" id="coDestinationId" required>
                        <!-- Dropdown Hasil Pencarian -->
                        <div id="destinationResults" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto">
                            <!-- Hasil akan dimuat di sini oleh JS -->
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Cek Ongkir / Layanan Kurir JNE *</label>
                    <select id="coKurir" required disabled class="w-full rounded-lg border-gray-300 border px-4 py-2.5 bg-gray-50 disabled:opacity-50 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" onchange="updateTotalBayar()">
                        <option value="">Isi Lokasi Dahulu</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Detail Alamat Lengkap *</label>
                    <textarea id="coAddress" rows="2" required class="w-full rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Nama Jalan, RT/RW, Nomor Rumah, Patokan..."></textarea>
                </div>
                
                <!-- Cost Summary -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mt-6">
                    <div class="flex justify-between text-sm mb-2 text-gray-600">
                        <span>Total Harga Produk</span>
                        <span id="coTotalProdukLabel" class="font-medium">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-sm mb-3 text-gray-600 border-b border-gray-200 pb-3">
                        <span>Ongkos Kirim</span>
                        <span id="coOngkirLabel" class="font-medium text-primary">Menunggu Lokasi</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-gray-900 mt-3 pt-1">
                        <span>Total Pembayaran</span>
                        <span id="coTotalBayarLabel">Rp 0</span>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" id="btnBayarMidtrans" class="w-full bg-primary hover:bg-green-800 text-white font-bold py-3.5 rounded-xl shadow-md transition-all flex justify-center items-center gap-2">
                        <span>Bayar Sekarang (Midtrans)</span>
                        <span class="iconify" data-icon="mdi:shield-check"></span>
                    </button>
                    <!-- Fallback to WA if API fails or user prefers -->
                    <button type="button" onclick="checkoutViaWA()" class="w-full mt-3 bg-white text-gray-600 border border-gray-300 hover:bg-gray-50 font-semibold py-3 rounded-xl transition-all flex justify-center items-center gap-2">
                        <span class="iconify" data-icon="mdi:whatsapp"></span>
                        Beli Manual via WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>

    
<!-- Hubungi Kami -->
    <section id="kontak" class="py-20 bg-surface">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row">
                <!-- Form Area -->
                <div class="md:w-1/2 p-8 md:p-12">
                    <span class="text-primary font-semibold tracking-wider uppercase text-sm">Hubungi Kami</span>
                    <h2 class="font-serif text-3xl font-bold text-gray-900 mt-2 mb-6">Mulai Hidup Sehat Hari Ini</h2>
                    <p class="text-gray-600 mb-8">
                        Punya pertanyaan atau ingin memesan dalam jumlah besar? Isi formulir di bawah atau hubungi kami langsung via WhatsApp.
                    </p>

                    <form id="contactForm" onsubmit="event.preventDefault(); submitContactForm();" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="contactName" required class="w-full rounded-lg border-gray-300 border p-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Nama Anda">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. WhatsApp / Telepon</label>
                            <input type="tel" id="contactPhone" required class="w-full rounded-lg border-gray-300 border p-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="0812...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                            <textarea id="contactMessage" required rows="4" class="w-full rounded-lg border-gray-300 border p-3 focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="Tulis pesan Anda disini..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-bold py-3.5 rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                            <span class="iconify text-xl" data-icon="mdi:whatsapp"></span>
                            Kirim via WhatsApp
                        </button>
                    </form>


                </div>

                <!-- Info Area -->
                <div class="md:w-1/2 bg-primary p-8 md:p-12 text-white flex flex-col justify-between">
                    <div>
                        <h3 class="font-serif text-2xl font-bold mb-6">Informasi Kontak</h3>
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <span class="iconify text-xl" data-icon="mdi:map-marker"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Alamat</h4>
                                    <p class="text-white/80 opacity-90">
                                        <strong>Distributor Mocafie - Tepung Mocaf Premium</strong><br>
                                        Ruko Griya Sawangan Indah, Jl. H. Sulaiman No.11, RT.05/RW.03, Bedahan, Kec. Sawangan, Kota Depok, Jawa Barat 16519
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <span class="iconify text-xl" data-icon="mdi:email-outline"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Email</h4>
                                    <p class="text-white/80 opacity-90">marketing.mokafie@gmail.com</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <span class="iconify text-xl" data-icon="mdi:whatsapp"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">WhatsApp</h4>
                                    <p class="text-white/80 opacity-90">
                                        <a href="https://wa.me/6285188789052" target="_blank" class="hover:text-white hover:underline transition-all">+62 851-8878-9052</a>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="bg-white/20 p-2 rounded-lg">
                                    <span class="iconify" data-icon="mdi:clock-outline"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-lg">Jam Operasional</h4>
                                    <p class="text-white/80 opacity-90">Senin - Jumat: 08.00 - 17.00<br>Sabtu: 08.00 - 14.00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 rounded-xl overflow-hidden h-56 md:h-64 bg-white/10 border border-white/20">
                        <iframe
                            title="Lokasi Mocafie di Google Maps"
                            class="w-full h-full block"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3964.887254558004!2d106.76766497573514!3d-6.408430293582494!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69e95e5bcb24cf%3A0x3b5ce274d697a8ff!2sDistributor%20Mocafie%20-%20Tepung%20Mocaf%20Premium!5e0!3m2!1sen!2sid!4v1709489500000!5m2!1sen!2sid"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            style="border:0;"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </main>

    
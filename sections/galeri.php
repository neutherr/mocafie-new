<!-- Galeri Kami -->
<section id="galeri" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-12">
      <h2 class="font-serif text-3xl md:text-4xl font-bold text-gray-900 mb-4">Galeri Kami</h2>
      <div class="w-20 h-1 bg-accent mx-auto rounded-full mb-6"></div>
      <p class="text-gray-600 text-lg">Inspirasi olahan dan momen terbaik bersama Mocafie.</p>
    </div>

    <!-- Slider Wrapper -->
    <div class="relative group">
      <!-- Prev/Next -->
      <button
        id="galleryPrev"
        type="button"
        aria-label="Sebelumnya"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white text-primary p-3 rounded-full shadow-lg backdrop-blur-sm transition-all
               opacity-100 md:opacity-0 md:group-hover:opacity-100 disabled:opacity-30 focus:outline-none
               translate-x-2 md:translate-x-0"
      >
        <span class="iconify text-2xl" data-icon="mdi:chevron-left"></span>
      </button>

      <button
        id="galleryNext"
        type="button"
        aria-label="Berikutnya"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white text-primary p-3 rounded-full shadow-lg backdrop-blur-sm transition-all
               opacity-100 md:opacity-0 md:group-hover:opacity-100 disabled:opacity-30 focus:outline-none
               -translate-x-2 md:translate-x-0"
      >
        <span class="iconify text-2xl" data-icon="mdi:chevron-right"></span>
      </button>

      <!-- Viewport (NO overflow-x-auto) -->
      <div
        id="galleryViewport"
        class="overflow-hidden"
        style="touch-action: pan-y pinch-zoom;"
      >
        <!-- Track (translateX controlled by JS) -->
        <div
          id="galleryTrack"
          class="flex -mx-3 transition-transform duration-500 ease-out will-change-transform"
        >
          <!-- SLIDE 1: Jamur Crispy Image -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md">
              <div class="img-placeholder-container w-full h-full">
                <img
                  src="assets/img/jamurcrispy.jpeg"
                  alt="Jamur Crispy Mocafie"
                  class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                  loading="lazy"
                />
                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                  <p class="text-white font-bold text-lg">Jamur Crispy</p>
                  <p class="text-white/80 text-sm">Gurih & Renyah</p>
                </div>
              </div>
            </div>
          </div>

          <!-- SLIDE 2: Jamur Crispy Video -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md bg-gray-900">
              <div class="img-placeholder-container w-full h-full">
                <video class="w-full h-full object-cover" controls playsinline loop muted poster="assets/img/jamurcrispy.jpeg">
                  <source src="assets/img/jamurcrispyvideo.mp4" type="video/mp4" />
                  Your browser does not support the video tag.
                </video>
                <div class="absolute top-4 right-4 bg-black/50 text-white rounded-full p-2 backdrop-blur-sm pointer-events-none">
                  <span class="iconify text-xl" data-icon="mdi:video"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- SLIDE 3: Nastar Image -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md">
              <div class="img-placeholder-container w-full h-full">
                <img
                  src="assets/img/nastar.jpeg"
                  alt="Nastar Mocafie"
                  class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                  loading="lazy"
                />
                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                  <p class="text-white font-bold text-lg">Nastar Premium</p>
                  <p class="text-white/80 text-sm">Lembut & Lumer</p>
                </div>
              </div>
            </div>
          </div>

          <!-- SLIDE 4: Nastar Video -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md bg-gray-900">
              <div class="img-placeholder-container w-full h-full">
                <video class="w-full h-full object-cover" controls playsinline loop muted poster="assets/img/nastar.jpeg">
                  <source src="assets/img/nastarvideo.mp4" type="video/mp4" />
                  Your browser does not support the video tag.
                </video>
                <div class="absolute top-4 right-4 bg-black/50 text-white rounded-full p-2 backdrop-blur-sm pointer-events-none">
                  <span class="iconify text-xl" data-icon="mdi:video"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- SLIDE 5: Cake Image -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md">
              <div class="img-placeholder-container w-full h-full">
                <img
                  src="assets/img/cake.jpg"
                  alt="Nastar Mocafie"
                  class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                  loading="lazy"
                />
                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                  <p class="text-white font-bold text-lg">Cup Cake</p>
                  <p class="text-white/80 text-sm">Moist & Lumer</p>
                </div>
              </div>
            </div>
          </div>

          <!-- SLIDE 6: Noodle Image -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md">
              <div class="img-placeholder-container w-full h-full">
                <img
                  src="assets/img/noodle.jpg"
                  alt="Nastar Mocafie"
                  class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                  loading="lazy"
                />
                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                  <p class="text-white font-bold text-lg">Fried Noodle</p>
                  <p class="text-white/80 text-sm">Gurih & Lezat</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- SLIDE 7: Poci-Poci Pemalang Image -->
          <div class="gallery-slide w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
            <div class="gallery-item aspect-[3/4] rounded-2xl overflow-hidden relative shadow-md">
              <div class="img-placeholder-container w-full h-full">
                <img
                  src="assets/img/pocipoci.jpeg"
                  alt="Poci-Poci Pemalang Mocafie"
                  class="w-full h-full object-cover transition-transform duration-700 hover:scale-110"
                  loading="lazy"
                />
                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
                  <p class="text-white font-bold text-lg">Poci-Poci Pemalang</p>
                  <p class="text-white/80 text-sm">Kenyal & Legit</p>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Dots -->
      <div id="galleryDots" class="flex items-center justify-center gap-2 mt-6"></div>
    </div>
  </div>
</section>

    
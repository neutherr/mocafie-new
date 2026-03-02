document.addEventListener("DOMContentLoaded", () => {
    // ===== Scroll Animations (punya kamu, aman) =====
    const observerOptions = { root: null, rootMargin: "-50px 0px", threshold: 0.15 };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("appear");
                obs.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll(".animate-on-scroll").forEach((el) => observer.observe(el));

    // ===== Gallery Slider Only (PAKAI INI SAJA) =====
    const viewport = document.getElementById("galleryViewport");
    const track = document.getElementById("galleryTrack");
    const btnPrev = document.getElementById("galleryPrev");
    const btnNext = document.getElementById("galleryNext");
    const dotsWrap = document.getElementById("galleryDots");

    if (!viewport || !track || !btnPrev || !btnNext || !dotsWrap) return;

    let pageIndex = 0;
    let perView = 1;
    let pages = 1;
    let autoplayTimer = null;
    let isDragging = false;
    let startX = 0;
    let currentTranslate = 0;
    let lastTranslate = 0;
    let lockByVideo = false;

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const getPerView = () => {
        const w = window.innerWidth;
        if (w >= 1024) return 3;
        if (w >= 640) return 2;
        return 1;
    };

    const slidesAll = () => Array.from(track.querySelectorAll(".gallery-slide"));

    const removeClones = () => {
        track.querySelectorAll('[data-clone="true"]').forEach((n) => n.remove());
    };

    const addClonesToFillLastPage = () => {
        const slides = slidesAll().filter((n) => n.dataset.clone !== "true");
        const remainder = slides.length % perView;
        if (remainder === 0) return;

        const need = perView - remainder;
        for (let i = 0; i < need; i++) {
            const clone = slides[i].cloneNode(true);
            clone.dataset.clone = "true";
            track.appendChild(clone);
        }
    };

    const calcPages = () => {
        const total = slidesAll().length;
        pages = Math.max(1, Math.ceil(total / perView));
    };

    const setTransition = (enabled) => {
        if (enabled) track.classList.remove("!transition-none");
        else track.classList.add("!transition-none");
    };

    const renderDots = () => {
        dotsWrap.innerHTML = "";
        for (let i = 0; i < pages; i++) {
            const b = document.createElement("button");
            b.type = "button";
            b.className =
                "w-2.5 h-2.5 p-3 box-content bg-clip-content rounded-full transition-all " +
                (i === pageIndex ? "bg-primary scale-110" : "bg-gray-300 hover:bg-gray-400");
            b.setAttribute("aria-label", `Ke slide ${i + 1}`);
            if (i === pageIndex) b.setAttribute("aria-current", "true");

            b.addEventListener("click", () => {
                stopAutoplay();
                goTo(i, true);
                resumeAutoplaySoon();
            });

            dotsWrap.appendChild(b);
        }
    };

    const updateDots = () => {
        Array.from(dotsWrap.children).forEach((d, i) => {
            d.className =
                "w-2.5 h-2.5 p-3 box-content bg-clip-content rounded-full transition-all " +
                (i === pageIndex ? "bg-primary scale-110" : "bg-gray-300 hover:bg-gray-400");
            if (i === pageIndex) d.setAttribute("aria-current", "true");
            else d.removeAttribute("aria-current");
        });
    };

    const goTo = (idx, animate = true) => {
        if (pages <= 1) idx = 0;

        if (idx < 0) idx = pages - 1;
        if (idx >= pages) idx = 0;
        pageIndex = idx;

        setTransition(animate && !prefersReducedMotion);

        const offset = pageIndex * viewport.clientWidth;
        currentTranslate = -offset;
        lastTranslate = currentTranslate;
        track.style.transform = `translate3d(${currentTranslate}px,0,0)`;

        updateDots();
    };

    const next = () => goTo(pageIndex + 1, true);
    const prev = () => goTo(pageIndex - 1, true);

    const stopAutoplay = () => {
        if (autoplayTimer) clearTimeout(autoplayTimer);
        autoplayTimer = null;
    };

    const startAutoplay = () => {
        stopAutoplay();
        if (prefersReducedMotion) return;
        if (pages <= 1) return;
        if (lockByVideo) return;

        autoplayTimer = setTimeout(() => {
            next();
            startAutoplay();
        }, 3500);
    };

    const resumeAutoplaySoon = () => {
        stopAutoplay();
        if (prefersReducedMotion) return;
        if (lockByVideo) return;
        setTimeout(startAutoplay, 2500);
    };

    btnNext.addEventListener("click", () => {
        stopAutoplay();
        next();
        resumeAutoplaySoon();
    });

    btnPrev.addEventListener("click", () => {
        stopAutoplay();
        prev();
        resumeAutoplaySoon();
    });

    viewport.addEventListener("mouseenter", stopAutoplay);
    viewport.addEventListener("mouseleave", startAutoplay);

    const onPointerDown = (e) => {
        if (e.target && (e.target.tagName === "VIDEO" || e.target.closest("video"))) return;
        isDragging = true;
        startX = e.clientX;
        setTransition(false);
        stopAutoplay();
        viewport.setPointerCapture?.(e.pointerId);
    };

    const onPointerMove = (e) => {
        if (!isDragging) return;
        const dx = e.clientX - startX;
        currentTranslate = lastTranslate + dx;
        track.style.transform = `translate3d(${currentTranslate}px,0,0)`;
    };

    const onPointerUp = () => {
        if (!isDragging) return;
        isDragging = false;

        const movedBy = currentTranslate - lastTranslate;
        const threshold = viewport.clientWidth * 0.15;

        setTransition(true);

        if (movedBy < -threshold) next();
        else if (movedBy > threshold) prev();
        else goTo(pageIndex, true);

        resumeAutoplaySoon();
    };

    viewport.addEventListener("pointerdown", onPointerDown);
    viewport.addEventListener("pointermove", onPointerMove);
    viewport.addEventListener("pointerup", onPointerUp);
    viewport.addEventListener("pointercancel", onPointerUp);

    const bindVideoEvents = () => {
        track.querySelectorAll("video").forEach((v) => {
            v.addEventListener("play", () => {
                lockByVideo = true;
                stopAutoplay();
            });
            v.addEventListener("pause", () => {
                lockByVideo = false;
                resumeAutoplaySoon();
            });
            v.addEventListener("ended", () => {
                lockByVideo = false;
                resumeAutoplaySoon();
            });
        });
    };

    const section = document.getElementById("galeri");
    const visObs = new IntersectionObserver(
        (entries) => {
            entries.forEach((en) => {
                if (!en.isIntersecting) stopAutoplay();
                else startAutoplay();
            });
        },
        { threshold: 0.2 }
    );
    if (section) visObs.observe(section);

    const rebuild = () => {
        removeClones();
        perView = getPerView();
        addClonesToFillLastPage();
        calcPages();

        if (pageIndex >= pages) pageIndex = 0;

        renderDots();
        goTo(pageIndex, false);
        bindVideoEvents();
        startAutoplay();
    };

    window.addEventListener("resize", () => {
        stopAutoplay();
        rebuild();
    });

    rebuild();

    // ===== Testimonial Slider =====
    const tsSlider = document.getElementById("testimoniSlider");
    const tsPrev = document.getElementById("tsPrev");
    const tsNext = document.getElementById("tsNext");
    const tsDotsWrap = document.getElementById("tsDots");

    if (tsSlider && tsPrev && tsNext && tsDotsWrap) {
        const tsSlides = Array.from(tsSlider.children);
        let tsCurrentIndex = 0;

        // Create dots
        tsDotsWrap.innerHTML = "";
        tsSlides.forEach((_, i) => {
            const dot = document.createElement("div");
            dot.className = `w-3 h-3 p-3 box-content bg-clip-content rounded-full cursor-pointer transition-all ${i === 0 ? 'bg-primary scale-110' : 'bg-gray-300 hover:bg-gray-400'}`;
            dot.addEventListener("click", () => {
                tsCurrentIndex = i;
                updateTsSlider();
            });
            tsDotsWrap.appendChild(dot);
        });

        const updateTsSlider = () => {
            if (tsCurrentIndex < 0) tsCurrentIndex = 0;
            if (tsCurrentIndex >= tsSlides.length) tsCurrentIndex = tsSlides.length - 1;

            const slide = tsSlides[tsCurrentIndex];
            // Center the slide in view
            const scrollPos = slide.offsetLeft - tsSlider.offsetLeft - (tsSlider.clientWidth - slide.clientWidth) / 2;
            tsSlider.scrollTo({
                left: scrollPos,
                behavior: 'smooth'
            });

            updateTsDots();
        };

        const updateTsDots = () => {
            Array.from(tsDotsWrap.children).forEach((dot, i) => {
                if (i === tsCurrentIndex) {
                    dot.className = "w-3 h-3 p-3 box-content bg-clip-content rounded-full bg-primary scale-110 transition-all cursor-pointer";
                } else {
                    dot.className = "w-3 h-3 p-3 box-content bg-clip-content rounded-full bg-gray-300 hover:bg-gray-400 cursor-pointer transition-all";
                }
            });
        }

        tsPrev.addEventListener("click", () => {
            tsCurrentIndex--;
            updateTsSlider();
        });

        tsNext.addEventListener("click", () => {
            tsCurrentIndex++;
            updateTsSlider();
        });

        // Update active dot on manual scroll with debounce
        let tsScrollTimeout;
        tsSlider.addEventListener("scroll", () => {
            clearTimeout(tsScrollTimeout);
            tsScrollTimeout = setTimeout(() => {
                const centerPos = tsSlider.scrollLeft + tsSlider.clientWidth / 2;
                let closestIndex = 0;
                let minDiff = Infinity;

                tsSlides.forEach((slide, i) => {
                    const slideCenter = slide.offsetLeft - tsSlider.offsetLeft + slide.clientWidth / 2;
                    const diff = Math.abs(centerPos - slideCenter);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closestIndex = i;
                    }
                });

                if (closestIndex !== tsCurrentIndex) {
                    tsCurrentIndex = closestIndex;
                    updateTsDots();
                }
            }, 50);
        });
    }

    // ===== Recipe Slider =====
    const rsSlider = document.getElementById("recipeSlider");
    const rsPrev = document.getElementById("rsPrev");
    const rsNext = document.getElementById("rsNext");
    const rsDotsWrap = document.getElementById("rsDots");

    if (rsSlider && rsPrev && rsNext && rsDotsWrap) {
        const rsSlides = Array.from(rsSlider.children);
        let rsCurrentIndex = 0;

        // Create dots
        rsDotsWrap.innerHTML = "";
        rsSlides.forEach((_, i) => {
            const dot = document.createElement("div");
            dot.className = `w-3 h-3 p-3 box-content bg-clip-content rounded-full cursor-pointer transition-all ${i === 0 ? 'bg-primary scale-110' : 'bg-gray-300 hover:bg-gray-400'}`;
            dot.addEventListener("click", () => {
                rsCurrentIndex = i;
                updateRsSlider();
            });
            rsDotsWrap.appendChild(dot);
        });

        const updateRsSlider = () => {
            if (rsCurrentIndex < 0) rsCurrentIndex = 0;
            if (rsCurrentIndex >= rsSlides.length) rsCurrentIndex = rsSlides.length - 1;

            const slide = rsSlides[rsCurrentIndex];
            // Center the slide in view
            const scrollPos = slide.offsetLeft - rsSlider.offsetLeft - (rsSlider.clientWidth - slide.clientWidth) / 2;
            rsSlider.scrollTo({
                left: scrollPos,
                behavior: 'smooth'
            });

            updateRsDots();
        };

        const updateRsDots = () => {
            Array.from(rsDotsWrap.children).forEach((dot, i) => {
                if (i === rsCurrentIndex) {
                    dot.className = "w-3 h-3 p-3 box-content bg-clip-content rounded-full bg-primary scale-110 transition-all cursor-pointer";
                } else {
                    dot.className = "w-3 h-3 p-3 box-content bg-clip-content rounded-full bg-gray-300 hover:bg-gray-400 cursor-pointer transition-all";
                }
            });
        }

        rsPrev.addEventListener("click", () => {
            rsCurrentIndex--;
            updateRsSlider();
        });

        rsNext.addEventListener("click", () => {
            rsCurrentIndex++;
            updateRsSlider();
        });

        // Update active dot on manual scroll with debounce
        let rsScrollTimeout;
        rsSlider.addEventListener("scroll", () => {
            clearTimeout(rsScrollTimeout);
            rsScrollTimeout = setTimeout(() => {
                const centerPos = rsSlider.scrollLeft + rsSlider.clientWidth / 2;
                let closestIndex = 0;
                let minDiff = Infinity;

                rsSlides.forEach((slide, i) => {
                    const slideCenter = slide.offsetLeft - rsSlider.offsetLeft + slide.clientWidth / 2;
                    const diff = Math.abs(centerPos - slideCenter);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closestIndex = i;
                    }
                });

                if (closestIndex !== rsCurrentIndex) {
                    rsCurrentIndex = closestIndex;
                    updateRsDots();
                }
            }, 50);
        });
    }
});


        // Checkout Modal Logic
        const checkoutModal = document.getElementById('checkoutModal');
        const modalContent = checkoutModal.querySelector('div.bg-white');

        // Fetch API settings for regions
        const apiBps = 'https://www.emsifa.com/api-wilayah-indonesia/api';

        // Load Provinces when modal opens
        async function loadProvinsi() {
            try {
                const response = await fetch(`${apiBps}/provinces.json`);
                const data = await response.json();
                const select = document.getElementById('coProvinsi');
                
                // Clear existing options except first
                select.innerHTML = '<option value="">Pilih Provinsi</option>';
                
                data.forEach(prov => {
                    const option = document.createElement('option');
                    option.value = prov.id;
                    option.textContent = prov.name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading provinces:', error);
            }
        }

        // Load Kabupaten/Kota based on selected Province
        async function loadKabupaten(provinsiId) {
            const selectKabupaten = document.getElementById('coKabupaten');
            const selectKecamatan = document.getElementById('coKecamatan');
            
            // Reset and disable cascading selects
            selectKabupaten.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
            selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
            selectKabupaten.disabled = true;
            selectKabupaten.classList.add('bg-gray-50');
            selectKecamatan.disabled = true;
            selectKecamatan.classList.add('bg-gray-50');

            if (!provinsiId) return;

            try {
                selectKabupaten.disabled = false;
                selectKabupaten.classList.remove('bg-gray-50');
                
                const response = await fetch(`${apiBps}/regencies/${provinsiId}.json`);
                const data = await response.json();
                
                data.forEach(kab => {
                    const option = document.createElement('option');
                    option.value = kab.id;
                    option.textContent = kab.name;
                    selectKabupaten.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading regencies:', error);
            }
        }

        // Load Kecamatan based on selected Kabupaten/Kota
        async function loadKecamatan(kabupatenId) {
            const selectKecamatan = document.getElementById('coKecamatan');
            
            // Reset
            selectKecamatan.innerHTML = '<option value="">Pilih Kecamatan</option>';
            selectKecamatan.disabled = true;
            selectKecamatan.classList.add('bg-gray-50');

            if (!kabupatenId) return;

            try {
                selectKecamatan.disabled = false;
                selectKecamatan.classList.remove('bg-gray-50');
                
                const response = await fetch(`${apiBps}/districts/${kabupatenId}.json`);
                const data = await response.json();
                
                data.forEach(kec => {
                    const option = document.createElement('option');
                    option.value = kec.id;
                    option.textContent = kec.name;
                    selectKecamatan.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading districts:', error);
            }
        }


        function openCheckoutModal() {
            checkoutModal.classList.remove('hidden');
            // Trigger reflow
            void checkoutModal.offsetWidth;
            checkoutModal.classList.remove('opacity-0');
            checkoutModal.classList.add('opacity-100');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            // Load provinces if not already loaded
            if (document.getElementById('coProvinsi').options.length <= 1) {
                loadProvinsi();
            }
        }

        function closeCheckoutModal() {
            checkoutModal.classList.remove('opacity-100');
            checkoutModal.classList.add('opacity-0');
            modalContent.classList.remove('scale-100');
            modalContent.classList.add('scale-95');
            // Restore body scroll
            document.body.style.overflow = '';
            setTimeout(() => {
                checkoutModal.classList.add('hidden');
            }, 300); // match duration-300
        }

        // Close when clicking outside content
        checkoutModal.addEventListener('click', function(e) {
            if (e.target === checkoutModal) {
                closeCheckoutModal();
            }
        });

        function updateQty(change) {
            const qtyInput = document.getElementById('coQty');
            let newQty = parseInt(qtyInput.value) + change;
            if(newQty < 1) newQty = 1;
            qtyInput.value = newQty;
        }

        function processCheckout(e) {
            e.preventDefault();
            
            const name = document.getElementById('coName').value.trim();
            const phone = document.getElementById('coPhone').value.trim();
            const size = document.querySelector('input[name="coSize"]:checked').value;
            const qty = document.getElementById('coQty').value;
            
            // Get text text of selected locations
            const provSelect = document.getElementById('coProvinsi');
            const kabSelect = document.getElementById('coKabupaten');
            const kecSelect = document.getElementById('coKecamatan');
            
            const provText = provSelect.options[provSelect.selectedIndex].text;
            const kabText = kabSelect.options[kabSelect.selectedIndex].text;
            const kecText = kecSelect.options[kecSelect.selectedIndex].text;
            
            const detailAddress = document.getElementById('coAddress').value.trim();
            const notes = document.getElementById('coNotes').value.trim();
            
            const adminWA = "6285188789052"; // Sesuai dengan form contact
            
            const message = `Halo Admin Mocafie, saya ingin memesan Tepung Mocaf:

*--- DATA PEMESAN ---*
Nama: ${name}
No. WA: ${phone}

*--- DETAIL PESANAN ---*
Produk: Tepung Mocafie
Ukuran: *${size}*
Jumlah: *${qty} Pcs*

*--- ALAMAT PENGIRIMAN ---*
${detailAddress}
Kec. ${kecText}, ${kabText}
Prov. ${provText}

${notes ? `*--- CATATAN ---*\n${notes}` : ''}

Mohon info total biaya + ongkir serta rekening pembayarannya ya. Terima kasih!`;

            const encodedMessage = encodeURIComponent(message);
            const waLink = `https://wa.me/${adminWA}?text=${encodedMessage}`;
            
            window.open(waLink, '_blank');
            closeCheckoutModal();
        }

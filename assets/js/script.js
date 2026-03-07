document.addEventListener("DOMContentLoaded", () => {
    // ===== Scroll Reveal Animations =====
    // Trigger a bit earlier (-30px) and at a lower threshold (8%)
    // so tall cards/sections aren't missed on smaller viewports.
    const observerOptions = {
        root: null,
        rootMargin: "-30px 0px",
        threshold: 0.08,
    };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const el = entry.target;

            // Auto-stagger siblings inside the same parent
            // Only apply if the element doesn't already have an explicit delay class
            const hasExplicitDelay = ["delay-100", "delay-200", "delay-300", "delay-400", "delay-500"]
                .some(c => el.classList.contains(c));

            if (!hasExplicitDelay) {
                const siblings = Array.from(el.parentElement?.querySelectorAll(".animate-on-scroll") ?? []);
                const idx = siblings.indexOf(el);
                if (idx > 0) {
                    // 60ms stagger per sibling, capped at 400ms
                    el.style.transitionDelay = Math.min(idx * 60, 400) + "ms";
                }
            }

            el.classList.add("appear");
            obs.unobserve(el);
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


// ===== Checkout Modal Logic =====
// Fungsi didefinisikan di global scope menggunakan function declarations.
// DOM element diambil saat dipanggil (lazy) - tidak bergantung pada timing apapun.

const _CO_API = "https://www.emsifa.com/api-wilayah-indonesia/api";

let currentTotalProduk = 0;
let currentOngkir = 0;

function openCheckoutModal() {
    const modal = document.getElementById("checkoutModal");
    const content = modal && modal.querySelector("div.bg-white");
    if (!modal || !content) return;

    // Populate Order Summary
    const summaryContainer = document.getElementById("coOrderSummary");
    const totalProdukLabel = document.getElementById("coTotalProdukLabel");

    if (cartItems.length === 0) {
        alert("Keranjang Anda kosong!");
        return;
    }

    let summaryHtml = '<ul class="space-y-2">';
    currentTotalProduk = 0;

    cartItems.forEach(item => {
        const itemTotal = item.price * item.qty;
        currentTotalProduk += itemTotal;
        summaryHtml += `
            <li class="flex justify-between text-sm">
                <span class="text-gray-700">${item.qty}x ${item.name}</span>
                <span class="font-medium text-gray-900">Rp ${itemTotal.toLocaleString('id-ID')}</span>
            </li>
        `;
    });
    summaryHtml += '</ul>';

    if (summaryContainer) summaryContainer.innerHTML = summaryHtml;
    if (totalProdukLabel) totalProdukLabel.textContent = `Rp ${currentTotalProduk.toLocaleString('id-ID')}`;

    // Reset Ongkir & Kurir
    currentOngkir = 0;
    const kurirSelect = document.getElementById("coKurir");
    if (kurirSelect) {
        kurirSelect.innerHTML = '<option value="">Isi Lokasi Dahulu</option>';
        kurirSelect.disabled = true;
    }
    updateTotalBayar();

    // Tampilkan Modal
    modal.classList.remove("hidden");
    void modal.offsetWidth;
    modal.classList.remove("opacity-0");
    modal.classList.add("opacity-100");
    content.classList.remove("scale-95");
    content.classList.add("scale-100");
    document.body.style.overflow = "hidden";

    const prov = document.getElementById("coProvinsi");
    if (prov && prov.options.length <= 1) loadProvinsi();

    if (!modal._coInit) {
        modal._coInit = true;
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeCheckoutModal();
        });
    }
}

function closeCheckoutModal() {
    const modal = document.getElementById("checkoutModal");
    const content = modal && modal.querySelector("div.bg-white");
    if (!modal || !content) return;

    modal.classList.remove("opacity-100");
    modal.classList.add("opacity-0");
    content.classList.remove("scale-100");
    content.classList.add("scale-95");
    document.body.style.overflow = "";

    setTimeout(() => modal.classList.add("hidden"), 300);
}

// function updateQty removed as it's handled by cart logic now

let searchTimeout;

function searchDestination(keyword) {
    const resultsContainer = document.getElementById("destinationResults");

    // Clear previous timeout
    clearTimeout(searchTimeout);

    if (!keyword || keyword.length < 3) {
        resultsContainer.classList.add("hidden");
        return;
    }

    // Debounce for 500ms
    searchTimeout = setTimeout(async () => {
        try {
            resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-sm">Mencari...</div>';
            resultsContainer.classList.remove("hidden");

            const response = await fetch(`api/rajaongkir_destination.php?search=${encodeURIComponent(keyword)}`);
            const json = await response.json();

            resultsContainer.innerHTML = '';

            if (json.status === "error") {
                resultsContainer.innerHTML = `<div class="p-3 text-red-500 text-sm">${json.message}</div>`;
                return;
            }

            if (json.meta && json.meta.code === 200 && json.data && json.data.length > 0) {
                json.data.forEach(item => {
                    const div = document.createElement("div");
                    div.className = "p-3 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-0";
                    div.textContent = item.label;
                    div.onclick = () => selectDestination(item.id, item.label);
                    resultsContainer.appendChild(div);
                });
            } else {
                resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-sm">Lokasi tidak ditemukan.</div>';
            }
        } catch (error) {
            console.error("Search API Error:", error);
            resultsContainer.innerHTML = '<div class="p-3 text-red-500 text-sm">Koneksi API Gagal. Pastikan tes di Hostinger (PHP).</div>';
        }
    }, 500);
}

function selectDestination(id, label) {
    document.getElementById("coDestinationSearch").value = label;
    document.getElementById("coDestinationId").value = id;
    document.getElementById("destinationResults").classList.add("hidden");

    // Auto calculate ongkir
    cekOngkir();
}

// Menutup search resutls jika di klik di luar
document.addEventListener("click", function (event) {
    const searchInput = document.getElementById("coDestinationSearch");
    const resultsContainer = document.getElementById("destinationResults");
    if (searchInput && resultsContainer && !searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
        resultsContainer.classList.add("hidden");
    }
});

async function cekOngkir() {
    const kurirSelect = document.getElementById("coKurir");
    const idKotaTujuan = document.getElementById("coDestinationId")?.value;

    if (!idKotaTujuan) return;

    // Hitung total berat dari keranjang (default: 1 produk = 1000 gram)
    let totalWeight = 0;
    cartItems.forEach(item => {
        totalWeight += (item.weight || 1000) * item.qty;
    });
    if (totalWeight === 0) totalWeight = 1000;

    kurirSelect.innerHTML = `<option value="">Memuat layanan kurir (${totalWeight / 1000} Kg)...</option>`;
    kurirSelect.disabled = true;

    // ID Kota Pengirim Komerce sesuai pengaturan (16519)
    const originKota = 16519;

    try {
        const response = await fetch('api/rajaongkir_cost.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                origin: originKota,
                destination: idKotaTujuan,
                weight: totalWeight,
                courier: 'jne'
            })
        });

        const data = await response.json();
        kurirSelect.innerHTML = `<option value="">Pilih Layanan JNE (Total Berat: ${totalWeight / 1000} Kg)</option>`;

        if (data && data.meta && data.meta.code === 200 && data.data) {
            data.data.forEach(c => {
                const serviceName = c.service;
                const costValue = c.cost;
                const etd = c.etd;

                // Pemetaan nama awam agar dimengerti pembeli
                let friendlyName = serviceName;
                const s = serviceName.toUpperCase();

                // --- FILTER: Hanya tampilkan Reguler dan Kargo JTR Dasar ---
                if (!(s === 'CTC' || s === 'REG' || s === 'JTR')) {
                    return; // Lewati / sembunyikan layanan lain (JTR>130, YES, OKE, SPS, dsb)
                }

                if (s === 'JTR') friendlyName = 'Kargo JTR';
                else if (s === 'CTC' || s === 'REG') friendlyName = 'Reguler';

                const option = document.createElement("option");
                // Simpan harganya di value (dipisah dash)
                option.value = `JNE-${serviceName}-${costValue}`;

                // Ubah tulisan "day" jadi "Hari"
                let etdText = etd ? etd.replace('day', 'Hari') : '';
                let labelWaktu = etdText ? `(${etdText})` : '';

                option.textContent = `JNE ${friendlyName} ${labelWaktu} - Rp ${costValue.toLocaleString('id-ID')}`;
                kurirSelect.appendChild(option);
            });
            kurirSelect.disabled = false;
        } else {
            kurirSelect.innerHTML = '<option value="">Gagal mendapatkan api (Cek API Key)</option>';
            console.log("RajaOngkir Error:", data);
        }
    } catch (err) {
        console.error("Fetch cost error:", err);
        kurirSelect.innerHTML = '<option value="">Error mendapatkan ongkir</option>';
    }

    currentOngkir = 0;
    updateTotalBayar();
}

function updateTotalBayar() {
    const kurirSelect = document.getElementById("coKurir");
    const ongkirLabel = document.getElementById("coOngkirLabel");
    const totalLabel = document.getElementById("coTotalBayarLabel");
    const btnBayar = document.getElementById("btnBayarMidtrans");

    if (kurirSelect && kurirSelect.value) {
        // Ekstrak ongkir dari value dummy (format: KODE-15000)
        const parts = kurirSelect.value.split('-');
        currentOngkir = parseInt(parts[parts.length - 1], 10);
        if (ongkirLabel) ongkirLabel.textContent = `Rp ${currentOngkir.toLocaleString('id-ID')}`;
    } else {
        currentOngkir = 0;
        if (ongkirLabel) ongkirLabel.textContent = "Pilih Kurir Dahulu";
    }

    const grandTotal = currentTotalProduk + currentOngkir;
    if (totalLabel) totalLabel.textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;

    if (btnBayar) {
        btnBayar.disabled = !(currentOngkir > 0 && currentTotalProduk > 0);
    }
}

async function processCheckout(e) {
    e.preventDefault();

    const btnSubmit = document.getElementById("btnBayarMidtrans");
    const originalText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = `<span class="iconify animate-spin" data-icon="mdi:loading"></span> Memproses...`;

    const payload = {
        name: document.getElementById("coName")?.value.trim(),
        phone: document.getElementById("coPhone")?.value.trim(),
        email: document.getElementById("coEmail")?.value.trim(),
        destination: document.getElementById("coDestinationSearch")?.value.trim(),
        address: document.getElementById("coAddress")?.value.trim(),
        notes: document.getElementById("coNotes")?.value.trim(),
        courierName: document.getElementById("coKurir")?.options[document.getElementById("coKurir").selectedIndex]?.text || "JNE",
        shippingCost: currentOngkir,
        cartItems: cartItems
    };

    try {
        const response = await fetch('api/checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.status === 'success' && data.token) {
            // Panggil Snap Midtrans
            window.snap.pay(data.token, {
                onSuccess: function (result) {
                    alert("Pembayaran Berhasil! Pesanan Anda akan segera diproses.");
                    cartItems = [];
                    saveCart();
                    window.location.reload();
                },
                onPending: function (result) {
                    alert("Menunggu Pembayaran. Silakan selesaikan pembayaran Anda.");
                },
                onError: function (result) {
                    alert("Pembayaran Gagal. Silakan coba lagi.");
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                },
                onClose: function () {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = originalText;
                }
            });
        } else {
            alert("Gagal memproses checkout: " + (data.message || "Error tidak diketahui"));
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        }
    } catch (err) {
        console.error("Checkout Request Error:", err);
        alert("Terjadi kesalahan koneksi saat memproses checkout.");
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalText;
    }
}

function checkoutViaWA() {
    const name = document.getElementById("coName")?.value.trim() || "";
    const phone = document.getElementById("coPhone")?.value.trim() || "";
    const email = document.getElementById("coEmail")?.value.trim() || "";

    const destText = document.getElementById("coDestinationSearch")?.value.trim() || "";
    const detailAddress = document.getElementById("coAddress")?.value.trim() || "";
    const notes = document.getElementById("coNotes")?.value.trim() || "";
    const kurir = document.getElementById("coKurir")?.options[document.getElementById("coKurir").selectedIndex]?.text || "Belum dipilih";

    let orderList = "";
    cartItems.forEach(item => {
        orderList += `- ${item.qty}x ${item.name} (Rp ${(item.price * item.qty).toLocaleString('id-ID')})\n`;
    });

    const adminWA = "6285188789052";

    const message = `Halo Admin Mocafie, saya ingin memesan Tepung Mocaf:\n\n*--- DATA PEMESAN ---*\nNama: ${name}\nNo. WA: ${phone}\nEmail: ${email}\n\n*--- DETAIL PESANAN ---*\n${orderList}\n*--- ALAMAT PENGIRIMAN ---*\n${detailAddress}\nWilayah: ${destText}\nKurir: ${kurir}\n${notes ? `\n*--- CATATAN ---*\n${notes}` : ""}\n\nMohon info rekening pembayarannya ya. Terima kasih!`;

    window.open(`https://wa.me/${adminWA}?text=${encodeURIComponent(message)}`, "_blank");
    closeCheckoutModal();
}


// ===== Shopping Cart Logic =====
let cartItems = [];

function loadCart() {
    const saved = localStorage.getItem('mocafie_cart');
    if (saved) {
        try {
            cartItems = JSON.parse(saved);
        } catch (e) {
            cartItems = [];
        }
    }
    renderCart();
}

function saveCart() {
    localStorage.setItem('mocafie_cart', JSON.stringify(cartItems));
    renderCart();
}

function addToCart(id, name, price, weight, image) {
    const existing = cartItems.find(item => item.id === id);
    if (existing) {
        existing.qty += 1;
    } else {
        cartItems.push({ id, name, price, weight, image, qty: 1 });
    }
    saveCart();

    // Langsung buka sidebar setelah menambah
    toggleCartSidebar();
}

function updateCartQuantity(id, change) {
    const item = cartItems.find(i => i.id === id);
    if (item) {
        item.qty += change;
        if (item.qty <= 0) {
            removeFromCart(id);
            return;
        }
        saveCart();
    }
}

function removeFromCart(id) {
    cartItems = cartItems.filter(item => item.id !== id);
    saveCart();
}

function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    const badgeDesktop = document.getElementById('cartCountDesktop');
    const badgeMobile = document.getElementById('cartCountMobile');
    const subtotalEl = document.getElementById('cartSubtotal');
    const btnCheckout = document.getElementById('btnProceedCheckout');

    if (!container) return;

    let totalQty = 0;
    let subtotal = 0;

    if (cartItems.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400 gap-3">
                <span class="iconify text-5xl opacity-50" data-icon="mdi:cart-remove"></span>
                <p>Keranjang Anda masih kosong</p>
                <button onclick="toggleCartSidebar()" class="mt-2 text-sm text-primary hover:underline font-medium focus:outline-none">Lanjut Belanja</button>
            </div>
        `;
        if (btnCheckout) btnCheckout.disabled = true;
    } else {
        let html = '';
        cartItems.forEach(item => {
            totalQty += item.qty;
            subtotal += (item.price * item.qty);

            html += `
                <div class="flex gap-4 p-3 bg-white border border-gray-100 rounded-xl shadow-sm relative animate-fade-in-up">
                    <img src="${item.image}" alt="${item.name}" class="w-20 h-20 object-cover rounded-lg bg-gray-50 border border-gray-100">
                    <div class="flex-1 flex flex-col justify-between py-1">
                        <div class="pr-6">
                            <h4 class="font-bold text-sm text-gray-900 leading-tight">${item.name}</h4>
                            <p class="text-primary font-bold text-sm mt-1">Rp ${item.price.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200">
                                <button onclick="updateCartQuantity('${item.id}', -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-primary transition-colors focus:outline-none">-</button>
                                <span class="w-8 text-center text-sm font-semibold">${item.qty}</span>
                                <button onclick="updateCartQuantity('${item.id}', 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-primary transition-colors focus:outline-none">+</button>
                            </div>
                        </div>
                    </div>
                    <button onclick="removeFromCart('${item.id}')" class="absolute top-2 right-2 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-colors focus:outline-none" aria-label="Hapus Item">
                        <span class="iconify text-lg" data-icon="mdi:trash-can-outline"></span>
                    </button>
                </div>
            `;
        });
        container.innerHTML = html;
        if (btnCheckout) btnCheckout.disabled = false;
    }

    // Update Badges
    [badgeDesktop, badgeMobile].forEach(badge => {
        if (badge) {
            badge.textContent = totalQty;
            if (totalQty > 0) badge.classList.remove('hidden');
            else badge.classList.add('hidden');
        }
    });

    // Update Subtotal
    if (subtotalEl) {
        subtotalEl.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    }
}

function toggleCartSidebar() {
    const sidebar = document.getElementById('cartSidebar');
    const overlay = document.getElementById('cartOverlay');

    if (!sidebar || !overlay) return;

    const isClosed = sidebar.classList.contains('translate-x-full');

    if (isClosed) {
        sidebar.classList.remove('translate-x-full');
        overlay.classList.remove('hidden');
        // small delay for transition
        setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        document.body.style.overflow = "hidden";
        renderCart(); // render on open to ensure fresh state
    } else {
        sidebar.classList.add('translate-x-full');
        overlay.classList.add('opacity-0');
        setTimeout(() => overlay.classList.add('hidden'), 300);
        document.body.style.overflow = "";
    }
}

function proceedToCheckout() {
    if (cartItems.length === 0) return;
    toggleCartSidebar(); // close sidebar
    setTimeout(() => {
        openCheckoutModal(); // open checkout modal
    }, 300);
}

// Inisialisasi keranjang saat load
document.addEventListener("DOMContentLoaded", () => {
    loadCart();
});

# Mocafie Website
Website e-commerce dan profil Mocafie yang dirancang menggunakan Tailwind CSS dan Vanilla JavaScript.

## Persyaratan Sistem Cepat (Khusus Windows)
- Aplikasi web server lokal (seperti **XAMPP**, **Laragon**, **WampServer**) wajib terpasang dan aktif (paling tidak modul `Apache` atau `Nginx`). Tidak perlu `MySQL` jika belum dibutuhkan.

## Cara Menjalankan Website Secara Lokal

1. Salin/pindahkan *folder* proyek `MocafieCSS` ini ke dalam folder *root web server* lokal komputer Anda:
   - Jika memakai **XAMPP**: Pindahkan folder ke `C:\xampp\htdocs\`.
   - Jika memakai **Laragon**: Pindahkan folder ke `C:\laragon\www\`.
2. Buka aplikasi web server lokal yang Anda gunakan (XAMPP/Laragon) lalu nyalakan / *start* modul **Apache**.
3. Buka *browser* pilihan Anda (seperti Google Chrome atau Microsoft Edge).
4. Akses alamat berikut di baris pencarian URL browser:
   `http://localhost/MocafieCSS`

Halaman utama Mocafie akan langsung dimuat secara otomatis berkat *entry point* dari file `index.php`.

---

## Cara *Compile* Ulang Tailwind CSS
Apabila Anda di kemudian hari merubah dan *menambahkan class Tailwind baru* ke dalam file HTML / PHP, berikut cara memperbarui tampilannya:

1. Unduh file program Tailwind v3 Standalone CLI. Caranya cukup kunjungi tautan: [tailwindcss-windows-x64.exe](https://github.com/tailwindlabs/tailwindcss/releases/download/v3.4.17/tailwindcss-windows-x64.exe)
2. Setelah beres terunduh otomatis, letakkan file `tailwindcss-windows-x64.exe` tersebut ke *folder* terluar *(root directory)* proyek `MocafieCSS` anda.
3. Ubah nama file yang baru dimasukkan tersebut (Hapus kata "-windows-x64") sehingga nama *file*-nya tepat dan hanya bernama: `tailwindcss.exe` saja.
4. Terakhir, dobel klik (jalankan) file `build-tailwind.bat`. Secara ajaib dan instan *file* desain antarmuka *style.css* situs Mocafie Anda sudah terbaru!

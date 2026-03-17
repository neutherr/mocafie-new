import os
import re

if os.path.exists("components") and not os.path.exists("partials"):
    os.rename("components", "partials")

with open("index.php", "r", encoding="utf-8") as f:
    content = f.read()

# Replace components/ with partials/
content = content.replace("components/header.php", "partials/header.php")
content = content.replace("components/navbar.php", "partials/navbar.php")
content = content.replace("components/footer.php", "partials/footer.php")

os.makedirs("sections", exist_ok=True)

# List of section tuples: (Comment, filename)
sections_info = [
    ("<!-- Hero Section -->", "hero.php"),
    ("<!-- Produk Kami -->", "produk.php"),
    ("<!-- Proses Produksi -->", "proses.php"),
    ("<!-- Tentang Kami -->", "tentang.php"),
    ("<!-- Perizinan & Sertifikasi (Requested Section) -->", "sertifikasi.php"),
    ("<!-- Galeri Kami -->", "galeri.php"),
    ("<!-- Testimoni / Customer Stories -->", "testimoni.php"),
    ("<!-- Resep -->", "resep.php"),
    ("<!-- Hubungi Kami -->", "kontak.php"),
    ("<!-- Checkout Modal -->", "checkout_modal.php")
]

new_index = []

last_idx = 0
for i in range(len(sections_info)):
    start_str = sections_info[i][0]
    filename = sections_info[i][1]
    
    start_idx = content.find(start_str, last_idx)
    if start_idx == -1:
        continue
        
    if i < len(sections_info) - 1:
        next_str = sections_info[i+1][0]
        end_idx = content.find(next_str, start_idx)
    else:
        end_idx = content.find("<!-- Footer -->", start_idx)
        
    if end_idx == -1:
        end_idx = len(content)
        
    pre_content = content[last_idx:start_idx]
    new_index.append(pre_content)
    
    section_content = content[start_idx:end_idx]
    with open(f"sections/{filename}", "w", encoding="utf-8") as sec_file:
        sec_file.write(section_content)
        
    new_index.append(f"<?php include 'sections/{filename}'; ?>\n")
    last_idx = end_idx

new_index.append(content[last_idx:])

final_index = "".join(new_index)

with open("index.php", "w", encoding="utf-8") as f:
    f.write(final_index)
    
print("Extraction complete.")

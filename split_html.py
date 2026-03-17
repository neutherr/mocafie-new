import os
import re

with open('index.html', 'r', encoding='utf-8') as f:
    html_content = f.read()

# find end of navbar
nav_end_idx = html_content.find('</nav>')
if nav_end_idx != -1:
    nav_end_idx += len('</nav>')
else:
    nav_end_idx = 0

# find start of footer
footer_start_idx = html_content.rfind('<footer')
if footer_start_idx == -1:
    footer_start_idx = len(html_content)

header_navbar = html_content[:nav_end_idx]
main_content = html_content[nav_end_idx:footer_start_idx]
footer_end = html_content[footer_start_idx:]

os.makedirs('components', exist_ok=True)

# Replace Tailwind CDN with local CSS in header
header_navbar = header_navbar.replace('<script src="https://cdn.tailwindcss.com"></script>', '<link rel="stylesheet" href="assets/css/style.css">')

# Remove the custom script and style tags
header_navbar = re.sub(r'<script>\s*tailwind\.config.*?<\/script>', '', header_navbar, flags=re.DOTALL)
header_navbar = re.sub(r'<style>.*?<\/style>', '', header_navbar, flags=re.DOTALL)

# Split header and navbar
navbar_start_idx = header_navbar.find('<nav')
if navbar_start_idx != -1:
    header = header_navbar[:navbar_start_idx]
    navbar = header_navbar[navbar_start_idx:]
else:
    header = header_navbar
    navbar = ""

with open('components/header.php', 'w', encoding='utf-8') as f:
    f.write(header)
with open('components/navbar.php', 'w', encoding='utf-8') as f:
    f.write(navbar)
with open('components/footer.php', 'w', encoding='utf-8') as f:
    f.write(footer_end)

with open('index.php', 'w', encoding='utf-8') as f:
    f.write("<?php include 'components/header.php'; ?>\n")
    f.write("<?php include 'components/navbar.php'; ?>\n")
    f.write(main_content)
    f.write("<?php include 'components/footer.php'; ?>\n")

print("Splitting done.")

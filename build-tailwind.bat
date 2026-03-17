@echo off
echo Memulai kompilasi Tailwind CSS...
tailwindcss.exe -i ./src/input.css -o ./assets/css/style.css --minify
echo Kompilasi selesai!
pause

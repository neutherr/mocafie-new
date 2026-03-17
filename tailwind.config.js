/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.{html,php,js}",
    "./partials/**/*.{html,php,js}",
    "./sections/**/*.{html,php,js}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#2D5A27', // Green
        accent: '#8B4513', // Brown
        surface: '#FAFAF9', // Warm White
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        serif: ['Playfair Display', 'serif'],
      }
    }
  },
  plugins: [],
}

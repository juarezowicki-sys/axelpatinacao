/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    "./src/**/*.php",
    "./src/templates/**/*.phtml", // Garanta que seus templates Mezzio estejam aqui
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
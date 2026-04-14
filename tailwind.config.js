/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    "./projeto/src/**/*.php",
    "./projeto/src/templates/**/*.phtml", // Garanta que seus templates Mezzio estejam aqui
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
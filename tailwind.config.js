/** @type {import('tailwindcss').Config} */

module.exports = {
  content: [
    "./projeto/src/App/src/**/*.php",
    "./projeto/src/App/src/templates/**/*.phtml", // Garanta que seus templates Mezzio estejam aqui
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./home.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    boxShadow: {
      '0-layer': '0 0 0 0 rgba(0, 0, 0, 0)',
      '1-layer': '0 1px 1px 0 rgba(0, 0, 0, 0.1)',
      '2-layer': '5px 10px 30px 0 rgba(0, 0, 0, 0.15)',
      '3-layer': '5px 35px 60px 0 rgba(0, 0, 0, 0.3)',
    },
    extend: {},
  },
  plugins: [],
}

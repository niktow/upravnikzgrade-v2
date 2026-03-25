/** @type {import('tailwindcss').Config} */
export default {
  content: ["./index.html", "./src/**/*.{js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        brand: {
          50: "#f5f8ff",
          100: "#e8efff",
          500: "#335cff",
          700: "#1d3fcc"
        }
      }
    }
  },
  plugins: []
};

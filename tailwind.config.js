/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./app/Views/**/*.php", "./resources/js/**/*.js"],
  theme: { extend: { colors: { primary: '#1e3a8a' } } },
  plugins: [require('@tailwindcss/forms')],
  important: true,
  prefix: 'tw-',
};

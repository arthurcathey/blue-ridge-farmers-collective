/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/Views/**/*.php',
    './public/js/**/*.js'
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          primary: '#1f6b45',
          'primary-hover': '#185437',
          'primary-light': '#6ea387',
          secondary: '#7b5b3e',
          'secondary-hover': '#614630',
          'secondary-light': '#a67e5a',
          accent: '#c9935f',
          'accent-hover': '#a77546',
        },
        neutral: {
          dark: '#1e293b',
          medium: '#475569',
          light: '#e8e4df',
          bg: '#f5f3ef',
        },
        category: {
          produce: '#125a28',
          dairy: '#8b6f47',
          baked: '#d4a574',
          meat: '#a05344',
          seafood: '#5b7b8f',
          pantry: '#9b7b6d',
          beverages: '#6b8e7a',
          flowers: '#b88b9d',
          prepared: '#c08f5a',
          honey: '#d4a574',
          grains: '#8b7355',
          herbs: '#5f8a6a',
          specialty: '#6e6e6e',
        },
      },
      
      spacing: {
        '1': '0.25rem',
        '2': '0.5rem',
        '3': '0.75rem',
        '4': '1rem',
        '5': '1.25rem',
        '6': '1.5rem',
        '8': '2rem',
        '10': '2.5rem',
        '12': '3rem',
        '16': '4rem',
        '20': '5rem',
        '24': '6rem',
        '32': '8rem',
      },

      screens: {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
        '2xl': '1536px',
      },

      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
        'base': ['1rem', { lineHeight: '1.5rem' }],
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
        '2xl': ['1.5rem', { lineHeight: '2rem' }],
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
        'fluid-xs': ['clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem)', { lineHeight: '1.4' }],
        'fluid-sm': ['clamp(0.875rem, 0.8rem + 0.375vw, 1rem)', { lineHeight: '1.5' }],
        'fluid-base': ['clamp(1rem, 0.9rem + 0.5vw, 1.125rem)', { lineHeight: '1.6' }],
        'fluid-lg': ['clamp(1.125rem, 1rem + 0.625vw, 1.25rem)', { lineHeight: '1.6' }],
        'fluid-xl': ['clamp(1.25rem, 1.1rem + 0.75vw, 1.5rem)', { lineHeight: '1.5' }],
        'fluid-2xl': ['clamp(1.5rem, 1.3rem + 1vw, 1.875rem)', { lineHeight: '1.4' }],
        'fluid-3xl': ['clamp(1.875rem, 1.6rem + 1.375vw, 2.25rem)', { lineHeight: '1.3' }],
      },

      boxShadow: {
        'card': '0 2px 8px rgba(0, 0, 0, 0.1)',
        'card-hover': '0 4px 12px rgba(0, 0, 0, 0.15)',
        'sm': '0 1px 2px rgba(0, 0, 0, 0.05)',
      },

      borderRadius: {
        'card': '8px',
        'sm': '4px',
      },

      transitionDuration: {
        '200': '200ms',
        '300': '300ms',
      },

      container: {
        center: true,
        padding: {
          DEFAULT: '1rem',
          'sm': '1.5rem',
          'md': '2rem',
          'lg': '2.5rem',
          'xl': '3rem',
        },
      },
    },
  },
  plugins: [],
};

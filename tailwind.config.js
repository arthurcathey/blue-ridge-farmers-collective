/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/Views/**/*.php',
    './public/js/**/*.js'
  ],
  theme: {
    extend: {
      /* BRAND COLORS - From your color scheme */
      colors: {
        brand: {
          primary: '#125a28',
          'primary-hover': '#0f4620',
          'primary-light': '#6b9b7c',
          secondary: '#8b6f47',
          'secondary-hover': '#6f5838',
          'secondary-light': '#a68a5e',
          accent: '#d4a574',
          'accent-hover': '#c08f5a',
        },
        neutral: {
          dark: '#1e293b',
          medium: '#475569',
          light: '#e8e4df',
          bg: '#f5f3ef',
        },
        /* CATEGORY COLORS */
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
      
      /* MOBILE-FIRST SPACING */
      spacing: {
        /* Small mobile: 4px = 1 unit */
        '1': '0.25rem',   /* 4px */
        '2': '0.5rem',    /* 8px */
        '3': '0.75rem',   /* 12px */
        '4': '1rem',      /* 16px - common padding on mobile */
        '5': '1.25rem',   /* 20px */
        '6': '1.5rem',    /* 24px - section padding mobile */
        '8': '2rem',      /* 32px - larger mobile gaps */
        '10': '2.5rem',   /* 40px */
        '12': '3rem',     /* 48px - desktop comfortable */
        '16': '4rem',     /* 64px - large desktop */
        '20': '5rem',     /* 80px - hero sections */
        '24': '6rem',     /* 96px */
        '32': '8rem',     /* 128px */
      },

      /* RESPONSIVE BREAKPOINTS (mobile-first) */
      screens: {
        'sm': '640px',   /* Small phones - landscape */
        'md': '768px',   /* Tablets */
        'lg': '1024px',  /* Small laptops */
        'xl': '1280px',  /* Desktop */
        '2xl': '1536px', /* Large desktop */
      },

      /* FONT SIZING (mobile-first base) */
      fontSize: {
        'xs': ['0.75rem', { lineHeight: '1rem' }],      /* 12px */
        'sm': ['0.875rem', { lineHeight: '1.25rem' }],  /* 14px */
        'base': ['1rem', { lineHeight: '1.5rem' }],     /* 16px - mobile default */
        'lg': ['1.125rem', { lineHeight: '1.75rem' }],  /* 18px */
        'xl': ['1.25rem', { lineHeight: '1.75rem' }],   /* 20px */
        '2xl': ['1.5rem', { lineHeight: '2rem' }],      /* 24px */
        '3xl': ['1.875rem', { lineHeight: '2.25rem' }], /* 30px */
        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],   /* 36px */
      },

      /* SHADOW EFFECTS (card-like) */
      boxShadow: {
        'card': '0 2px 8px rgba(0, 0, 0, 0.1)',
        'card-hover': '0 4px 12px rgba(0, 0, 0, 0.15)',
        'sm': '0 1px 2px rgba(0, 0, 0, 0.05)',
      },

      /* BORDER RADIUS */
      borderRadius: {
        'card': '8px',
        'sm': '4px',
      },

      /* TRANSITION TIMING */
      transitionDuration: {
        '200': '200ms',
        '300': '300ms',
      },

      /* CONTAINER SIZES (mobile-first) */
      container: {
        center: true,
        padding: {
          DEFAULT: '1rem',    /* Mobile padding */
          'sm': '1.5rem',     /* Tablet */
          'md': '2rem',       /* Desktop */
          'lg': '2.5rem',
          'xl': '3rem',
        },
      },
    },
  },
  plugins: [],
};

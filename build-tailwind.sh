#!/bin/bash

# Blue Ridge Farmers Collective - Tailwind CSS Build Script
# This script builds optimized Tailwind CSS with PurgeCSS
# Usage: ./build-tailwind.sh

echo "🔨 Building Tailwind CSS with PurgeCSS optimization..."
echo ""

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
  echo "❌ node_modules not found. Installing dependencies..."
  npm install
fi

# Build Tailwind with minification
echo "📦 Building minified CSS..."
npm run tailwind:build

echo ""
echo "✅ Build complete!"
echo ""

# Show file sizes
TAILWIND_FILE="./public/css/tailwind.css"
if [ -f "$TAILWIND_FILE" ]; then
  ORIGINAL_SIZE=$(wc -c < "$TAILWIND_FILE")
  ORIGINAL_KB=$((ORIGINAL_SIZE / 1024))
  echo "📊 Tailwind CSS size: ${ORIGINAL_KB} KiB"
  echo ""
  echo "💡 Performance tips:"
  echo "   • Check browser DevTools → Network to verify CSS is loading"
  echo "   • Use Chrome DevTools Coverage tab to see unused CSS"
  echo "   • Run 'npm run tailwind:watch' while developing"
else
  echo "⚠️  CSS file not found at $TAILWIND_FILE"
fi

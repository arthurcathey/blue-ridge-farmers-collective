@echo off
REM Blue Ridge Farmers Collective - Tailwind CSS Build Script (Windows)
REM This script builds optimized Tailwind CSS with PurgeCSS
REM Usage: build-tailwind.bat

echo.
echo 🔨 Building Tailwind CSS with PurgeCSS optimization...
echo.

REM Check if node_modules exists
if not exist "node_modules" (
  echo ❌ node_modules not found. Installing dependencies...
  call npm install
)

REM Build Tailwind with minification
echo 📦 Building minified CSS...
call npm run tailwind:build

echo.
echo ✅ Build complete!
echo.

REM Show file sizes if CSS file exists
if exist "public\css\tailwind.css" (
  for %%A in ("public\css\tailwind.css") do (
    set size=%%~zA
    set /A KB=size / 1024
    echo 📊 Tailwind CSS size: !KB! KiB
  )
  echo.
  echo 💡 Performance tips:
  echo    * Check browser DevTools ^> Network to verify CSS is loading
  echo    * Use Chrome DevTools Coverage tab to see unused CSS
  echo    * Run 'npm run tailwind:watch' while developing
) else (
  echo ⚠️  CSS file not found at public\css\tailwind.css
)

pause

#!/bin/bash

echo "🎨 Fixing PDF background images..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Create backgrounds directory if not exists
mkdir -p public/backgrounds

# Create test background if none exist
if [ ! "$(ls -A public/backgrounds)" ]; then
    echo "📸 Creating test background image..."
    # Create a simple colored rectangle as test background
    echo "<svg xmlns='http://www.w3.org/2000/svg' width='794' height='1123' viewBox='0 0 794 1123'>
        <rect width='794' height='1123' fill='#f0f8ff'/>
        <text x='50' y='100' font-family='Arial' font-size='24' fill='#333'>Test Background</text>
    </svg>" > public/backgrounds/test-bg.svg
    echo "✅ Test background created: test-bg.svg"
fi

# Clear caches
php artisan view:clear
php artisan config:clear

echo ""
echo "🎯 BACKGROUND IMAGES FIXED!"
echo ""
echo "✅ WHAT CHANGED:"
echo "   🖼️  Background images now use <img> tags instead of CSS"
echo "   📁 Backgrounds directory: public/backgrounds/"
echo "   🔧 DomPDF chroot configured for local images"
echo "   ⚙️  Enhanced PDF options for image support"
echo ""
echo "📁 BACKGROUND LOCATIONS:"
echo "   Place images in: public/backgrounds/"
echo "   Supported formats: JPG, PNG, SVG"
echo "   Recommended size: 794x1123px (A4 ratio)"
echo ""
echo "🔄 TEST NOW:"
echo "   1. Add background images to public/backgrounds/"
echo "   2. Set background in editor"
echo "   3. Download PDF"
echo "   4. Backgrounds should now be visible!"
echo ""
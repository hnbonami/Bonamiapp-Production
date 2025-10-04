#!/bin/bash

echo "📄 Installing DomPDF for PDF generation..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Install DomPDF via Composer
composer require barryvdh/laravel-dompdf

echo "✅ DomPDF installed!"

# Publish config (optional)
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

echo "🎯 PDF functionality ready to implement!"
@extends('layouts.app')

@section('content')
<div class="profile-settings-page">
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Profielinstellingen</h1>
                <p class="text-gray-600 mt-2">Beheer je persoonlijke gegevens en voorkeuren</p>
            </div>
            <!-- Profile Completion -->
            <div class="text-right">
                <div class="text-sm text-gray-600 mb-1">Profiel volledigheid</div>
                <div class="w-48 bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $completion }}%"></div>
                </div>
                <div class="text-sm font-semibold text-blue-600 mt-1">{{ $completion }}%</div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <nav class="space-y-2">
                    <a href="#personal" class="tab-link active flex items-center px-3 py-2 text-sm font-medium rounded-md bg-blue-50 text-blue-700">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Persoonlijk
                    </a>
                    <a href="#security" class="tab-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Beveiliging
                    </a>
                    <a href="#preferences" class="tab-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Voorkeuren
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            <!-- Personal Information Tab -->
            <div id="personal-tab" class="tab-content bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                @include('profile.tabs.personal')
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content bg-white rounded-lg shadow-sm border border-gray-200 p-6 hidden">
                @include('profile.tabs.security')
            </div>

            <!-- Preferences Tab -->
            <div id="preferences-tab" class="tab-content bg-white rounded-lg shadow-sm border border-gray-200 p-6 hidden">
                @include('profile.tabs.preferences')
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            tabLinks.forEach(l => {
                l.classList.remove('active', 'bg-blue-50', 'text-blue-700');
                l.classList.add('text-gray-700');
            });
            
            // Add active class to clicked link
            this.classList.add('active', 'bg-blue-50', 'text-blue-700');
            this.classList.remove('text-gray-700');
            
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            const tabId = this.getAttribute('href').substring(1) + '-tab';
            document.getElementById(tabId).classList.remove('hidden');
        });
    });
    
    // AJAX form submissions
    setupAjaxForms();
});

function setupAjaxForms() {
    const forms = document.querySelectorAll('.ajax-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Opslaan...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Update completion percentage if provided
                    if (data.completion) {
                        updateCompletionBar(data.completion);
                    }
                    
                    // Update avatar if provided
                    if (data.avatar_url) {
                        updateAvatarImage(data.avatar_url);
                    }
                } else {
                    showMessage('Er is een fout opgetreden', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Er is een fout opgetreden', 'error');
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    });
}

function showMessage(message, type) {
    const alertDiv = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    
    alertDiv.className = `${bgColor} border px-4 py-3 rounded mb-4 alert-message`;
    alertDiv.textContent = message;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.lg\\:col-span-3');
    mainContent.insertBefore(alertDiv, mainContent.firstChild);
    
    // Remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

function updateCompletionBar(percentage) {
    const progressBar = document.querySelector('.bg-blue-600');
    const percentageText = document.querySelector('.text-blue-600');
    
    if (progressBar && percentageText) {
        progressBar.style.width = percentage + '%';
        percentageText.textContent = percentage + '%';
    }
}

function updateAvatarImage(url) {
    const avatarImages = document.querySelectorAll('.user-avatar');
    avatarImages.forEach(img => {
        img.src = url;
    });
}

// JavaScript to force resize profile photo on settings page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🖼️ Resizing profile photo on settings page...');
    
    // Find all images on the settings page and resize them
    const images = document.querySelectorAll('img');
    images.forEach(function(img) {
        // Skip logo and navigation images
        if (img.alt?.includes('Logo') || img.src?.includes('logo')) {
            return;
        }
        
        // Resize profile/avatar images
        if (img.src?.includes('/storage/') || 
            img.alt?.includes('profiel') || 
            img.alt?.includes('Avatar') ||
            img.className?.includes('avatar') ||
            img.className?.includes('profile')) {
            
            console.log('Resizing image:', img.src);
            img.style.width = '150px';
            img.style.height = '150px';
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';
            img.style.minWidth = '150px';
            img.style.minHeight = '150px';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
        }
    });
    
    // Also resize any images that get loaded later
    setTimeout(function() {
        const lateImages = document.querySelectorAll('img[src*="/storage/"]');
        lateImages.forEach(function(img) {
            img.style.width = '150px';
            img.style.height = '150px';
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';
            img.style.borderRadius = '50%';
            img.style.objectFit = 'cover';
        });
    }, 1000);
});
</script>
@endpush
</div>
@endsection
<!-- Gallery Lightbox Component -->
<div id="gallery-lightbox" class="lightbox">
    <div class="lightbox-content">
        <div class="lightbox-header">
            <h3 id="gallery-title">Gallery</h3>
            <button class="lightbox-close" onclick="closeGallery()">×</button>
        </div>
        <div class="lightbox-body">
            <div class="gallery-container">
                <div id="gallery-media" class="gallery-media">
                    <!-- Images/videos will be loaded here -->
                </div>
                <div class="gallery-nav">
                    <button class="gallery-btn prev" onclick="previousMedia()">❮</button>
                    <button class="gallery-btn next" onclick="nextMedia()">❯</button>
                </div>
                <div class="gallery-thumbnails" id="gallery-thumbnails">
                    <!-- Thumbnails will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Gallery Lightbox Styles */
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 9999;
        overflow-y: auto;
    }
    
    .lightbox.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .lightbox-content {
        background: white;
        border-radius: 12px;
        width: 100%;
        max-width: 900px;
        max-height: 90vh;
        overflow: hidden;
    }
    
    .lightbox-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .lightbox-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #6b7280;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .lightbox-close:hover {
        background: #f3f4f6;
        color: #374151;
    }
    
    .lightbox-body {
        padding: 1.5rem;
    }
    
    .gallery-container {
        position: relative;
    }
    
    .gallery-media {
        width: 100%;
        height: 500px;
        background: #f8fafc;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }
    
    .gallery-media img,
    .gallery-media video {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }
    
    .gallery-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        display: flex;
        justify-content: space-between;
        padding: 0 1rem;
        pointer-events: none;
    }
    
    .gallery-btn {
        background: rgba(255,255,255,0.9);
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        pointer-events: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        line-height: 1;
        font-family: system-ui, -apple-system, sans-serif;
        font-weight: bold;
    }
    
    .gallery-btn:hover {
        background: white;
        transform: scale(1.1);
    }
    
    .gallery-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    
    .gallery-thumbnails {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0.5rem 0;
    }
    
    .gallery-thumbnail {
        width: 80px;
        height: 60px;
        border-radius: 4px;
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    
    .gallery-thumbnail:hover {
        border-color: #3b82f6;
    }
    
    .gallery-thumbnail.active {
        border-color: #3b82f6;
    }
    
    .gallery-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .gallery-placeholder {
        color: #9ca3af;
        text-align: center;
        padding: 3rem;
        font-size: 1.125rem;
    }
    
    @media (max-width: 768px) {
        .lightbox {
            padding: 1rem;
        }
        
        .gallery-media {
            height: 300px;
        }
        
        .gallery-btn {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }
        
        .gallery-thumbnails {
            gap: 0.25rem;
        }
        
        .gallery-thumbnail {
            width: 60px;
            height: 45px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let galleryMedia = [];
    let currentMediaIndex = 0;

    function showLightbox(title, media) {
        galleryMedia = media || [];
        currentMediaIndex = 0;
        document.getElementById('gallery-title').textContent = title;
        loadGalleryMedia();
        document.getElementById('gallery-lightbox').classList.add('active');
    }

    function loadGalleryMedia() {
        const mediaContainer = document.getElementById('gallery-media');
        const thumbnailsContainer = document.getElementById('gallery-thumbnails');
        
        if (galleryMedia.length === 0) {
            mediaContainer.innerHTML = '<div class="gallery-placeholder">No images or videos available.</div>';
            thumbnailsContainer.innerHTML = '';
            updateGalleryNav();
            return;
        }
        
        // Load main media
        const currentMedia = galleryMedia[currentMediaIndex];
        if (currentMedia.type === 'image') {
            mediaContainer.innerHTML = `<img src="${currentMedia.url}" alt="Gallery Image" />`;
        } else if (currentMedia.type === 'video') {
            mediaContainer.innerHTML = `<video src="${currentMedia.url}" controls></video>`;
        }
        
        // Load thumbnails
        thumbnailsContainer.innerHTML = '';
        galleryMedia.forEach((media, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = `gallery-thumbnail ${index === currentMediaIndex ? 'active' : ''}`;
            thumbnail.onclick = () => goToMedia(index);
            
            if (media.type === 'image') {
                thumbnail.innerHTML = `<img src="${media.thumbnail || media.url}" alt="Thumbnail" />`;
            } else if (media.type === 'video') {
                thumbnail.innerHTML = `<img src="${media.thumbnail || ''}" alt="Video Thumbnail" />`;
            }
            
            thumbnailsContainer.appendChild(thumbnail);
        });
        
        updateGalleryNav();
    }

    function updateGalleryNav() {
        const prevBtn = document.querySelector('.gallery-btn.prev');
        const nextBtn = document.querySelector('.gallery-btn.next');
        
        if (prevBtn) prevBtn.disabled = currentMediaIndex === 0;
        if (nextBtn) nextBtn.disabled = currentMediaIndex === galleryMedia.length - 1;
        
        // Hide navigation if no media or only one media
        const galleryNav = document.querySelector('.gallery-nav');
        if (galleryNav) {
            galleryNav.style.display = galleryMedia.length <= 1 ? 'none' : 'flex';
        }
    }

    function previousMedia() {
        if (currentMediaIndex > 0) {
            currentMediaIndex--;
            loadGalleryMedia();
        }
    }

    function nextMedia() {
        if (currentMediaIndex < galleryMedia.length - 1) {
            currentMediaIndex++;
            loadGalleryMedia();
        }
    }

    function goToMedia(index) {
        currentMediaIndex = index;
        loadGalleryMedia();
    }

    function closeGallery() {
        document.getElementById('gallery-lightbox').classList.remove('active');
        galleryMedia = [];
        currentMediaIndex = 0;
    }

    // Close lightbox when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const lightbox = document.getElementById('gallery-lightbox');
        if (lightbox) {
            lightbox.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeGallery();
                }
            });
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('gallery-lightbox').classList.contains('active')) {
            if (e.key === 'Escape') {
                closeGallery();
            } else if (e.key === 'ArrowLeft') {
                previousMedia();
            } else if (e.key === 'ArrowRight') {
                nextMedia();
            }
        }
    });
</script>
@endpush
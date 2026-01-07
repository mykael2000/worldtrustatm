/**
 * Card Display JavaScript
 * Loading animation and card reveal for card-display.php
 */

document.addEventListener('DOMContentLoaded', function() {
    const loadingContainer = document.getElementById('loadingContainer');
    const cardContainer = document.getElementById('cardContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    
    if (!loadingContainer || !cardContainer) return;
    
    let progress = 0;
    const duration = 4000; // 4 seconds total
    const interval = 50; // Update every 50ms
    const increment = (interval / duration) * 100;
    
    // Loading animation
    const progressInterval = setInterval(function() {
        progress += increment;
        
        if (progress >= 100) {
            progress = 100;
            clearInterval(progressInterval);
            
            // Show card after loading complete
            setTimeout(function() {
                loadingContainer.style.display = 'none';
                cardContainer.style.display = 'block';
                
                // Trigger card animation
                const atmCard = document.querySelector('.atm-card');
                if (atmCard) {
                    atmCard.style.animation = 'cardFlip 0.8s ease-out';
                }
            }, 500);
        }
        
        // Update progress bar and text
        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.floor(progress);
    }, interval);
    
    // Add some random status messages during loading
    const statusMessages = [
        'Verifying your information...',
        'Generating your card details...',
        'Securing your account...',
        'Activating your card...',
        'Almost done...'
    ];
    
    const loadingText = document.querySelector('.loading-text');
    let messageIndex = 0;
    
    const messageInterval = setInterval(function() {
        messageIndex++;
        if (messageIndex < statusMessages.length) {
            loadingText.textContent = statusMessages[messageIndex];
        } else {
            clearInterval(messageInterval);
        }
    }, 800);
});

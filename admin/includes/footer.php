            </div> <!-- .content-wrapper -->
        </main>
    </div> <!-- .admin-wrapper -->
    
    <script>
    // Session timeout warning
    const sessionTimeout = <?php echo SESSION_TIMEOUT; ?>;
    const warningTime = sessionTimeout - 300; // 5 minutes before timeout
    let remainingTime = sessionTimeout;
    
    // Update session timer
    function updateSessionTimer() {
        remainingTime--;
        
        if (remainingTime <= 0) {
            alert('Your session has expired. You will be redirected to the login page.');
            window.location.href = '/admin/logout.php';
            return;
        }
        
        if (remainingTime <= 300 && remainingTime % 60 === 0) {
            const minutes = Math.floor(remainingTime / 60);
            console.log(`Session will expire in ${minutes} minute(s)`);
        }
        
        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        const timerElement = document.getElementById('sessionTimer');
        if (timerElement) {
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }
    
    // Update timer every second
    setInterval(updateSessionTimer, 1000);
    
    // Reset timer on user activity
    document.addEventListener('mousemove', function() {
        // Send AJAX request to refresh session (optional)
    });
    </script>
</body>
</html>

// Session timeout handling
class SessionManager {
    constructor(timeoutWarningMinutes = 5) { // 5 minutes warning before timeout
        this.timeoutWarningMinutes = timeoutWarningMinutes;
        this.checkInterval = 30000; // Check every 30 seconds
        this.isActive = true;
        this.lastActivityTime = Date.now();
        this.warningShown = false;
        this.isHandlingTimeout = false;
        this.isLoggingOut = false;
        this.sessionTimeout = 1800; // 30 minutes in seconds
        this.setupSessionMonitoring();
        this.setupActivityListeners();
        console.log('Session Manager initialized with 30-minute timeout');
    }

    showWarning() {
        if (!this.warningShown && !this.isHandlingTimeout) {
            this.warningShown = true;
            Swal.fire({
                title: 'Session Timeout Warning',
                html: 'Your session will expire in 5 minutes.<br>Would you like to stay logged in?',
                icon: 'warning',
                timer: 300000, // 5 minutes
                timerProgressBar: true,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                allowOutsideClick: false,
                width: '400px',
                customClass: {
                    container: 'small-modal',
                    popup: 'small-modal',
                    header: 'small-modal-header',
                    title: 'small-modal-title',
                    content: 'small-modal-content'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.resetTimer();
                    Swal.fire({
                        title: 'Extended!',
                        text: 'Session extended successfully',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        width: '400px',
                        customClass: {
                            container: 'small-modal',
                            popup: 'small-modal',
                            header: 'small-modal-header',
                            title: 'small-modal-title',
                            content: 'small-modal-content'
                        }
                    });
                } else if (
                    result.dismiss === Swal.DismissReason.cancel ||
                    result.dismiss === Swal.DismissReason.timer
                ) {
                    this.handleSessionTimeout();
                }
                this.warningShown = false;
            });
        }
    }

    hideWarning() {
        if (this.warningShown) {
            Swal.close();
            this.warningShown = false;
        }
    }

    setupSessionMonitoring() {
        // Check session status periodically
        setInterval(() => this.checkSession(), this.checkInterval);
    }

    setupActivityListeners() {
        // Reset timer on user activity
        const events = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart', 'click'];
        events.forEach(event => {
            document.addEventListener(event, () => {
                if (!this.isHandlingTimeout && !this.isLoggingOut) {
                    this.isActive = true;
                    this.lastActivityTime = Date.now();
                    if (this.warningShown) {
                        this.hideWarning();
                    }
                    this.resetTimer();
                }
            });
        });
        console.log('Activity listeners set up');
    }

    async checkSession() {
        if (this.isHandlingTimeout || this.isLoggingOut) return;

        try {
            const response = await fetch('../backend/session_handler.php?check_session=1');
            const data = await response.json();
            console.log('Session check:', data);
            
            if (!data.valid && !this.isHandlingTimeout) {
                this.handleSessionTimeout();
            } else if (data.last_activity) {
                // Calculate time until session expires
                const currentTime = Math.floor(Date.now() / 1000);
                const lastActivity = parseInt(data.last_activity);
                const timeUntilExpire = this.sessionTimeout - (currentTime - lastActivity);
                
                // Show warning if within warning period (5 minutes)
                if (timeUntilExpire <= 300 && !this.warningShown && !this.isHandlingTimeout) {
                    this.showWarning();
                }
                
                console.log(`Time until session expires: ${Math.floor(timeUntilExpire / 60)} minutes and ${timeUntilExpire % 60} seconds`);
            }
        } catch (error) {
            console.error('Session check failed:', error);
        }
    }

    async handleSessionTimeout() {
        if (this.isHandlingTimeout) return;
        
        try {
            this.isHandlingTimeout = true;
            console.log('Session timeout detected, destroying session...');
            this.hideWarning();
            
            const response = await fetch('../backend/session_handler.php?destroy_session=1');
            const data = await response.json();
            console.log('Session destroy response:', data);
            
            await Swal.fire({
                title: 'Session Expired',
                text: 'Please log in again',
                icon: 'info',
                allowOutsideClick: false,
                confirmButtonText: 'OK',
                width: '400px',
                customClass: {
                    container: 'small-modal',
                    popup: 'small-modal',
                    header: 'small-modal-header',
                    title: 'small-modal-title',
                    content: 'small-modal-content'
                }
            });
            
            window.location.href = '/index.php';
        } catch (error) {
            console.error('Session destruction failed:', error);
            window.location.href = '/index.php';
        }
    }

    async resetTimer() {
        if (!this.isActive || this.isHandlingTimeout || this.isLoggingOut) return;
        
        try {
            console.log('Resetting session timer...');
            const response = await fetch('../backend/session_handler.php?reset_session=1');
            const data = await response.json();
            console.log('Session reset response:', data);
            
            if (!data.success) {
                console.warn('Session reset failed:', data.message);
                if (!data.session_data || !data.session_data.user_id) {
                    this.handleSessionTimeout();
                }
            }
        } catch (error) {
            console.error('Session reset failed:', error);
        }
    }

    async logout() {
        if (this.isLoggingOut) return;
        
        try {
            this.isLoggingOut = true;
            console.log('Logging out...');
            const response = await fetch('../backend/session_handler.php?logout=1');
            const data = await response.json();
            console.log('Logout response:', data);
            
            await Swal.fire({
                title: 'Logged Out',
                text: 'You have been logged out successfully',
                icon: 'success',
                allowOutsideClick: false,
                confirmButtonText: 'OK',
                width: '400px',
                customClass: {
                    container: 'small-modal',
                    popup: 'small-modal',
                    header: 'small-modal-header',
                    title: 'small-modal-title',
                    content: 'small-modal-content'
                }
            });
            
            window.location.href = '/index.php';
        } catch (error) {
            console.error('Logout failed:', error);
            window.location.href = '/index.php';
        } finally {
            this.isLoggingOut = false;
        }
    }
}

// Initialize session manager when document is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing SessionManager...');
    window.sessionManager = new SessionManager();
});

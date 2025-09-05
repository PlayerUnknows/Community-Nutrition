<?php
// Header section with logo, welcome message, clock, and profile dropdown
?>
<!-- Header Section -->
<div class="bg-white py-1 shadow-sm h-1 header-section">
    <div class="container d-flex align-items-center justify-content-between">
        <!-- Logo Section -->
        <div class="d-flex align-items-center logo-container">
            <img src="../../assets/img/SanAndres.svg" alt="San Andres Logo" class="responsive-logo me-3" style="width: 65px; height: 65px;">
            <div class="logo-text">
                <h6 class="mb-0 text-primary fw-bold" style="font-size: 14px; line-height: 1.2;">Community Nutrition</h6>
                <small class="text-muted" style="font-size: 11px; line-height: 1.2;">Health Management</small>
            </div>
        </div>

        <div class="text-center">
            <!-- Enhanced Welcome Section -->
            <div class="welcome-section mb-2">
                <div class="welcome-card d-inline-block px-4 py-2" style="background: rgba(0,123,255,0.1); border-radius: 25px; border: 1px solid rgba(0,123,255,0.2);">
                    <p class="mb-0 text-dark fw-semibold">
                        <i class="fas fa-user-circle text-primary me-2"></i>
                        Welcome, <span id="username" class="text-primary fw-bold">
                            <?php
                            // Multiple checks to retrieve email
                            $displayEmail = "Guest";

                            if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                                $email = htmlspecialchars($_SESSION['email']);
                            } elseif (isset($_SESSION['user']['email']) && !empty($_SESSION['user']['email'])) {
                                $email = htmlspecialchars($_SESSION['user']['email']);
                            }

                            if (isset($email)) {
                                // Remove '@gmail.com' if it exists
                                $displayEmail = str_replace('@gmail.com', '', $email);
                            }

                            echo $displayEmail;
                            ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <!-- Enhanced Clock Section -->
            <div class="clock-section">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="clock-item me-3 px-3 py-1" style="background: rgba(40,167,69,0.1); border-radius: 15px; border: 1px solid rgba(40,167,69,0.2);">
                        <i class="fas fa-calendar-alt text-success me-1"></i>
                        <span id="current-date" class="text-success fw-semibold small"></span>
                    </div>
                    <div class="clock-item px-3 py-1" style="background: rgba(255,193,7,0.1); border-radius: 15px; border: 1px solid rgba(255,193,7,0.2);">
                        <i class="fas fa-clock text-warning me-1"></i>
                        <span id="current-time" class="text-warning fw-semibold small"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Section -->
        <?php include __DIR__ . '/profile_dropdown.php'; ?>
        </div>
    </div>
</div>

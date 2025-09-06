<?php
// Header section with logo, welcome message, clock, and profile dropdown
?>
<!-- Clean Professional Header -->
<div class="clean-header">
    <div class="header-content">
        <!-- Left: User Welcome -->
        <div class="user-welcome">
            <i class="fas fa-user-circle user-icon"></i>
            <span class="welcome-text">Welcome, <strong id="username">
                <?php
                $displayEmail = "Guest";
                if (isset($_SESSION['email']) && !empty($_SESSION['email'])) {
                    $email = htmlspecialchars($_SESSION['email']);
                } elseif (isset($_SESSION['user']['email']) && !empty($_SESSION['user']['email'])) {
                    $email = htmlspecialchars($_SESSION['user']['email']);
                }
                if (isset($email)) {
                    $displayEmail = str_replace('@gmail.com', '', $email);
                }
                echo $displayEmail;
                ?>
            </strong></span>
        </div>

        <!-- Center: Logo & Title -->
        <div class="brand-section">
            <img src="../../assets/img/SanAndres.svg" alt="Logo" class="brand-logo">
            <div class="brand-info">
                <h1 class="brand-title">Community Nutrition</h1>
                <p class="brand-subtitle">Health Management System</p>
            </div>
        </div>

        <!-- Right: Date, Time & Profile -->
        <div class="right-section">
            <div class="datetime-info">
                <span class="date-info"><i class="fas fa-calendar-alt"></i> <span id="current-date"></span></span>
                <span class="time-info"><i class="fas fa-clock"></i> <span id="current-time"></span></span>
            </div>
            <div class="profile-area">
                <?php include __DIR__ . '/profile_dropdown.php'; ?>
            </div>
        </div>
    </div>
</div>

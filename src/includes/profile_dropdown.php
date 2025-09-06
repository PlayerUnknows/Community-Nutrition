<?php
// Enhanced profile dropdown with professional styling
?>

<!-- Enhanced Profile Dropdown -->
<div class="nav-item dropdown" style="position: relative; z-index: 1051;">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.3s ease;">
        <div class="profile-avatar me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,123,255,0.3);">
            <i class="fas fa-user text-white" style="font-size: 14px;"></i>
        </div>
        <span class="fw-semibold text-primary">Profile</span>
        <i class="fas fa-chevron-down ms-2 text-primary" style="font-size: 12px;"></i>
    </a>
    
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 12px; padding: 8px; min-width: 220px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); z-index: 1050;">
        <!-- Profile Header -->
        <li class="dropdown-header px-3 py-2 mb-2" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); border-radius: 8px; border-bottom: none;">
            <div class="d-flex align-items-center">
                <div class="profile-icon me-2" style="width: 24px; height: 24px; background: linear-gradient(135deg, #007bff, #0056b3); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user text-white" style="font-size: 10px;"></i>
                </div>
                <small class="text-muted fw-semibold">Account Options</small>
            </div>
        </li>
        
        <!-- Profile Settings -->
        <li>
            <a class="dropdown-item d-flex align-items-center py-2 px-3" href="#" id="profileSettingsBtn" style="border-radius: 8px; transition: all 0.2s ease;">
                <div class="icon-wrapper me-3" style="width: 32px; height: 32px; background: rgba(0,123,255,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-cog text-primary"></i>
                </div>
                <div>
                    <div class="fw-semibold text-dark">Profile Settings</div>
                    <small class="text-muted">Manage your account</small>
                </div>
            </a>
        </li>
        
        <!-- Display Settings -->
        <!-- <li>
            <a class="dropdown-item d-flex align-items-center py-2 px-3" href="#" id="displaySettingsBtn" style="border-radius: 8px; transition: all 0.2s ease;">
                <div class="icon-wrapper me-3" style="width: 32px; height: 32px; background: rgba(40,167,69,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-cog text-success"></i>
                </div>
                <div>
                    <div class="fw-semibold text-dark">Display Settings</div>
                    <small class="text-muted">Customize appearance</small>
                </div>
            </a>
        </li> -->
        
        <!-- Divider -->
        <li><hr class="dropdown-divider my-2" style="border-color: rgba(0,0,0,0.1);"></li>
        
        <!-- Logout -->
        <li>
            <a class="dropdown-item d-flex align-items-center py-2 px-3 logout-button" href="#" id="logoutButton" style="border-radius: 8px; transition: all 0.2s ease; background: rgba(220,53,69,0.05);">
                <div class="icon-wrapper me-3" style="width: 32px; height: 32px; background: rgba(220,53,69,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-sign-out-alt text-danger"></i>
                </div>
                <div>
                    <div class="fw-semibold text-danger">Logout</div>
                    <small class="text-muted">Sign out of your account</small>
                </div>
            </a>
        </li>
    </ul>
</div>

<style>
/* Enhanced hover effects */
.dropdown-item:hover {
    background: rgba(0,123,255,0.1) !important;
    transform: translateX(2px);
}

.logout-button:hover {
    background: rgba(220,53,69,0.1) !important;
}

.dropdown-toggle:hover {
    background: rgba(0,123,255,0.15) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Smooth transitions */
.dropdown-menu {
    animation: fadeInDown 0.3s ease;
    z-index: 1050 !important;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

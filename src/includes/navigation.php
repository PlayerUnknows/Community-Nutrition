<?php
// Enhanced professional navigation tabs for admin dashboard
?>
<!-- Enhanced Professional Tab Navigation - Inline Layout -->
<div class="professional-nav-container" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); border-bottom: 2px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
    <ul class="nav nav-tabs professional-nav" id="myTab" role="tablist" style="border-bottom: none; margin-bottom: 0; display: flex; flex-wrap: nowrap;">
        <li class="nav-item" role="presentation">
            <button class="nav-link professional-nav-link active" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients" type="button" role="tab" aria-controls="patients" aria-selected="true" tabindex="0">
                <i class="fas fa-users me-2"></i>
                <span>Patients Profile</span>
            </button>
        </li>
        <li class="nav-item" role="presentation" id="monitoring-container">
            <button class="nav-link professional-nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab" aria-controls="schedule" aria-selected="false" tabindex="-1">
                <i class="fas fa-chart-line me-2"></i>
                <span>Nutrition Monitoring</span>
                <i class="fas fa-chevron-down ms-2"></i>
            </button>
            <div class="sub-nav professional-sub-nav">
                <button class="sub-nav-button professional-sub-button" data-target="monitoring-records">
                    <i class="fas fa-clipboard-list me-2"></i>Monitoring Records
                </button>
                <button class="sub-nav-button professional-sub-button" data-target="nutrition-report">
                    <i class="fas fa-chart-area me-2"></i>Growth Trends
                </button>
                <button class="sub-nav-button professional-sub-button" data-target="arm-circumference">
                    <i class="fas fa-ruler me-2"></i>Arm Circumference
                </button>
                <button class="sub-nav-button professional-sub-button" data-target="bmi-statistics">
                    <i class="fas fa-weight me-2"></i>BMI Statistics
                </button>
                <button class="sub-nav-button professional-sub-button" data-target="overall-report">
                    <i class="fas fa-chart-pie me-2"></i>OverAllReport
                </button>
            </div>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link professional-nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab" aria-controls="appointments" aria-selected="false" tabindex="-1">
                <i class="fas fa-calendar-alt me-2"></i>
                <span>Appointments</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link professional-nav-link" id="event-tab" data-bs-toggle="tab" data-bs-target="#event" type="button" role="tab" aria-controls="event" aria-selected="false" tabindex="-1">
                <i class="fas fa-calendar-check me-2"></i>
                <span>Event Information</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link professional-nav-link" id="audit-tab" data-bs-toggle="tab" data-bs-target="#audit" type="button" role="tab" aria-controls="audit" aria-selected="false" tabindex="-1">
                <i class="fas fa-history me-2"></i>
                <span>Audit Trail</span>
            </button>
        </li>
        <li class="nav-item" role="presentation" id="acc-reg-container">
            <button class="nav-link professional-nav-link" id="acc-reg" data-bs-toggle="tab" data-bs-target="#account" type="button" role="tab" aria-controls="account" aria-selected="false" tabindex="-1">
                <i class="fas fa-user-plus me-2"></i>
                <span>Create Account</span>
                <i class="fas fa-chevron-down ms-2"></i>
            </button>
            <!-- Sub-navigation -->
            <div class="sub-nav professional-sub-nav">
                <button class="sub-nav-button professional-sub-button" data-target="view-users">
                    <i class="fas fa-users-cog me-2"></i>View Users
                </button>
            </div>
        </li>
    </ul>
</div>

<style>
/* Professional Navigation Styles */
.professional-nav-container {
    position: relative;
    z-index: 1000;
}

.professional-nav {
    padding: 0 15px;
    display: flex !important;
    flex-wrap: nowrap !important;
    white-space: nowrap;
}

.professional-nav-link {
    background: transparent !important;
    border: none !important;
    border-radius: 8px 8px 0 0 !important;
    padding: 12px 16px !important;
    margin-right: 2px !important;
    color: #6c757d !important;
    font-weight: 500 !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    position: relative !important;
    display: flex !important;
    align-items: center !important;
    border-bottom: 3px solid transparent !important;
    flex-shrink: 0 !important;
    white-space: nowrap !important;
}

.professional-nav-link:hover {
    background: rgba(0,123,255,0.05) !important;
    color: #007bff !important;
    transform: translateY(-1px);
}

.professional-nav-link.active {
    background: #ffffff !important;
    color: #007bff !important;
    border-bottom: 3px solid #007bff !important;
    box-shadow: 0 -2px 8px rgba(0,123,255,0.15) !important;
    font-weight: 600 !important;
}

.professional-nav-link i {
    font-size: 14px;
    width: 16px;
    text-align: center;
}

/* Sub-navigation Styles */
.professional-sub-nav {
    position: absolute;
    top: 100%;
    left: 0;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    border: 1px solid #e9ecef;
    padding: 8px 0;
    min-width: 220px;
    z-index: 1001;
    display: none;
    animation: fadeInDown 0.3s ease;
}

.professional-sub-button {
    width: 100%;
    padding: 10px 20px;
    border: none;
    background: transparent;
    color: #495057;
    text-align: left;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.professional-sub-button:hover {
    background: rgba(0,123,255,0.1);
    color: #007bff;
    transform: translateX(4px);
}

.professional-sub-button i {
    font-size: 12px;
    width: 16px;
    text-align: center;
}

/* Animation */
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .professional-nav {
        padding: 0 10px;
        flex-wrap: wrap;
    }
    
    .professional-nav-link {
        padding: 10px 15px !important;
        font-size: 13px !important;
        margin-right: 2px !important;
    }
    
    .professional-nav-link span {
        display: none;
    }
    
    .professional-nav-link i {
        margin: 0 !important;
    }
}
</style>

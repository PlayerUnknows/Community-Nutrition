<?php
// Session already started in index.php
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = intval($_SESSION['role']);
    
    switch ($role) {
        case 1: // Parent/Family
            header('Location: src/view/parent.php');
            break;
        case 2: // Health Worker
            header('Location: src/view/health_worker_dashboard.php');
            break;
        case 3: // Admin
            header('Location: src/view/admin.php');
            break;
        default:
            header('Location: src/view/general_dashboard.php');
    }
    exit();
}
?>

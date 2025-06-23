<?php
$pageTitle = "My Profile";
require_once 'userComponents/header.php'; // Correct path to header.php

require_once 'users.php';
$userActions = new UserActions();

// Get user profile data
$profileUserId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUser['user_id'];
$profileData = $userActions->getUserById($profileUserId);

// Check if user exists
if (!$profileData) {
    header('Location: home.php');
    exit;
}

// Check if current user has permission to view this profile
if ($currentUser['role_id'] != 1 && $currentUser['user_id'] != $profileUserId) {
    header('Location: home.php');
    exit;
}
?>

<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="profile-image mb-3">
                    <img src="assets/images/default-avatar.png" class="rounded-circle" width="150" height="150" alt="Profile Image">
                </div>
                <h4><?php echo htmlspecialchars($profileData['first_name'] . ' ' . $profileData['last_name']); ?></h4>
                <p class="text-muted">@<?php echo htmlspecialchars($profileData['username']); ?></p>
                
                <div class="d-flex justify-content-center mb-3">
                    <span class="badge bg-primary me-2"><?php echo htmlspecialchars($profileData['role_name']); ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($profileData['department_name']); ?></span>
                </div>
                
                <div class="d-flex justify-content-center">
                    <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-envelope"></i></a>
                    <a href="#" class="btn btn-outline-primary"><i class="fas fa-phone"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Contact Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($profileData['email']); ?>
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-phone me-2"></i>
                        <?php echo $profileData['phone'] ? htmlspecialchars($profileData['phone']) : 'Not provided'; ?>
                    </li>
                    <li>
                        <i class="fas fa-calendar-alt me-2"></i>
                        Joined <?php echo formatDate($profileData['created_at']); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Profile Details -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Profile Details</h5>
                    <?php if ($currentUser['user_id'] == $profileUserId || $currentUser['role_id'] == 1): ?>
                        <a href="profile_edit.php?id=<?php echo $profileUserId; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($profileData['first_name']); ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($profileData['last_name']); ?>" readonly>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($profileData['username']); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($profileData['email']); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="<?php echo $profileData['phone'] ? htmlspecialchars($profileData['phone']) : 'Not provided'; ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">User Category</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($profileData['user_category']); ?>" readonly>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <input type="text" class="form-control" value="<?php echo $profileData['is_active'] ? 'Active' : 'Inactive'; ?>" readonly>
                </div>
            </div>
        </div>
        
        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <h5>Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php
                $activities = $userActions->getUserActivityLog($profileUserId, 5);
                if (empty($activities['activities'])): ?>
                    <p class="text-muted">No recent activity</p>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($activities['activities'] as $activity): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo ucfirst($activity['activity_type']); ?></strong>
                                        <?php if ($activity['activity_type'] === 'file_access' && isset($activity['file_name'])): ?>
                                            - <?php echo htmlspecialchars($activity['original_name']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo formatDate($activity['activity_time']); ?></small>
                                </div>
                                <small class="text-muted">
                                    <?php echo $activity['ip_address']; ?> • <?php echo $activity['user_agent']; ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
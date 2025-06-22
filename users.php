<?php
$pageTitle = "User Management";
$pageScript = 'users.js';
require_once 'userincludes/header.php';

require_once 'includes/users.php';
require_once 'includes/auth.php';
$userActions = new UserActions();

// Get all users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$filters = [
    'search' => $_GET['search'] ?? '',
    'is_active' => isset($_GET['is_active']) ? (bool)$_GET['is_active'] : null,
    'role_id' => isset($_GET['role_id']) ? (int)$_GET['role_id'] : null,
    'department_id' => isset($_GET['department_id']) ? (int)$_GET['department_id'] : null,
    'sort' => $_GET['sort'] ?? 'u.created_at',
    'order' => $_GET['order'] ?? 'DESC'
];

$usersData = $userActions->getAllUsers($page, $perPage, $filters);
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1>User Management</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="users_create.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create User
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Filters</h5>
    </div>
    <div class="card-body">
        <form method="get" action="users.php">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select" id="is_active" name="is_active">
                            <option value="">All</option>
                            <option value="1" <?php echo $filters['is_active'] === true ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo $filters['is_active'] === false ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select class="form-select" id="role_id" name="role_id">
                            <option value="">All</option>
                            <option value="1" <?php echo $filters['role_id'] === 1 ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" <?php echo $filters['role_id'] === 2 ? 'selected' : ''; ?>>Dept Head</option>
                            <option value="3" <?php echo $filters['role_id'] === 3 ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            <?php
                            $departments = $userActions->getAllDepartments();
                            foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>" 
                                    <?php echo $filters['department_id'] === $dept['department_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="users.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            <a href="?<?php echo buildSortQuery('u.username', $filters); ?>">
                                Username <?php echo getSortArrow('u.username', $filters); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?<?php echo buildSortQuery('u.email', $filters); ?>">
                                Email <?php echo getSortArrow('u.email', $filters); ?>
                            </a>
                        </th>
                        <th>
                            <a href="?<?php echo buildSortQuery('full_name', $filters); ?>">
                                Name <?php echo getSortArrow('full_name', $filters); ?>
                            </a>
                        </th>
                        <th>Roles</th>
                        <th>Departments</th>
                        <th>
                            <a href="?<?php echo buildSortQuery('u.created_at', $filters); ?>">
                                Created <?php echo getSortArrow('u.created_at', $filters); ?>
                            </a>
                        </th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usersData['users'])): ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usersData['users'] as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['roles']); ?></td>
                                <td><?php echo htmlspecialchars($user['departments']); ?></td>
                                <td><?php echo formatDate($user['user_created']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['user_active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['user_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" 
                                                id="userActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="userActionsDropdown">
                                            <li><a class="dropdown-item" href="profile.php?id=<?php echo $user['user_id']; ?>">View Profile</a></li>
                                            <li><a class="dropdown-item" href="users_edit.php?id=<?php echo $user['user_id']; ?>">Edit</a></li>
                                            <?php if ($user['user_active']): ?>
                                                <li><a class="dropdown-item text-danger" href="users_deactivate.php?id=<?php echo $user['user_id']; ?>">Deactivate</a></li>
                                            <?php else: ?>
                                                <li><a class="dropdown-item text-success" href="users_activate.php?id=<?php echo $user['user_id']; ?>">Activate</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($usersData['total_pages'] > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo buildPageQuery($page - 1, $filters); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $usersData['total_pages']; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?<?php echo buildPageQuery($i, $filters); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $usersData['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php echo buildPageQuery($page + 1, $filters); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'footer.php';

// Helper functions
function buildSortQuery($field, $filters) {
    $query = http_build_query([
        'search' => $filters['search'],
        'is_active' => $filters['is_active'],
        'role_id' => $filters['role_id'],
        'department_id' => $filters['department_id'],
        'sort' => $field,
        'order' => $filters['sort'] === $field && $filters['order'] === 'ASC' ? 'DESC' : 'ASC'
    ]);
    return $query;
}

function getSortArrow($field, $filters) {
    if ($filters['sort'] === $field) {
        return $filters['order'] === 'ASC' ? '↑' : '↓';
    }
    return '';
}

function buildPageQuery($page, $filters) {
    $query = http_build_query([
        'search' => $filters['search'],
        'is_active' => $filters['is_active'],
        'role_id' => $filters['role_id'],
        'department_id' => $filters['department_id'],
        'sort' => $filters['sort'],
        'order' => $filters['order'],
        'page' => $page
    ]);
    return $query;
}
?>
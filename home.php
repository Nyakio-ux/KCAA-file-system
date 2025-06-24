<?php
$pageTitle = "Dashboard";
require_once 'userincludes/header.php';
require_once 'includes/auth.php';
require_once 'includes/dashboard.php';
$dashboard = new Dashboard();

$dashboardData = $dashboard->getDashboardData($currentUser['user_id']);
?>

<!DOCTYPE html>
<html lang="en" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - File Management System</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        secondary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in',
                        'spin-slow': 'spin 3s linear infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .sidebar-transition {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="w-64 fixed h-full bg-white dark:bg-gray-800 shadow-lg sidebar-transition z-50" id="sidebar">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3">
                    <div class="bg-primary-600 text-white p-2 rounded-lg">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">File Management</h1>
                </div>
            </div>
            
            <div class="p-4 overflow-y-auto">
                <ul class="space-y-2">
                    <?php foreach ($menus as $key => $menu): ?>
                        <?php if ($menu['permission']): ?>
                            <li>
                                <a href="<?php echo $menu['url']; ?>" class="flex items-center p-3 rounded-lg hover:bg-primary-50 dark:hover:bg-gray-700 group <?php echo ($currentPage === basename($menu['url'])) ? 'bg-primary-50 dark:bg-gray-700 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                                    <i class="<?php echo $menu['icon']; ?> mr-3 <?php echo ($currentPage === basename($menu['url'])) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400'; ?>"></i>
                                    <span><?php echo $menu['title']; ?></span>
                                </a>
                                
                                <?php if (isset($menu['submenus'])): ?>
                                    <ul class="pl-4 mt-1 space-y-1">
                                        <?php foreach ($menu['submenus'] as $subkey => $submenu): ?>
                                            <li>
                                                <a href="<?php echo $submenu['url']; ?>" class="flex items-center p-2 rounded-lg hover:bg-primary-50 dark:hover:bg-gray-700 group <?php echo ($currentPage === basename($submenu['url'])) ? 'text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-400'; ?>">
                                                    <span class="text-sm"><?php echo $submenu['title']; ?></span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-sm font-medium text-primary-800 dark:text-primary-400"><?php echo substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1); ?></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-white"><?php echo $currentUser['first_name'] . ' ' . $currentUser['last_name']; ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo $currentUser['role_name']; ?></p>
                        </div>
                    </div>
                    <button id="darkModeToggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon text-gray-600 dark:text-yellow-300 hidden dark:block"></i>
                        <i class="fas fa-sun text-gray-600 dark:text-gray-300 block dark:hidden"></i>
                    </button>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 ml-64 transition-all duration-300" id="mainContent">
            <!-- Top Navigation -->
            <nav class="bg-white dark:bg-gray-800 shadow-sm">
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="sidebarToggle" class="mr-4 text-gray-600 dark:text-gray-300 focus:outline-none lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800 dark:text-white"><?php echo $pageTitle; ?></h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                        <div class="relative">
                            <button class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                                <i class="fas fa-envelope"></i>
                                <span class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Dashboard Content -->
            <main class="p-6">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                            <?php if ($currentUser['role_id'] == 1): ?>
                                Admin Dashboard
                            <?php elseif ($currentUser['role_id'] == 2): ?>
                                Department Head Dashboard
                            <?php else: ?>
                                User Dashboard
                            <?php endif; ?>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400">Welcome back, <?php echo $currentUser['first_name']; ?>!</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Dashboard Content -->
                <div class="space-y-6">
                    <?php if ($currentUser['role_id'] == 1): // Admin Dashboard ?>
                        <!-- User Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Total Users</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['user_stats']['total_users']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300">
                                        <i class="fas fa-users text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-green-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 12% from last month
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Active Users</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['user_stats']['active_users']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300">
                                        <i class="fas fa-user-check text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-green-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 8% from last month
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Admins</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['user_stats']['admin_users']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-300">
                                        <i class="fas fa-user-shield text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 mt-4 flex items-center">
                                    <i class="fas fa-equals mr-1"></i> No change
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Dept. Heads</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['user_stats']['dept_head_users']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300">
                                        <i class="fas fa-user-tie text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-red-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-down mr-1"></i> 2% from last month
                                </p>
                            </div>
                        </div>
                        
                        <!-- File Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Total Files</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['file_stats']['total_files']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300">
                                        <i class="fas fa-file-alt text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-green-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 24% from last month
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Total Storage</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo formatBytes($dashboardData['file_stats']['total_storage']); ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-300">
                                        <i class="fas fa-database text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-green-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 18% from last month
                                </p>
                            </div>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 card-hover transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">Total Shares</p>
                                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2"><?php echo $dashboardData['file_stats']['total_shares']; ?></h3>
                                    </div>
                                    <div class="p-3 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-600 dark:text-pink-300">
                                        <i class="fas fa-share-alt text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-sm text-green-500 mt-4 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 32% from last month
                                </p>
                            </div>
                        </div>
                        
                        <!-- Recent Activities and Department Stats -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Recent Activities -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Recent Activities</h3>
                                </div>
                                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($dashboardData['recent_activities'] as $activity): ?>
                                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                    <i class="fas fa-bell"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <h4 class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($activity['title']); ?></h4>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($activity['message']); ?></p>
                                                    <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                                                        <span><?php echo htmlspecialchars($activity['sender_username']); ?></span>
                                                        <span class="mx-1">•</span>
                                                        <span><?php echo formatDate($activity['created_at']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-700 text-center">
                                    <a href="#" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">View all activities</a>
                                </div>
                            </div>
                            
                            <!-- Department Stats -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Department Statistics</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Department</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Users</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Files</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Shares</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <?php foreach ($dashboardData['department_stats'] as $dept): ?>
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo $dept['user_count']; ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo $dept['file_count']; ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo $dept['share_count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($currentUser['role_id'] == 2): // Department Head Dashboard ?>
                        <!-- Department Head Dashboard Content -->
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Department Info -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Department Information</h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <div class="mb-4">
                                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Department Name</h4>
                                                <p class="mt-1 text-gray-800 dark:text-white"><?php echo htmlspecialchars($dashboardData['department_info']['department_name']); ?></p>
                                            </div>
                                            <div class="mb-4">
                                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h4>
                                                <p class="mt-1 text-gray-800 dark:text-white"><?php echo htmlspecialchars($dashboardData['department_info']['description']); ?></p>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="mb-4">
                                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Head</h4>
                                                <p class="mt-1 text-gray-800 dark:text-white"><?php echo htmlspecialchars($dashboardData['department_info']['head_name']); ?></p>
                                            </div>
                                            <div class="mb-4">
                                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Members</h4>
                                                <p class="mt-1 text-gray-800 dark:text-white"><?php echo $dashboardData['department_info']['member_count']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Department Files and Pending Approvals -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Department Files -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                        <h3 class="font-semibold text-gray-800 dark:text-white">Recent Department Files</h3>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File Name</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                <?php foreach ($dashboardData['department_files'] as $file): ?>
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="flex-shrink-0 h-10 w-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                                    <i class="fas fa-file"></i>
                                                                </div>
                                                                <div class="ml-4">
                                                                    <div class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['original_name']); ?></div>
                                                                    <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($file['uploaded_by']); ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($file['category_name']); ?></td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo getStatusBadge($file['current_status']); ?>-100 dark:bg-<?php echo getStatusBadge($file['current_status']); ?>-900 text-<?php echo getStatusBadge($file['current_status']); ?>-800 dark:text-<?php echo getStatusBadge($file['current_status']); ?>-300">
                                                                <?php echo $file['current_status']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Pending Approvals -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                        <h3 class="font-semibold text-gray-800 dark:text-white">Pending Approvals</h3>
                                    </div>
                                    <div class="p-6">
                                        <?php if (empty($dashboardData['pending_approvals'])): ?>
                                            <div class="text-center py-8">
                                                <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500">
                                                    <i class="fas fa-check-circle text-3xl"></i>
                                                </div>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No pending approvals</h3>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">All caught up!</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="space-y-4">
                                                <?php foreach ($dashboardData['pending_approvals'] as $approval): ?>
                                                    <div class="flex items-start p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                        <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center text-yellow-600 dark:text-yellow-300">
                                                            <i class="fas fa-clock"></i>
                                                        </div>
                                                        <div class="ml-4 flex-1">
                                                            <div class="flex items-center justify-between">
                                                                <h4 class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($approval['original_name']); ?></h4>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo formatDate($approval['request_date']); ?></span>
                                                            </div>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">From: <?php echo htmlspecialchars($approval['source_department']); ?></p>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">Uploaded by: <?php echo htmlspecialchars($approval['uploaded_by']); ?></p>
                                                            <div class="mt-2 flex space-x-3">
                                                                <a href="approve_file.php?id=<?php echo $approval['file_id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                    Approve
                                                                </a>
                                                                <a href="reject_file.php?id=<?php echo $approval['file_id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                    Reject
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Department Members -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                    <h3 class="font-semibold text-gray-800 dark:text-white">Department Members</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Username</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <?php foreach ($dashboardData['department_members'] as $member): ?>
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                                <?php echo substr($member['full_name'], 0, 1); ?>
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($member['full_name']); ?></div>
                                                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($member['user_category']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($member['username']); ?></td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $member['is_active'] ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300'; ?>">
                                                            <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: // Regular User Dashboard ?>
                        <!-- Regular User Dashboard Content -->
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Department Info -->
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">My Department</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Department Name</h4>
                                           <p class="mt-1 text-gray-800 dark:text-white">
                                                <?php echo is_array($dashboardData['department_info']) ? htmlspecialchars($dashboardData['department_info']['department_name']) : 'N/A'; ?>
                                            </p>
                                        </div>
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</h4>
                                            <p class="mt-1 text-gray-800 dark:text-white">
                                                <?php echo is_array($dashboardData['department_info']) ? htmlspecialchars($dashboardData['department_info']['department_name']) : 'N/A'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">Head</h4>
                                            <p class="mt-1 text-gray-800 dark:text-white">
                                            <?php echo is_array($dashboardData['department_info']) ? htmlspecialchars($dashboardData['department_info']['department_name']) : 'N/A'; ?>
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- My Files and Shared Files -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- My Files -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                        <h3 class="font-semibold text-gray-800 dark:text-white">My Recent Files</h3>
                                    </div>
                                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($dashboardData['user_files'] as $file): ?>
                                            <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-300">
                                                        <i class="fas fa-file"></i>
                                                    </div>
                                                    <div class="ml-4 flex-1">
                                                        <div class="flex items-center justify-between">
                                                            <h4 class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['original_name']); ?></h4>
                                                            <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo formatDate($file['upload_date']); ?></span>
                                                        </div>
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><?php echo htmlspecialchars($file['category_name']); ?></p>
                                                        <div class="mt-2">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-<?php echo getStatusBadge($file['current_status']); ?>-100 dark:bg-<?php echo getStatusBadge($file['current_status']); ?>-900 text-<?php echo getStatusBadge($file['current_status']); ?>-800 dark:text-<?php echo getStatusBadge($file['current_status']); ?>-300">
                                                                <?php echo $file['current_status']; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-700 text-center">
                                        <a href="#" class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">View all my files</a>
                                    </div>
                                </div>
                                
                                <!-- Shared Files -->
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
                                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                                        <h3 class="font-semibold text-gray-800 dark:text-white">Shared Files</h3>
                                    </div>
                                    <div class="p-6">
                                        <?php if (empty($dashboardData['shared_files'])): ?>
                                            <div class="text-center py-8">
                                                <div class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500">
                                                    <i class="fas fa-folder-open text-3xl"></i>
                                                </div>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No shared files</h3>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Files shared with your department will appear here</p>
                                            </div>
                                        <?php else: ?>
                                            <div class="space-y-4">
                                                <?php foreach ($dashboardData['shared_files'] as $file): ?>
                                                    <div class="flex items-start p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-300">
                                                            <i class="fas fa-share-alt"></i>
                                                        </div>
                                                        <div class="ml-4 flex-1">
                                                            <div class="flex items-center justify-between">
                                                                <h4 class="text-sm font-medium text-gray-800 dark:text-white"><?php echo htmlspecialchars($file['original_name']); ?></h4>
                                                                <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo formatDate($file['share_date']); ?></span>
                                                            </div>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">From: <?php echo htmlspecialchars($file['shared_from']); ?></p>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">Shared by: <?php echo htmlspecialchars($file['shared_by']); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    

    <!-- Scripts -->
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Dark mode toggle
        document.getElementById('darkModeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                document.cookie = 'darkMode=false; path=/; max-age=31536000; SameSite=Lax';
            } else {
                html.classList.add('dark');
                document.cookie = 'darkMode=true; path=/; max-age=31536000; SameSite=Lax';
            }
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && !sidebar.contains(event.target) && event.target !== sidebarToggle) {
                sidebar.classList.remove('active');
            }
        });
        
        // Adjust main content margin when sidebar is toggled
        function adjustMainContent() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                if (sidebar.classList.contains('active')) {
                    mainContent.style.marginLeft = '0';
                } else {
                    mainContent.style.marginLeft = '0';
                }
            } else {
                mainContent.style.marginLeft = '16rem'; // 64 = 16rem
            }
        }
        
        // Initial adjustment
        adjustMainContent();
        
        // Adjust on window resize
        window.addEventListener('resize', adjustMainContent);
    </script>
    
    <?php
    // Helper functions
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    function formatDate($dateString) {
        return date('M j, Y H:i', strtotime($dateString));
    }
    
    function getStatusBadge($status) {
        switch (strtolower($status)) {
            case 'approved': return 'green';
            case 'rejected': return 'red';
            case 'pending review': return 'yellow';
            case 'under review': return 'blue';
            case 'pending approval': return 'indigo';
            case 'revision required': return 'purple';
            default: return 'gray';
        }
    }
    ?>
</body>
</html>
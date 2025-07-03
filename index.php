<?php
/**
 * Index Page - KCAA SmartFiles
 * 
 */

// Start session
session_start();

// Check if user is already logged in 
if (isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true) {
    header('Location: home.php');
    exit();
}

// If redirect parameter is set, go directly to login
if (isset($_GET['redirect']) && $_GET['redirect'] === 'now') {
    header('Location: login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KCAA SmartFiles - Loading</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        .spinner-reverse {
            animation: spin 1.5s linear infinite reverse;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-gray-900 flex items-center justify-center">
    <div class="flex flex-col items-center">
        <img src="assets/images/logo.png" alt="KCAA Logo" class="mb-8 w-32 h-auto">
    <div class="relative">
        <div 
            class="w-16 h-16 border-4 border-blue-200/30 border-t-blue-500 rounded-full spinner">
        </div>
        <div 
            class="absolute inset-0 w-16 h-16 border-4 border-transparent border-r-orange-500 rounded-full spinner-reverse">
        </div>
    </div>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = 'login';
        }, 3000);
    </script>
</body>
</html>
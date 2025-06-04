<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary SEO Meta Tags -->
    <title>KCAA SmartFiles - Secure File Management System | Login Portal</title>
    <meta name="title" content="KCAA SmartFiles - Secure File Management System | Login Portal">
    <meta name="description" content="Access your KCAA SmartFiles account. Secure file management system for Kenya Civil Aviation Authority with enterprise-grade security and document tracking.">
    <meta name="keywords" content="KCAA, file management, document management, Kenya Civil Aviation Authority, secure login, file tracking">
    <meta name="author" content="KCAA">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Security Headers -->
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#1E40AF">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- External Stylesheets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF', 
                        primaryLight: '#3B82F6',
                        primaryDark: '#1E3A8A',
                        secondary: '#EA580C', 
                        secondaryLight: '#F97316', 
                        secondaryDark: '#C2410C', 
                        accent: '#F59E0B', 
                        dark: '#0F172A', 
                        darker: '#1E293B', 
                        light: '#F8FAFC', 
                        gray: {
                            850: '#1a202c',
                        }
                    },
                    animation: {
                        fadeIn: 'fadeIn 0.8s ease-out',
                        slideUp: 'slideUp 0.6s ease-out',
                        float: 'float 6s ease-in-out infinite',
                        pulse: 'pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        glow: 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        fadeIn: {
                            'from': { opacity: '0', transform: 'translateY(20px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            'from': { opacity: '0', transform: 'translateY(30px)' },
                            'to': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                            '33%': { transform: 'translateY(-15px) rotate(2deg)' },
                            '66%': { transform: 'translateY(8px) rotate(-1deg)' },
                        },
                        glow: {
                            'from': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.2)' },
                            'to': { boxShadow: '0 0 30px rgba(59, 130, 246, 0.3)' },
                        }
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-gray-900 relative overflow-hidden">
    
    <div class="fixed inset-0 pointer-events-none">
        <!-- Floating Documents -->
        <div class="absolute top-[15%] left-[10%] text-blue-500/10 text-3xl animate-float">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="absolute top-[25%] right-[12%] text-orange-500/10 text-2xl animate-float" style="animation-delay: 1.5s;">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="absolute bottom-[30%] left-[8%] text-blue-400/10 text-2xl animate-float" style="animation-delay: 3s;">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div class="absolute bottom-[15%] right-[15%] text-orange-400/10 text-3xl animate-float" style="animation-delay: 4.5s;">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="absolute top-[50%] left-[5%] text-blue-500/10 text-2xl animate-float" style="animation-delay: 2s;">
            <i class="fas fa-database"></i>
        </div>
        <div class="absolute top-[60%] right-[8%] text-orange-500/10 text-2xl animate-float" style="animation-delay: 6s;">
            <i class="fas fa-lock"></i>
        </div>
        
        <!-- Gradient Orbs -->
        <div class="absolute top-20 -left-20 w-72 h-72 bg-blue-600 rounded-full mix-blend-multiply filter blur-xl opacity-5 animate-pulse"></div>
        <div class="absolute bottom-20 -right-20 w-72 h-72 bg-orange-600 rounded-full mix-blend-multiply filter blur-xl opacity-5 animate-pulse" style="animation-delay: 3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-blue-500 to-orange-500 rounded-full mix-blend-multiply filter blur-3xl opacity-3 animate-pulse" style="animation-delay: 1.5s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 overflow-hidden animate-slideUp">
                <div class="px-8 pt-8 pb-6 text-center bg-slate-800/20">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-orange-600 text-white text-3xl shadow-lg shadow-blue-500/20 mb-6 animate-glow">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-orange-400 bg-clip-text text-transparent mb-2">
                        KCAA SmartFiles
                    </h1>
                    <h2 class="text-xl font-semibold text-white mb-2">File Management Portal</h2>
                    <p class="text-blue-200 text-sm">Kenya Civil Aviation Authority</p>
                </div>

                <!-- Form Section -->
                <div class="px-8 py-8 bg-slate-800/20">
                    <div class="mb-6 p-4 rounded-lg bg-blue-500/10 border border-blue-400/30 text-blue-200 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle mr-3 text-blue-400"></i>
                            <div>
                                <p class="font-medium">Secure Access Required</p>
                                <p class="text-xs mt-1 text-blue-300">Use your authorized KCAA credentials to access the system</p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="loginForm" class="space-y-6">
                        <!-- Username Field -->
                        <div class="space-y-2">
                            <label for="username" class="block text-gray-200 text-sm font-medium">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    placeholder="Enter your username" 
                                    required
                                    class="w-full pl-10 pr-4 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30"
                                >
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="password" class="block text-gray-200 text-sm font-medium">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-lock text-sm"></i>
                                </div>
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password" 
                                    required
                                    class="w-full pl-10 pr-12 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30"
                                >
                                <button 
                                    type="button" 
                                    id="togglePassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors duration-200"
                                >
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center text-gray-300">
                                <input type="checkbox" id="rememberMe" class="mr-2 rounded border-gray-600 bg-white/10 text-blue-500 focus:ring-blue-400">
                                Remember me
                            </label>
                            <a href="#" class="text-orange-400 hover:text-orange-300 hover:underline transition-colors">
                                Forgot password?
                            </a>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="w-full py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                        >
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Access System
                        </button>
                    </form>
                </div>
            </div>

            <!-- Footer Information -->
            <div class="mt-8 text-center space-y-4">
                <!-- Security Badge -->
                <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-gray-300 text-xs border border-white/20">
                    <i class="fas fa-shield-alt text-blue-400 mr-2"></i>
                    Secure & encryption
                </div>
                
                <!-- System Status -->
                <div class="flex items-center justify-center space-x-4 text-xs text-gray-400">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                        System Online
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        Last Updated: <span id="currentTime"></span>
                    </div>
                </div>
                
                <!-- Copyright -->
                <p class="text-xs text-gray-500">
                    © 2025 Kenya Civil Aviation Authority. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>

</body>
</html>
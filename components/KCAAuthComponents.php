<?php
/**
 * KCAA Authentication Components
 * components for login and password recovery
 */

class KCAAuthComponents {
    
    /**
     * Render the common HTML head section
     */
    public static function renderHead($title = "KCAA SmartFiles", $description = "Secure file management system") {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            
            <!-- Primary SEO Meta Tags -->
            <title><?php echo htmlspecialchars($title); ?></title>
            <meta name="title" content="<?php echo htmlspecialchars($title); ?>">
            <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
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
        <?php
    }
    
    /**
     * Render the authentication layout wrapper
     */
    public static function renderAuthLayout($config = []) {
        $defaults = [
            'icon' => 'folder-open',
            'title' => 'KCAA SmartFiles',
            'subtitle' => 'File Management Portal',
            'description' => 'Kenya Civil Aviation Authority',
            'info_title' => 'Secure Access Required',
            'info_text' => 'Use your authorized KCAA credentials to access the system'
        ];
        
        $config = array_merge($defaults, $config);
        ?>
        <body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-gray-900 relative">
            <!-- Main Content -->
            <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md">
                    <!-- Auth Card -->
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 overflow-hidden animate-slideUp">
                        <div class="px-8 pt-8 pb-6 text-center bg-slate-800/20">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-orange-600 text-white text-3xl shadow-lg shadow-blue-500/20 mb-6 animate-glow">
                                <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                            </div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-orange-400 bg-clip-text text-transparent mb-2">
                                <?php echo htmlspecialchars($config['title']); ?>
                            </h1>
                            <h2 class="text-xl font-semibold text-white mb-2"><?php echo htmlspecialchars($config['subtitle']); ?></h2>
                            <p class="text-blue-200 text-sm"><?php echo htmlspecialchars($config['description']); ?></p>
                        </div>

                        <!-- Form Section -->
                        <div class="px-8 py-8 bg-slate-800/20">
                            <div class="mb-6 p-4 rounded-lg bg-blue-500/10 border border-blue-400/30 text-blue-200 text-sm">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle mr-3 text-blue-400"></i>
                                    <div>
                                        <p class="font-medium"><?php echo htmlspecialchars($config['info_title']); ?></p>
                                        <p class="text-xs mt-1 text-blue-300"><?php echo htmlspecialchars($config['info_text']); ?></p>
                                    </div>
                                </div>
                            </div>
        <?php
    }
    
    /**
     * Render login form
     */
    public static function renderLoginForm($action = "", $method = "POST") {
        ?>
        <form id="loginForm" action="<?php echo htmlspecialchars($action); ?>" method="<?php echo $method; ?>" class="space-y-6">
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
            
            <!-- Password Field -->
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
            
            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-gray-300">
                    <input type="checkbox" id="rememberMe" name="remember_me" 
                    class="mr-2 rounded border-gray-600 bg-white/10 text-blue-500 focus:ring-blue-400">
                    Remember me
                </label>
                <a href="forgotpassword" 
                    class="text-orange-400 hover:text-orange-300 hover:underline transition-colors">
                    Forgot password?
                </a>
            </div>
            
            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Access System
            </button>
        </form>
        <?php
    }
    
    /**
     * Render password recovery form
     */
    public static function renderPasswordRecoveryForm($action = "", $method = "POST") {
        ?>
        <form id="passwordResetForm" action="<?php echo htmlspecialchars($action); ?>" method="<?php echo $method; ?>" class="space-y-6">
            <!-- Email Field -->
            <div class="space-y-2">
                <label for="email" class="block text-gray-200 text-sm font-medium">Registered Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-envelope text-sm"></i>
                    </div>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your registered email" 
                        required
                        class="w-full pl-10 pr-4 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30"
                    >
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50"
                >
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reset Link
                </button>
            </div>
            
            <!-- Return to Login Link -->
            <div class="text-center text-sm text-gray-300">
                Remember your password? 
                <a href="login" class="text-orange-400 hover:text-orange-300 hover:underline transition-colors">
                    Return to login
                </a>
            </div>
        </form>
        <?php
    }
    
    /**
     * Render footer
     */
    public static function renderFooter() {
        ?>
                        </div>
                    </div>

                    <!-- Footer Information -->
                    <div class="mt-8 text-center space-y-4">
                        <!-- Security Badge -->
                        <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-gray-300 text-xs border border-white/20">
                            <i class="fas fa-shield-alt text-blue-400 mr-2"></i>
                            Secure & encrypted transmission
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
                            © <?php echo date('Y'); ?> Kenya Civil Aviation Authority. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>

           <script src="../assets/js/footer.js"></script>
        </body>
        </html>
        <?php
    }
    
    /**
     * Render input field component
     */
    public static function renderInputField($config = []) {
        $defaults = [
            'type' => 'text',
            'name' => '',
            'id' => '',
            'label' => '',
            'placeholder' => '',
            'icon' => '',
            'required' => false,
            'value' => '',
            'classes' => ''
        ];
        
        $config = array_merge($defaults, $config);
        ?>
        <div class="space-y-2">
            <?php if ($config['label']): ?>
            <label for="<?php echo htmlspecialchars($config['id']); ?>" class="block text-gray-200 text-sm font-medium">
                <?php echo htmlspecialchars($config['label']); ?>
            </label>
            <?php endif; ?>
            <div class="relative">
                <?php if ($config['icon']): ?>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-<?php echo $config['icon']; ?> text-sm"></i>
                </div>
                <?php endif; ?>
                <input 
                    type="<?php echo htmlspecialchars($config['type']); ?>" 
                    id="<?php echo htmlspecialchars($config['id']); ?>" 
                    name="<?php echo htmlspecialchars($config['name']); ?>" 
                    placeholder="<?php echo htmlspecialchars($config['placeholder']); ?>" 
                    value="<?php echo htmlspecialchars($config['value']); ?>"
                    <?php echo $config['required'] ? 'required' : ''; ?>
                    class="w-full <?php echo $config['icon'] ? 'pl-10' : 'pl-4'; ?> pr-4 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30 <?php echo $config['classes']; ?>"
                >
            </div>
        </div>
        <?php
    }


    /**
     * Render password reset form
     */
    public static function renderPasswordResetForm($action = "", $method = "POST", $token = "") {
    ?>
    <form id="passwordResetForm" action="<?php echo htmlspecialchars($action); ?>" method="<?php echo $method; ?>" class="space-y-6">
        <!-- Hidden token field -->
        <?php if ($token): ?>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <?php endif; ?>
        
        <!-- New Password Field -->
        <div class="space-y-2">
            <label for="new_password" class="block text-gray-200 text-sm font-medium">New Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </div>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    placeholder="Enter your new password" 
                    required
                    minlength="8"
                    class="w-full pl-10 pr-12 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30"
                >
                <button 
                    type="button" 
                    id="toggleNewPassword" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors duration-200"
                >
                    <i class="fas fa-eye text-sm"></i>
                </button>
            </div>
            <!-- Password strength indicator -->
            <div class="mt-2">
                <div class="flex items-center space-x-2">
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div id="passwordStrength" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <span id="passwordStrengthText" class="text-xs text-gray-400">Weak</span>
                </div>
            </div>
        </div>
        
        <!-- Confirm Password Field -->
        <div class="space-y-2">
            <label for="confirm_password" class="block text-gray-200 text-sm font-medium">Confirm New Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </div>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Confirm your new password" 
                    required
                    class="w-full pl-10 pr-12 py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30"
                >
                <button 
                    type="button" 
                    id="toggleConfirmPassword" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors duration-200"
                >
                    <i class="fas fa-eye text-sm"></i>
                </button>
            </div>
            <!-- Password match indicator -->
            <div id="passwordMatchIndicator" class="mt-2 text-xs hidden">
                <div class="flex items-center">
                    <i id="matchIcon" class="fas fa-times text-red-400 mr-2"></i>
                    <span id="matchText" class="text-red-400">Passwords do not match</span>
                </div>
            </div>
        </div>
        
        <!-- Password Requirements -->
        <div class="bg-slate-700/30 rounded-lg p-4 text-sm">
            <h4 class="text-gray-200 font-medium mb-2">Password Requirements:</h4>
            <ul class="space-y-1 text-gray-300 text-xs">
                <li class="flex items-center">
                    <i id="req-length" class="fas fa-times text-red-400 mr-2 w-3"></i>
                    At least 8 characters long
                </li>
                <li class="flex items-center">
                    <i id="req-uppercase" class="fas fa-times text-red-400 mr-2 w-3"></i>
                    One uppercase letter
                </li>
                <li class="flex items-center">
                    <i id="req-lowercase" class="fas fa-times text-red-400 mr-2 w-3"></i>
                    One lowercase letter
                </li>
                <li class="flex items-center">
                    <i id="req-number" class="fas fa-times text-red-400 mr-2 w-3"></i>
                    One number
                </li>
                <li class="flex items-center">
                    <i id="req-special" class="fas fa-times text-red-400 mr-2 w-3"></i>
                    One special character (!@#$%^&*)
                </li>
            </ul>
        </div>
        
        <!-- Submit Button -->
        <div class="pt-2">
            <button 
                type="submit" 
                id="resetSubmitBtn"
                disabled
                class="w-full py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white font-semibold rounded-lg transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-400/50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-key mr-2"></i>
                Reset Password
            </button>
        </div>
        
        <!-- Return to Login Link -->
        <div class="text-center text-sm text-gray-300">
            Remember your password? 
            <a href="login" class="text-orange-400 hover:text-orange-300 hover:underline transition-colors">
                Return to login
            </a>
        </div>
    </form>

    <script src="../assets/js/resetpassword.js"></script>
    <?php
}

}
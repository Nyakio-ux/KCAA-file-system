<?php
/**
 * KCAA Authentication Head Component
 * Renders the common HTML head section with styling and meta tags
 */

class AuthHead {
    
    /**
     * Render the common HTML head section
     */
    public static function render($title = "KCAA SmartFiles", $description = "Secure file management system") {
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
}
?>
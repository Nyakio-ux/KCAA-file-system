<?php
/**
 * KCAA Authentication Footer Component
 * Renders the footer section with system status and scripts
 */

class AuthFooter {
    
    /**
     * Render footer
     */
    public static function render() {
        ?>
                        </div>
                    </div>
                    <div class="mt-8 text-center space-y-4">
                        <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-gray-300 text-xs border border-white/20">
                            <i class="fas fa-shield-alt text-blue-400 mr-2"></i>
                            Secure & encrypted transmission
                        </div>
                        
                        <p class="text-xs text-gray-500">
                            © <?php echo date('Y'); ?> Kenya Civil Aviation Authority. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
            <script src="../assets/js/footer.js"></script>
        </body>
        </html>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.password-toggle').forEach(button => {
                    button.addEventListener('click', function() {
                        const targetId = this.dataset.target;
                        const passwordInput = document.getElementById(targetId);
                        const icon = this.querySelector('i');
                        
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
?>
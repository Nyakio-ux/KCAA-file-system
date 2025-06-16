<?php
/**
 * KCAA Password Components
 * Specialized components for password fields and validation
 */

class PasswordComponents {
    
    /**
     * Render password strength indicator
     */
    public static function renderPasswordStrengthIndicator($targetId = 'new_password') {
        ?>
        <div class="mt-2">
            <div class="flex items-center space-x-2">
                <div class="flex-1 bg-gray-700 rounded-full h-2">
                    <div id="passwordStrength" class="h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <span id="passwordStrengthText" class="text-xs text-gray-400">Weak</span>
            </div>
        </div>
        <?php
    }

    /**
     * Render password requirements checklist
     */
    public static function renderPasswordRequirements() {
        ?>
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
        <?php
    }

    /**
     * Render password match indicator
     */
    public static function renderPasswordMatchIndicator() {
        ?>
        <div id="passwordMatchIndicator" class="mt-2 text-xs hidden">
            <div class="flex items-center">
                <i id="matchIcon" class="fas fa-times text-red-400 mr-2"></i>
                <span id="matchText" class="text-red-400">Passwords do not match</span>
            </div>
        </div>
        <?php
    }
}
?>
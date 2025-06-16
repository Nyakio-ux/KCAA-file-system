<?php
/**
 * KCAA Form Components
 * Reusable form input, button, and field components
 */

class FormComponents {
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
            'classes' => '',
            'minlength' => null,
            'maxlength' => null,
            'autocomplete' => null,
            'wrapper_classes' => 'space-y-2',
            'show_toggle' => false, 
            'validation_rules' => [], 
            'help_text' => '', 
            'error_message' => '', 
        ];
        
        $config = array_merge($defaults, $config);
        $inputId = $config['id'] ?: $config['name'];
        ?>
        <div class="<?php echo $config['wrapper_classes']; ?>">
            <?php if ($config['label']): ?>
            <label for="<?php echo htmlspecialchars($inputId); ?>" class="block text-gray-200 text-sm font-medium">
                <?php echo htmlspecialchars($config['label']); ?>
                <?php if ($config['required']): ?>
                    <span class="text-red-400 ml-1">*</span>
                <?php endif; ?>
            </label>
            <?php endif; ?>
            
            <div class="relative">
                <?php if ($config['icon']): ?>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-<?php echo htmlspecialchars($config['icon']); ?> text-sm"></i>
                </div>
                <?php endif; ?>
                
                <input 
                    type="<?php echo htmlspecialchars($config['type']); ?>" 
                    id="<?php echo htmlspecialchars($inputId); ?>" 
                    name="<?php echo htmlspecialchars($config['name']); ?>" 
                    placeholder="<?php echo htmlspecialchars($config['placeholder']); ?>" 
                    value="<?php echo htmlspecialchars($config['value']); ?>"
                    <?php echo $config['required'] ? 'required' : ''; ?>
                    <?php echo $config['minlength'] ? 'minlength="' . $config['minlength'] . '"' : ''; ?>
                    <?php echo $config['maxlength'] ? 'maxlength="' . $config['maxlength'] . '"' : ''; ?>
                    <?php echo $config['autocomplete'] ? 'autocomplete="' . $config['autocomplete'] . '"' : ''; ?>
                    class="w-full <?php echo $config['icon'] ? 'pl-10' : 'pl-4'; ?> <?php echo $config['show_toggle'] ? 'pr-12' : 'pr-4'; ?> py-3 bg-white/10 border border-gray-300/30 rounded-lg text-white placeholder-gray-400 backdrop-blur-sm transition-all duration-200 focus:outline-none focus:border-gray-300/50 focus:ring-1 focus:ring-gray-300/30 <?php echo $config['classes']; ?>"
                >
                
                <?php if ($config['show_toggle'] && $config['type'] === 'password'): ?>
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors duration-200 password-toggle"
                    data-target="<?php echo htmlspecialchars($inputId); ?>"
                >
                    <i class="fas fa-eye text-sm"></i>
                </button>
                <?php endif; ?>
            </div>
            
            <?php if ($config['help_text']): ?>
            <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($config['help_text']); ?></p>
            <?php endif; ?>
            
            <?php if ($config['error_message']): ?>
            <p class="text-xs text-red-400 mt-1 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?php echo htmlspecialchars($config['error_message']); ?>
            </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function renderCheckboxField($config = []) {
        $defaults = [
            'name' => '',
            'id' => '',
            'label' => '',
            'value' => '1',
            'checked' => false,
            'classes' => '',
            'wrapper_classes' => 'flex items-center',
        ];
        
        $config = array_merge($defaults, $config);
        $inputId = $config['id'] ?: $config['name'];
        ?>
        <div class="<?php echo $config['wrapper_classes']; ?>">
            <input 
                type="checkbox" 
                id="<?php echo htmlspecialchars($inputId); ?>" 
                name="<?php echo htmlspecialchars($config['name']); ?>" 
                value="<?php echo htmlspecialchars($config['value']); ?>"
                <?php echo $config['checked'] ? 'checked' : ''; ?>
                class="mr-2 rounded border-gray-600 bg-white/10 text-blue-500 focus:ring-blue-400 <?php echo $config['classes']; ?>"
            >
            <?php if ($config['label']): ?>
            <label for="<?php echo htmlspecialchars($inputId); ?>" class="text-gray-300 text-sm">
                <?php echo htmlspecialchars($config['label']); ?>
            </label>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function renderSubmitButton($config = []) {
        $defaults = [
            'text' => 'Submit',
            'icon' => '',
            'id' => '',
            'classes' => '',
            'disabled' => false,
            'type' => 'submit',
            'wrapper_classes' => 'pt-2',
        ];
        
        $config = array_merge($defaults, $config);
        ?>
        <div class="<?php echo $config['wrapper_classes']; ?>">
            <button 
                type="<?php echo htmlspecialchars($config['type']); ?>"
                <?php echo $config['id'] ? 'id="' . htmlspecialchars($config['id']) . '"' : ''; ?>
                <?php echo $config['disabled'] ? 'disabled' : ''; ?>
                class="w-full py-3 bg-gradient-to-r from-blue-600 to-orange-600 hover:from-blue-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-xl hover:shadow-blue-500/25 focus:outline-none focus:ring-2 focus:ring-blue-400/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:from-gray-600 disabled:to-gray-700 <?php echo $config['classes']; ?>"
            >
                <?php if ($config['icon']): ?>
                <i class="fas fa-<?php echo htmlspecialchars($config['icon']); ?> mr-2"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($config['text']); ?>
            </button>
        </div>
        <?php
    }

    public static function renderLink($config = []) {
        $defaults = [
            'href' => '#',
            'text' => '',
            'classes' => 'text-orange-400 hover:text-orange-300 hover:underline transition-colors',
            'target' => '',
        ];
        
        $config = array_merge($defaults, $config);
        ?>
        <a 
            href="<?php echo htmlspecialchars($config['href']); ?>" 
            class="<?php echo $config['classes']; ?>"
            <?php echo $config['target'] ? 'target="' . htmlspecialchars($config['target']) . '"' : ''; ?>
        >
            <?php echo htmlspecialchars($config['text']); ?>
        </a>
        <?php
    }
}
?>
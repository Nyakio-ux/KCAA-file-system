<?php
/**
 * KCAA Authentication Layout Component
 * Renders the authentication layout wrapper with header section
 */

class AuthLayout {
    public static function render($config = []) {
        $defaults = [
            //'icon' => 'folder-open', 
            'logo' => 'assets/images/logo.png', 
            'title' => 'KCAA SmartFiles',
            'subtitle' => 'File Management Portal',
            'description' => 'Kenya Civil Aviation Authority',
            'info_title' => 'Secure Access Required',
            'info_text' => 'Use your authorized KCAA credentials to access the system'
        ];
        
        $config = array_merge($defaults, $config);
        ?>
        <body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-gray-900 relative">
            <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md">
                    <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl border border-white/20 overflow-hidden animate-slideUp">
                        <div class="px-8 pt-4 pb-2 text-center bg-slate-800/20">
                        <?php if (!empty($config['logo'])): ?>
                           <img src="<?php echo htmlspecialchars($config['logo']); ?>" alt="Logo" class="mx-auto mb-6 w-40 h-40 object-contain rounded-2xl shadow-lg">
                        <?php endif; ?>
                         
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-600 to-orange-600 text-white text-3xl shadow-lg shadow-blue-500/20 mb-6 animate-glow">
                                <i class="fas fa-<?php echo $config['icon']; ?>"></i>
                            </div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-orange-400 bg-clip-text text-transparent mb-2">
                                <?php echo htmlspecialchars($config['title']); ?>
                            </h1>
                            <h2 class="text-xl font-semibold text-white mb-2"><?php echo htmlspecialchars($config['subtitle']); ?></h2>
                            <p class="text-blue-200 text-sm"><?php echo htmlspecialchars($config['description']); ?></p>
                        </div>
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
}
?>
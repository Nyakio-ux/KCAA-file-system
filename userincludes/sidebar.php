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
                        <a href="<?php echo $menu['url']; ?>" 
                        class="flex items-center p-3 rounded-lg hover:bg-primary-50 dark:hover:bg-gray-700 group <?php echo ($currentPage === basename($menu['url'])) ? 'bg-primary-50 dark:bg-gray-700 text-primary-600 dark:text-primary-400' : 'text-gray-700 dark:text-gray-300'; ?>"
                        <?php if (isset($menu['attributes'])): ?>
                            <?php foreach ($menu['attributes'] as $attr => $value): ?>
                                <?php echo $attr . '="' . $value . '" '; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        >
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
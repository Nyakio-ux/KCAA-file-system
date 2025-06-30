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
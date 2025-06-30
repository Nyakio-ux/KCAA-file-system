<!-- modals/uploadModal.php -->
<div class="modal fade fixed top-0 left-0 hidden w-full h-full outline-none overflow-x-hidden overflow-y-auto z-50" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog relative w-auto pointer-events-none mx-auto mt-4 mb-4 px-4 max-w-xs sm:max-w-sm md:max-w-md lg:max-w-2xl xl:max-w-4xl">
        <div class="modal-content border-none shadow-lg relative flex flex-col w-full pointer-events-auto bg-white dark:bg-gray-800 bg-clip-padding rounded-md outline-none text-current">
            <!-- Modal header -->
            <div class="modal-header flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 rounded-t-md">
                <h5 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-white" id="uploadModalLabel">
                    Upload New File
                </h5>
                <button type="button" class="btn-close box-content w-4 h-4 p-1 text-gray-500 dark:text-gray-400 border-none rounded-none opacity-50 focus:shadow-none focus:outline-none focus:opacity-100 hover:text-gray-700 hover:opacity-75 hover:no-underline" data-bs-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Modal body -->
            <div class="modal-body relative p-4 sm:p-6 max-h-[70vh] overflow-y-auto">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <!-- File Name -->
                            <div>
                                <label for="file_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File Name*</label>
                                <input type="text" id="file_name" name="file_name" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Enter descriptive file name">
                            </div>
                            
                            <!-- Reference Number -->
                            <div>
                                <label for="reference_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference Number*</label>
                                <input type="text" id="reference_no" name="reference_no" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Enter reference number">
                            </div>
                            
                            <!-- Department -->
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Department*</label>
                                <select id="department" name="department" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Department</option>
                                    <!-- Departments will be populated via JavaScript -->
                                </select>
                            </div>
                            
                            <!-- Originator -->
                            <div>
                                <label for="originator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Originator*</label>
                                <input type="text" id="originator" name="originator" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Person who created the document">
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-4">
                            <!-- Destination -->
                            <div>
                                <label for="destination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destination*</label>
                                <select id="destination" name="destination" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="">Select Destination</option>
                                    <!-- Destinations will be populated via JavaScript -->
                                </select>
                            </div>
                            
                            <!-- Receiver -->
                            <div>
                                <label for="receiver" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receiver*</label>
                                <input type="text" id="receiver" name="receiver" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Who will receive this document?">
                            </div>
                            
                            <!-- Date of Origination -->
                            <div>
                                <label for="date_of_origination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date of Origination*</label>
                                <input type="date" id="date_of_origination" name="date_of_origination" required class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            </div>
                            
                            <!-- Comments -->
                            <div>
                                <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comments</label>
                                <textarea id="comments" name="comments" rows="2" class="w-full px-3 sm:px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Any additional notes"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- File Upload -->
                    <div class="mt-4 sm:mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File Upload*</label>
                        <div class="mt-1 flex justify-center px-4 sm:px-6 pt-4 sm:pt-5 pb-4 sm:pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 dark:text-gray-400">
                                    <label for="file" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 dark:hover:text-primary-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload a file</span>
                                        <input id="file" name="file" type="file" required class="sr-only">
                                    </label>
                                    <p class="sm:pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    PDF, DOCX, XLSX, PPTX up to 10MB
                                </p>
                                <div id="filePreview" class="hidden mt-4">
                                    <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                        <div class="flex items-center">
                                            <i class="fas fa-file text-gray-500 dark:text-gray-400 mr-2"></i>
                                            <span id="fileName" class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate"></span>
                                        </div>
                                        <button type="button" id="removeFile" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 ml-2 flex-shrink-0">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form submission feedback -->
                    <div id="uploadFeedback" class="hidden mt-4 p-4 rounded-md"></div>
                </form>
            </div>
            
            <!-- Modal footer -->
            <div class="modal-footer flex flex-col sm:flex-row flex-shrink-0 items-stretch sm:items-center justify-end p-4 border-t border-gray-200 dark:border-gray-700 rounded-b-md space-y-2 sm:space-y-0 sm:space-x-3">
                <button type="button" class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 order-2 sm:order-1" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" form="uploadForm" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center order-1 sm:order-2">
                    <span id="submitText">Upload File</span>
                    <span id="spinner" class="hidden ml-2">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function populateDropdown(selector, data, placeholder) {
        const select = $(selector);
        select.empty();
        select.append($('<option>', {
            value: '',
            text: placeholder
        }));
        data.forEach(function(item) {
            select.append($('<option>', {
                value: item,
                text: item
            }));
        });
    }
    $.getJSON('get_departments.php', function(data) {
        populateDropdown('#department', data, 'Select Department');
        populateDropdown('#destination', data, 'Select Destination');
    }).fail(function() {
        console.error('Failed to load departments');
        // Show error in both dropdowns
        $('#department').append($('<option>', {
            value: '',
            text: 'Error loading departments'
        }));
        $('#destination').append($('<option>', {
            value: '',
            text: 'Error loading destinations'
        }));
    });

    // Handle file selection preview
    $('#file').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#fileName').text(file.name);
            $('#filePreview').removeClass('hidden');
        }
    });

    $('#removeFile').on('click', function() {
        $('#file').val('');
        $('#filePreview').addClass('hidden');
    });

    const dropArea = $('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.on(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.on(eventName, function() {
            $(this).addClass('border-primary-500 bg-primary-50 dark:bg-primary-900 bg-opacity-50');
        });
    });

    // Unhighlight when drag leaves or drops
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.on(eventName, function() {
            $(this).removeClass('border-primary-500 bg-primary-50 dark:bg-primary-900 bg-opacity-50');
        });
    });

    // Handle dropped files
    dropArea.on('drop', function(e) {
        const dt = e.originalEvent.dataTransfer;
        const files = dt.files;
        
        if (files.length) {
            $('#file')[0].files = files;
            $('#fileName').text(files[0].name);
            $('#filePreview').removeClass('hidden');
        }
    });

    // Form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(this);
        const feedback = $('#uploadFeedback');
        const submitBtn = form.find('[type="submit"]');
        const submitText = $('#submitText');
        const spinner = $('#spinner');
        
        // Show loading state
        submitText.text('Uploading...');
        spinner.removeClass('hidden');
        submitBtn.prop('disabled', true);
        feedback.addClass('hidden').empty();
        
        $.ajax({
            url: 'upload_file.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.includes('successfully')) {
                    feedback.removeClass('hidden').addClass('bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300').html(response);
                    form[0].reset();
                    $('#filePreview').addClass('hidden');
                    
                    // Close modal after 2 seconds
                    setTimeout(function() {
                        $('#uploadModal').modal('hide');
                        location.reload(); 
                    }, 2000);
                } else {
                    feedback.removeClass('hidden').addClass('bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300').html(response);
                }
            },
            error: function(xhr, status, error) {
                feedback.removeClass('hidden').addClass('bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300').text('Error: ' + error);
            },
            complete: function() {
                submitText.text('Upload File');
                spinner.addClass('hidden');
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Reset modal when closed
    $('#uploadModal').on('hidden.bs.modal', function() {
        $('#uploadForm')[0].reset();
        $('#filePreview').addClass('hidden');
        $('#uploadFeedback').addClass('hidden').empty();
        
        // Reset feedback classes
        $('#uploadFeedback').removeClass('bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300');
    });

    $('#uploadModal').on('click', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
});
</script>
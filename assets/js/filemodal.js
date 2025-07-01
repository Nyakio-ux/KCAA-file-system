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
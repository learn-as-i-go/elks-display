        </div> <!-- End content -->
    </div> <!-- End container -->
    
    <script>
        // Common JavaScript functions for all admin pages
        
        // Image preview function
        function previewImage(input, previewId = 'image-preview') {
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" style="max-width: 200px; max-height: 250px; border-radius: 8px; border: 2px solid #dee2e6;">';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Confirm delete function
        function confirmDelete(itemName = 'this item') {
            return confirm('Are you sure you want to delete ' + itemName + '?');
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
    </script>
</body>
</html>

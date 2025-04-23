</div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate total hours when start and end time change
        function calculateTotalHours() {
            const startTime = document.getElementById('start_time').value;
            const endTime = document.getElementById('end_time').value;
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01T${startTime}`);
                const end = new Date(`2000-01-01T${endTime}`);
                
                if (end < start) {
                    // If end time is before start time, assume it's the next day
                    end.setDate(end.getDate() + 1);
                }
                
                const diffMs = end - start;
                const diffHrs = diffMs / (1000 * 60 * 60);
                
                document.getElementById('total_hours').value = diffHrs.toFixed(2);
            }
        }
        
        // Add event listeners if the elements exist
        document.addEventListener('DOMContentLoaded', function() {
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            if (startTimeInput && endTimeInput) {
                startTimeInput.addEventListener('change', calculateTotalHours);
                endTimeInput.addEventListener('change', calculateTotalHours);
            }
        });
    </script>
</body>
</html>
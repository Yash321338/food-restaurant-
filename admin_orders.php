<script>
    
// Function to update order count
function updateOrderCount() {
    $.ajax({
        url: 'get_order_count.php',
        method: 'GET',
        success: function(response) {
            $('#order-count').text(response);
        }
    });
}

// Call this function after any order modification
$(document).ready(function() {
    // Update count after form submissions
    $('form').on('submit', function() {
        setTimeout(updateOrderCount, 500); // Small delay to allow DB update
    });
    
    // Initial count update
    updateOrderCount();
});

</script>
/**
 * Main JavaScript File
 */

// Format currency to IDR
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

// Show confirmation dialog
function showConfirm(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Remove item from cart
function removeFromCart(itemIndex) {
    showConfirm('Hapus item ini dari keranjang?', function() {
        document.querySelector(`[data-item-index="${itemIndex}"]`).closest('tr').remove();
        updateCartTotal();
    });
}

// Update cart total
function updateCartTotal() {
    const rows = document.querySelectorAll('[data-subtotal]');
    let total = 0;
    rows.forEach(row => {
        total += parseInt(row.dataset.subtotal);
    });
    
    const totalElement = document.querySelector('[data-cart-total]');
    if (totalElement) {
        totalElement.textContent = formatCurrency(total);
    }
}

// Prevent form resubmission
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add any dynamic functionality here
});

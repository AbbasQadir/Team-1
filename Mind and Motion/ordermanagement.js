document.addEventListener('DOMContentLoaded', function() {
    const orders = [
        { orderId: 101, customerName: 'John Doe', orderDate: '09-12-2024', totalAmount: '£150.00', paymentMethod: 'Credit Card', shippingAddress: '123 Elm St, Springfield', status: 'Shipped' },
        { orderId: 102, customerName: 'Jane Smith', orderDate: '09-01-2025', totalAmount: '£200.00', paymentMethod: 'PayPal', shippingAddress: '456 Maple Ave, Anytown', status: 'Pending' }
    ];

    const container = document.querySelector('.order-container');

    orders.forEach(order => {
        const row = document.createElement('div');
        row.className = 'order-row';
        row.innerHTML = `
            <div class="order-cell">${order.orderId}</div>
            <div class="order-cell">${order.customerName}</div>
            <div class="order-cell">${order.orderDate}</div>
            <div class="order-cell">${order.totalAmount}</div>
            <div class="order-cell">${order.paymentMethod}</div>
            <div class="order-cell">${order.shippingAddress}</div>
            <div class="order-cell">
                <select onchange="updateStatus(this.value, ${order.orderId})">
                    <option value="Pending" ${order.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Shipped" ${order.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                    <option value="Delivered" ${order.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                    <option value="Cancelled" ${order.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                </select>
            </div>
            <div class="order-cell actions">
                <button class="button" onclick="updateOrder(${order.orderId})">Update</button>
                <button class="button" onclick="deleteOrder(${order.orderId})">Delete</button>
            </div>
        `;
        container.appendChild(row);
    });
});

function updateStatus(status, orderId) {
    console.log(`Order ID ${orderId} status updated to ${status}`);
    // Implement AJAX request to update status
}

function updateOrder(orderId) {
    console.log(`Updating order ID ${orderId}`);
    // Implement logic to handle order updates
}

function deleteOrder(orderId) {
    if (confirm("Are you sure you want to delete this order?")) {
        console.log(`Deleting order ID ${orderId}`);
        // Implement logic to handle order deletion
    }
}

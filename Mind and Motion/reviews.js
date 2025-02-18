document.addEventListener("DOMContentLoaded", function() {
    loadSavedReview();
    loadRecentReviews();

    // Character counter and enforce max length
    const reviewText = document.getElementById("desc-txtArea");
    const charCount = document.getElementById("charCount");

    reviewText.addEventListener("input", function () {
        if (this.value.length > 500) {
            this.value = this.value.substring(0, 500); // Truncate extra characters
        }
        charCount.innerText = `${this.value.length}/500`;
        localStorage.setItem("savedReview", this.value);
    });

    // Prevent multiple submissions
    document.getElementById("reviewForm").addEventListener("submit", function(event) {
        event.preventDefault();
        const submitButton = document.querySelector('.btn');
        submitButton.disabled = true;
        submitButton.innerText = "Submitting...";

        setTimeout(() => {
            alert("Review Submitted!");
            submitButton.disabled = false;
            submitButton.innerText = "Submit Review";
            localStorage.removeItem("savedReview");
        }, 2000);
    });
});
const productImages = {
    "Product 1": "placeholder1.jpg",
    "Product 2": "placeholder2.jpg",
    "Product 3": "placeholder3.jpg"
};

// Update product image
function updateProductImage() {
    const inputElement = document.getElementById('ProductId');
    const inputValue = inputElement.value.trim().toLowerCase();
    const imageElement = document.getElementById('reviewSelectedProduct-image');

    const matchedProduct = Object.keys(productImages).find(product =>
        product.toLowerCase().includes(inputValue) // Matches even partial input
    );

    if (matchedProduct) {
        imageElement.src = productImages[matchedProduct]; // Update image source
    } else {
        imageElement.src = "placeholder.jpg"; // Default image if no match found
    }
}

// Close alert
function closeAlert() {
    document.getElementById('alertBox').style.display = 'none';
}

// Enlarge image
function enlargeImage() {
    const image = document.getElementById("reviewSelectedProduct-image");
    window.open(image.src, "_blank");
}

// Load saved review
function loadSavedReview() {
    const savedReview = localStorage.getItem("savedReview");
    if (savedReview) {
        document.getElementById("desc-txtArea").value = savedReview;
        document.getElementById("charCount").innerText = `${savedReview.length}/500`;
    }
}

// Load recent reviews
function loadRecentReviews() {
    document.getElementById("reviewsList").innerHTML = "<li>No reviews yet. Be the first to leave one!</li>";
}
$(document).ready(function() {
    $('#ProductId').select2({
        placeholder: "Select or Type a Product",
        allowClear: true
    });
});

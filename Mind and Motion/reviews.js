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

// Update product image
function updateProductImage() {
    const productDropdown = document.getElementById('ProductId');
    const selectedOption = productDropdown.options[productDropdown.selectedIndex];
    const newImageUrl = selectedOption.getAttribute('data-image');
    const imageElement = document.getElementById('reviewSelectedProduct-image');

    if (imageElement.src !== newImageUrl) {
        imageElement.src = newImageUrl;
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
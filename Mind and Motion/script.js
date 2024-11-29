document.addEventListener("DOMContentLoaded", () => {
    const track = document.querySelector(".carousel-track");
    const dots = document.querySelectorAll(".dot");
    const leftArrow = document.querySelector(".carousel-arrow.left");
    const rightArrow = document.querySelector(".carousel-arrow.right");
    const items = document.querySelectorAll(".carousel-item");
    const totalSlides = items.length;
    let currentIndex = 0;
    let isTransitioning = false; // Prevent simultaneous transitions
    const transitionDuration = 500; // Match CSS transition time (ms)
    let autoSlideInterval;

    // Function to update the carousel display
    const updateCarousel = (manual = false) => {
        if (!manual) {
            track.style.transition = `transform ${transitionDuration}ms ease-in-out`;
        }
        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        // Update active dot
        dots.forEach((dot, i) => {
            dot.classList.toggle("active", i === currentIndex);
        });

        // Allow new interactions after the transition ends
        if (!manual) {
            isTransitioning = true;
            setTimeout(() => {
                isTransitioning = false;
            }, transitionDuration);
        }
    };

    // Function to move to the next slide
    const moveToNextSlide = () => {
        if (!isTransitioning) {
            currentIndex = (currentIndex + 1) % totalSlides;
            updateCarousel();
        }
    };

    // Function to move to the previous slide
    const moveToPreviousSlide = () => {
        if (!isTransitioning) {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : totalSlides - 1;
            updateCarousel();
        }
    };

    // Start auto-slide
    const startAutoSlide = () => {
        clearInterval(autoSlideInterval);
        autoSlideInterval = setInterval(() => {
            moveToNextSlide();
        }, 5000);
    };

    // Stop auto-slide
    const stopAutoSlide = () => {
        clearInterval(autoSlideInterval);
    };

    // Arrow navigation
    leftArrow.addEventListener("click", () => {
        stopAutoSlide();
        moveToPreviousSlide();
        startAutoSlide();
    });

    rightArrow.addEventListener("click", () => {
        stopAutoSlide();
        moveToNextSlide();
        startAutoSlide();
    });

    // Dot navigation
    dots.forEach((dot, i) => {
        dot.addEventListener("click", () => {
            if (isTransitioning || currentIndex === i) return; // Ignore if already on the clicked dot
            stopAutoSlide();
            currentIndex = i;
            updateCarousel(true);
            startAutoSlide();
        });
    });

    // Pause auto-slide on hover
    const carousel = document.querySelector(".carousel");
    carousel.addEventListener("mouseenter", stopAutoSlide);
    carousel.addEventListener("mouseleave", startAutoSlide);

    // Initialize carousel
    updateCarousel(true);
    startAutoSlide();
});

document.addEventListener("DOMContentLoaded", () => {
    const basketCount = document.querySelector(".basket span"); // Select the basket count element
    const buttons = document.querySelectorAll(".add-to-basket");
    let itemCount = 0;

    buttons.forEach((button) => {
        button.addEventListener("click", () => {
            itemCount += 1; // Increment item count
            basketCount.textContent = `(${itemCount})`; // Update basket count display
        });
    });
});

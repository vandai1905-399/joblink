/**
 * HERO SLIDER JAVASCRIPT - Chuyển động mượt mà cho 4 slide trang chủ JobLink
 * Hỗ trợ: Auto-play (5s), Pause on hover, Next/Prev buttons, Dots, Touch Swipe & Keyboard
 */
document.addEventListener('DOMContentLoaded', function () {
    const sliderContainer = document.querySelector('.hero-slider-section');
    if (!sliderContainer) return;

    const slides = sliderContainer.querySelectorAll('.hero-slide-item');
    const dots = sliderContainer.querySelectorAll('.hero-slider-dot');
    const prevBtn = sliderContainer.querySelector('.hero-slider-nav.prev');
    const nextBtn = sliderContainer.querySelector('.hero-slider-nav.next');
    const counter = sliderContainer.querySelector('.hero-slider-counter');

    if (!slides.length) return;

    let currentIndex = 0;
    const totalSlides = slides.length;
    const intervalDuration = 5000; // 5 giây tự động chuyển
    let slideTimer = null;
    let isPaused = false;

    // Cập nhật hiển thị slide theo index
    function showSlide(index) {
        if (index < 0) {
            currentIndex = totalSlides - 1;
        } else if (index >= totalSlides) {
            currentIndex = 0;
        } else {
            currentIndex = index;
        }

        // Cập nhật class active cho từng slide
        slides.forEach((slide, idx) => {
            if (idx === currentIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.remove('active');
            }
        });

        // Cập nhật dots
        dots.forEach((dot, idx) => {
            if (idx === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });

        // Cập nhật bộ đếm 01 / 04
        if (counter) {
            const currentNum = String(currentIndex + 1).padStart(2, '0');
            const totalNum = String(totalSlides).padStart(2, '0');
            counter.textContent = `${currentNum} / ${totalNum}`;
        }
    }

    function nextSlide() {
        showSlide(currentIndex + 1);
    }

    function prevSlide() {
        showSlide(currentIndex - 1);
    }

    // Bắt đầu timer auto-play
    function startAutoPlay() {
        stopAutoPlay();
        slideTimer = setInterval(() => {
            if (!isPaused) {
                nextSlide();
            }
        }, intervalDuration);
    }

    // Dừng timer
    function stopAutoPlay() {
        if (slideTimer) {
            clearInterval(slideTimer);
            slideTimer = null;
        }
    }

    // Sự kiện nút Next / Prev
    if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            startAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            startAutoPlay();
        });
    }

    // Sự kiện bấm vào các chấm dot
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', (e) => {
            e.preventDefault();
            showSlide(idx);
            startAutoPlay();
        });
    });

    // Pause on Hover (Rê chuột vào thì tạm dừng, bỏ ra thì tiếp tục)
    sliderContainer.addEventListener('mouseenter', () => {
        isPaused = true;
    });

    sliderContainer.addEventListener('mouseleave', () => {
        isPaused = false;
    });

    // Phím mũi tên Bàn phím (Left / Right arrow)
    document.addEventListener('keydown', (e) => {
        // Chỉ nhận khi không đang gõ trong input/textarea
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return;
        }
        if (e.key === 'ArrowRight') {
            nextSlide();
            startAutoPlay();
        } else if (e.key === 'ArrowLeft') {
            prevSlide();
            startAutoPlay();
        }
    });

    // Hỗ trợ Vuốt chạm trên Điện thoại (Touch Swipe)
    let touchStartX = 0;
    let touchEndX = 0;

    sliderContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    sliderContainer.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const threshold = 40; // Khoảng cách quẹt tối thiểu (px)
        if (touchEndX < touchStartX - threshold) {
            // Quẹt sang trái -> Next
            nextSlide();
            startAutoPlay();
        } else if (touchEndX > touchStartX + threshold) {
            // Quẹt sang phải -> Prev
            prevSlide();
            startAutoPlay();
        }
    }

    // Khởi tạo ban đầu
    showSlide(0);
    startAutoPlay();
});

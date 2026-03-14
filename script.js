// Scroll Animation Observer
const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
}, observerOptions);
document.querySelectorAll('section:not(.hero)').forEach(section => observer.observe(section));

// Simple animated counter
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.counter');
    
    counters.forEach(counter => {
        const target = parseInt(counter.parentElement.getAttribute('data-target'));
        let count = 0;
        const duration = 900; // 2 seconds
        const increment = target / (duration / 10);
        
        const updateCount = () => {
            count += increment;
            if (count < target) {
                counter.innerText = Math.ceil(count);
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target;
            }
        };
        
        // Start animation after 500ms delay
        setTimeout(updateCount, 500);
    });
});

// SIMPLIFIED MOBILE MENU - WORKING VERSION
document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('navMenu');
    const overlay = document.getElementById('overlay');
    
    if (!burger || !navMenu) {
        console.error('Menu elements not found!');
        return;
    }
    
    function toggleMenu() {
        burger.classList.toggle('active');
        navMenu.classList.toggle('active');
        if (overlay) overlay.classList.toggle('active');
        
        // Lock body scroll
        document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
    }
    
    // Click events
    burger.addEventListener('click', toggleMenu);
    if (overlay) overlay.addEventListener('click', toggleMenu);
    
    // Close when clicking nav links
    navMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            burger.classList.remove('active');
            navMenu.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navMenu.classList.contains('active')) {
            toggleMenu();
        }
    });
});

const carousel = document.getElementById('skillsCarousel');
const cards = document.querySelectorAll('.skill-card');
const navContainer = document.getElementById('carouselNav');

let currentRotation = 0;
const cardCount = cards.length;
const anglePerCard = 360 / cardCount;
let currentIndex = 0;
let isDragging = false;
let startX = 0;
let currentX = 0;

// Create navigation dots
cards.forEach((_, index) => {
    const dot = document.createElement('div');
    dot.className = 'nav-dot' + (index === 0 ? ' active' : '');
    dot.addEventListener('click', () => rotateTo(index));
    navContainer.appendChild(dot);
});

const dots = document.querySelectorAll('.nav-dot');

function updateCarousel() {
    carousel.style.transform = `rotateY(${currentRotation}deg)`;
    
    // Update active states
    cards.forEach((card, index) => {
        // Calculate which card is at front (0 degrees)
        let cardAngle = (index * anglePerCard - currentRotation) % 360;
        if (cardAngle < 0) cardAngle += 360;
        
        // Normalize to -180 to 180
        if (cardAngle > 180) cardAngle -= 360;
        
        const isFront = Math.abs(cardAngle) < anglePerCard / 2;
        card.classList.toggle('active', isFront);
        
        // Adjust z-index based on position
        const zIndex = Math.round(100 - Math.abs(cardAngle));
        card.style.zIndex = zIndex;
    });

    // Update dots
    const activeIndex = Math.round((-currentRotation / anglePerCard) % cardCount);
    const normalizedIndex = ((activeIndex % cardCount) + cardCount) % cardCount;
    
    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === normalizedIndex);
    });
}

function rotateTo(index) {
    currentRotation = -index * anglePerCard;
    updateCarousel();
}

function nextCard() {
    currentRotation -= anglePerCard;
    updateCarousel();
}

function prevCard() {
    currentRotation += anglePerCard;
    updateCarousel();
}

// Click on card to move it to back (rotate carousel)
cards.forEach((card, index) => {
    card.addEventListener('click', (e) => {
        e.stopPropagation();
        
        // Calculate current position of this card
        let cardAngle = (index * anglePerCard - currentRotation) % 360;
        if (cardAngle < 0) cardAngle += 360;
        
        // If card is at front (0 degrees), move to back (180 degrees behind)
        if (cardAngle < anglePerCard / 2 || cardAngle > 360 - anglePerCard / 2) {
            // Move to back (rotate 180 degrees)
            currentRotation -= 180;
        } else {
            // Otherwise bring to front
            rotateTo(index);
        }
        updateCarousel();
    });
});

// Drag to rotate
const container = document.querySelector('.carousel-container');

container.addEventListener('mousedown', (e) => {
    isDragging = true;
    startX = e.clientX;
    container.style.cursor = 'grabbing';
});

document.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    currentX = e.clientX;
    const diff = currentX - startX;
    const rotation = diff * 0.5;
    carousel.style.transform = `rotateY(${currentRotation + rotation}deg)`;
});

document.addEventListener('mouseup', (e) => {
    if (!isDragging) return;
    isDragging = false;
    container.style.cursor = 'pointer';
    const diff = e.clientX - startX;
    currentRotation += diff * 0.5;
    
    // Snap to nearest card
    const snapAngle = Math.round(currentRotation / anglePerCard) * anglePerCard;
    currentRotation = snapAngle;
    updateCarousel();
});

// Touch support
container.addEventListener('touchstart', (e) => {
    isDragging = true;
    startX = e.touches[0].clientX;
});

document.addEventListener('touchmove', (e) => {
    if (!isDragging) return;
    currentX = e.touches[0].clientX;
    const diff = currentX - startX;
    const rotation = diff * 0.5;
    carousel.style.transform = `rotateY(${currentRotation + rotation}deg)`;
});

document.addEventListener('touchend', (e) => {
    if (!isDragging) return;
    isDragging = false;
    const diff = currentX - startX;
    currentRotation += diff * 0.5;
    
    const snapAngle = Math.round(currentRotation / anglePerCard) * anglePerCard;
    currentRotation = snapAngle;
    updateCarousel();
});

// Auto-rotate slowly when idle
let autoRotateInterval;
function startAutoRotate() {
    autoRotateInterval = setInterval(() => {
        if (!isDragging) {
            currentRotation -= 0.2;
            updateCarousel();
        }
    }, 50);
}

function stopAutoRotate() {
    clearInterval(autoRotateInterval);
}

// Start auto-rotate after 3 seconds of inactivity
let inactivityTimer;
function resetInactivityTimer() {
    stopAutoRotate();
    clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(startAutoRotate, 3000);
}

document.addEventListener('mousemove', resetInactivityTimer);
document.addEventListener('click', resetInactivityTimer);
document.addEventListener('touchstart', resetInactivityTimer);

// Initialize
updateCarousel();
resetInactivityTimer();

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        prevCard();
        resetInactivityTimer();
    } else if (e.key === 'ArrowRight') {
        nextCard();
        resetInactivityTimer();
    }
});
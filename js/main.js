document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // Add glowing effect to elements on hover
    const glassPanels = document.querySelectorAll('.glass-panel');
    glassPanels.forEach(panel => {
        panel.addEventListener('mouseenter', () => {
            panel.style.boxShadow = '0 0 20px rgba(0, 210, 255, 0.4)';
        });
        panel.addEventListener('mouseleave', () => {
            panel.style.boxShadow = '0 8px 32px 0 rgba(0, 0, 0, 0.5)';
        });
    });
});

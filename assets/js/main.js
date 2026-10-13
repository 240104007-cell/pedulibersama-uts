// PeduliBersama - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // Flash auto-dismiss
    const flash = document.querySelector('.flash-message');
    if (flash) {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s, transform 0.5s';
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-20px)';
            setTimeout(() => flash.remove(), 500);
        }, 5000);
    }

    // Nominal buttons
    document.querySelectorAll('.nominal-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.nominal-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const input = document.getElementById('jumlah');
            if (input) input.value = this.dataset.nominal;
        });
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) e.preventDefault();
        });
    });

    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeIn');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.card-kampanye, .stat-item, .category-card').forEach(el => {
        observer.observe(el);
    });
});

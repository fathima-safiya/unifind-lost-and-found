document.addEventListener('DOMContentLoaded', () => {

    // --- Scroll Reveal ---
    const revealElements = document.querySelectorAll('.reveal-element');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion && revealElements.length > 0) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                    if (entry.target.classList.contains('stat-counter')) {
                        animateCounter(entry.target);
                    }
                }
            });
        }, { threshold: 0.15, rootMargin: "0px 0px -50px 0px" });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => {
            el.classList.add('is-revealed');
            if (el.classList.contains('stat-counter')) {
                animateCounter(el);
            }
        });
    }

    // --- Animated Number Counter ---
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'), 10);
        if (isNaN(target)) return;

        const duration = 1500;
        const totalFrames = Math.round(duration / (1000 / 60));
        let frame = 0;

        const timer = setInterval(() => {
            frame++;
            const progress = frame / totalFrames;
            const current = Math.round(target * (1 - (1 - progress) * (1 - progress)));
            element.textContent = current.toLocaleString();
            if (frame === totalFrames) {
                clearInterval(timer);
                element.textContent = target.toLocaleString();
            }
        }, 1000 / 60);
    }

    // --- Navbar Scroll Effect ---
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 10);
        });
    }

    // --- Delete Confirmation Modal ---
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    const globalModal = document.getElementById('confirm-modal');

    if (globalModal && confirmForms.length > 0) {
        const modalMessage = globalModal.querySelector('.modal-body p');
        const confirmBtn = globalModal.querySelector('.btn-confirm');
        const cancelBtn = globalModal.querySelector('.btn-cancel');
        let currentForm = null;

        confirmForms.forEach(form => {
            form.removeAttribute('onsubmit');
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                currentForm = form;
                const message = form.getAttribute('data-confirm');
                if (modalMessage && message) {
                    modalMessage.textContent = message;
                }
                globalModal.classList.add('active');
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                globalModal.classList.remove('active');
                currentForm = null;
            });
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (currentForm) currentForm.submit();
            });
        }

        globalModal.addEventListener('click', (e) => {
            if (e.target === globalModal) {
                globalModal.classList.remove('active');
                currentForm = null;
            }
        });
    }
});

// Show a toast notification (success or error)
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✅' : '⚠️';
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

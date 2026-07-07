/* ================================================================
   CityMarket — Visual Effects Engine
   Scroll reveal, 3D tilt, particles, confetti, magnetic buttons,
   scroll progress bar, cursor glow, AR viewer
   ================================================================ */

(function () {
    'use strict';

    /* ---------- Scroll Progress Bar ---------- */
    function initScrollProgress() {
        const bar = document.getElementById('scroll-progress');
        if (!bar) return;
        function update() {
            const h = document.documentElement;
            const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight);
            bar.style.width = Math.min(scrolled * 100, 100) + '%';
        }
        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    /* ---------- Cursor Glow ---------- */
    function initCursorGlow() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.innerWidth < 768) return; // skip on mobile
        const glow = document.getElementById('cursor-glow');
        if (!glow) return;
        let raf;
        let tx = 0, ty = 0, cx = 0, cy = 0;
        document.addEventListener('mousemove', function (e) {
            tx = e.clientX; ty = e.clientY;
        });
        function animate() {
            cx += (tx - cx) * 0.1;
            cy += (ty - cy) * 0.1;
            glow.style.transform = 'translate(' + cx + 'px,' + cy + 'px) translate(-50%, -50%)';
            raf = requestAnimationFrame(animate);
        }
        animate();
    }

    /* ---------- Glassmorphism Header on scroll ---------- */
    function initGlassHeader() {
        const header = document.getElementById('main-header');
        if (!header) return;
        function onScroll() {
            if (window.scrollY > 30) header.classList.add('glass-header');
            else header.classList.remove('glass-header');
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- Scroll Reveal (IntersectionObserver) ---------- */
    function initScrollReveal() {
        const els = document.querySelectorAll('[data-reveal]');
        if (!els.length) return;

        if (!('IntersectionObserver' in window)) {
            els.forEach(el => el.classList.add('revealed'));
            return;
        }
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const delay = entry.target.getAttribute('data-reveal-delay') || 0;
                    setTimeout(() => entry.target.classList.add('revealed'), delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        els.forEach(el => observer.observe(el));
    }

    /* ---------- 3D Tilt Cards ---------- */
    function init3DTilt() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const cards = document.querySelectorAll('.card-3d-tilt');
        if (!cards.length) return;

        cards.forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const cx = rect.width / 2;
                const cy = rect.height / 2;
                const rotateX = ((y - cy) / cy) * -8;
                const rotateY = ((x - cx) / cx) * 8;
                card.style.transform = 'perspective(800px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale(1.03)';
                card.classList.add('tilting');
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
                card.classList.remove('tilting');
            });
        });
    }

    /* ---------- Particle Background (canvas) ---------- */
    function initParticles() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const canvas = document.getElementById('particle-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let particles = [];
        let animId;

        function resize() {
            const parent = canvas.parentElement;
            canvas.width = parent.offsetWidth;
            canvas.height = parent.offsetHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        const colors = ['#dc2626', '#7c3aed', '#0891b2', '#f59e0b'];

        function spawn() {
            const count = Math.min(40, Math.floor(canvas.width / 30));
            particles = [];
            for (let i = 0; i < count; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    vx: (Math.random() - 0.5) * 0.5,
                    vy: (Math.random() - 0.5) * 0.5,
                    r: Math.random() * 3 + 1,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    alpha: Math.random() * 0.4 + 0.1,
                });
            }
        }
        spawn();

        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(function (p) {
                p.x += p.vx;
                p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.globalAlpha = p.alpha;
                ctx.fill();
            });
            // draw connections
            ctx.globalAlpha = 1;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 120) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.strokeStyle = particles[i].color;
                        ctx.globalAlpha = (1 - dist / 120) * 0.15;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
            ctx.globalAlpha = 1;
            animId = requestAnimationFrame(draw);
        }
        draw();
    }

    /* ---------- Confetti ---------- */
    function fireConfetti(originX, originY) {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const colors = ['#dc2626', '#7c3aed', '#0891b2', '#f59e0b', '#10b981', '#ef4444'];
        const count = 40;
        for (let i = 0; i < count; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.left = originX + 'px';
            piece.style.top = originY + 'px';
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            document.body.appendChild(piece);

            const angle = (Math.PI * 2 * i) / count + (Math.random() - 0.5) * 0.5;
            const velocity = Math.random() * 150 + 100;
            const vx = Math.cos(angle) * velocity;
            const vy = Math.sin(angle) * velocity - 100;
            const rotation = Math.random() * 720 - 360;

            piece.animate([
                { transform: 'translate(0,0) rotate(0deg)', opacity: 1 },
                { transform: 'translate(' + vx + 'px,' + vy + 'px) rotate(' + rotation + 'deg)', opacity: 0 }
            ], { duration: 1200 + Math.random() * 600, easing: 'cubic-bezier(0.2, 0.6, 0.4, 1)' });

            setTimeout(() => piece.remove(), 2000);
        }
    }

    /* ---------- Magnetic Buttons ---------- */
    function initMagnetic() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.innerWidth < 768) return;
        const btns = document.querySelectorAll('.magnetic-btn');
        btns.forEach(function (btn) {
            btn.addEventListener('mousemove', function (e) {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                btn.style.transform = 'translate(' + (x * 0.2) + 'px,' + (y * 0.2) + 'px)';
            });
            btn.addEventListener('mouseleave', function () {
                btn.style.transform = '';
            });
        });
    }

    /* ---------- Ripple effect ---------- */
    function initRipple() {
        document.addEventListener('click', function (e) {
            const target = e.target.closest('.ripple');
            if (!target) return;
            const rect = target.getBoundingClientRect();
            const ripple = document.createElement('span');
            ripple.className = 'ripple-circle';
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            target.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    }

    /* ---------- AR Product Viewer ---------- */
    function initARViewer() {
        // Open
        document.querySelectorAll('[data-ar-open]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const modal = document.getElementById('ar-modal');
                if (!modal) return;
                modal.classList.add('open');
                document.body.style.overflow = 'hidden';
                initARDrag();
            });
        });
        // Close
        document.querySelectorAll('[data-ar-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const modal = document.getElementById('ar-modal');
                if (modal) modal.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
        // Close on backdrop click
        const modal = document.getElementById('ar-modal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.remove('open');
                    document.body.style.overflow = '';
                }
            });
        }
    }

    let arAngle = 0, arDragging = false, arStartX = 0, arStartAngle = 0;
    function initARDrag() {
        const card = document.getElementById('ar-product-card');
        if (!card) return;
        card.classList.remove('ar-auto-rotate');

        card.addEventListener('mousedown', arDragStart);
        card.addEventListener('touchstart', arDragStart, { passive: true });
        document.addEventListener('mousemove', arDragMove);
        document.addEventListener('touchmove', arDragMove, { passive: true });
        document.addEventListener('mouseup', arDragEnd);
        document.addEventListener('touchend', arDragEnd);
    }
    function arDragStart(e) {
        arDragging = true;
        arStartX = e.touches ? e.touches[0].clientX : e.clientX;
        arStartAngle = arAngle;
        const card = document.getElementById('ar-product-card');
        if (card) card.classList.remove('ar-auto-rotate');
    }
    function arDragMove(e) {
        if (!arDragging) return;
        const x = e.touches ? e.touches[0].clientX : e.clientX;
        arAngle = arStartAngle + (x - arStartX) * 0.5;
        const card = document.getElementById('ar-product-card');
        if (card) card.style.transform = 'rotateY(' + arAngle + 'deg)';
    }
    function arDragEnd() { arDragging = false; }

    /* ---------- Parallax ---------- */
    function initParallax() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.innerWidth < 768) return;
        const layers = document.querySelectorAll('[data-parallax]');
        if (!layers.length) return;
        window.addEventListener('scroll', function () {
            const scrolled = window.scrollY;
            layers.forEach(function (layer) {
                const speed = parseFloat(layer.getAttribute('data-parallax')) || 0.3;
                layer.style.transform = 'translateY(' + (scrolled * speed) + 'px)';
            });
        }, { passive: true });
    }

    /* ---------- Init everything on DOM ready ---------- */
    function initAll() {
        initScrollProgress();
        initCursorGlow();
        initGlassHeader();
        initScrollReveal();
        init3DTilt();
        initParticles();
        initMagnetic();
        initRipple();
        initARViewer();
        initParallax();
    }

    // Expose confetti globally so toast / add-to-cart can trigger it
    window.CityMarketFX = { fireConfetti: fireConfetti };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Re-init for dynamically loaded content
    window.addEventListener('load', function () {
        initScrollReveal();
        init3DTilt();
        initMagnetic();
    });
})();

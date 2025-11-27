document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('menu-toggle');
    const appContainer = document.querySelector('.app-container');

    if (menuButton && appContainer) {
        menuButton.addEventListener('click', function() {
            appContainer.classList.toggle('sidebar-collapsed');
        });
    }

    // Bottom navigation: stricter active item matching and ripple effect
    const bottomNavItems = document.querySelectorAll('.bottom-nav-item');
    if (bottomNavItems && bottomNavItems.length) {
        const path = window.location.pathname.split('/').pop();
        // map filenames to targets for exact matching
        const map = {
            'dashboard.php': 'dashboard',
            'index.php': 'websites',
            'add_website.php': 'add',
            'settings.php': 'settings',
            'logout.php': 'logout'
        };

        bottomNavItems.forEach(item => {
            const target = item.getAttribute('data-target');
            if (path && map[path] && map[path] === target) {
                item.classList.add('active');
            }

            // Ripple on click/touch
            const createRipple = (ev) => {
                const rect = item.getBoundingClientRect();
                const ripple = document.createElement('span');
                ripple.className = 'ripple';
                const size = Math.max(rect.width, rect.height);
                ripple.style.width = ripple.style.height = size + 'px';
                const x = (ev.touches ? ev.touches[0].clientX : ev.clientX) - rect.left - size/2;
                const y = (ev.touches ? ev.touches[0].clientY : ev.clientY) - rect.top - size/2;
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                item.appendChild(ripple);
                setTimeout(() => { ripple.remove(); }, 600);
            };

            item.addEventListener('click', createRipple);
            item.addEventListener('touchstart', createRipple);
        });
    }

    // landing: mark feature cards visible when scrolled
    const features = document.querySelectorAll('.feature-card');
    const onScroll = () => {
        const h = window.innerHeight;
        features.forEach(f=>{
            const rect = f.getBoundingClientRect();
            if (rect.top < h - 60) f.classList.add('visible');
        });
    };
    window.addEventListener('scroll', onScroll);
    onScroll();

    // Disable heavy tilt on touch devices; enable subtle tilt on pointer devices
    (function(){
        const comp = document.getElementById('computer-3d');
        const isTouch = ('ontouchstart' in window) || navigator.maxTouchPoints > 0;
        if (!comp) return;
        if (isTouch) {
            // subtle idle transform for visual depth on touch devices
            comp.style.transform = 'translateZ(0) rotateX(0deg) rotateY(0deg)';
            comp.classList.add('no-tilt');
        } else {
            comp.addEventListener('mousemove', (e) => {
                const r = comp.getBoundingClientRect();
                const cx = r.left + r.width/2;
                const cy = r.top + r.height/2;
                const dx = (e.clientX - cx) / (r.width/2);
                const dy = (e.clientY - cy) / (r.height/2);
                comp.style.transform = `rotateX(${(-dy*8).toFixed(2)}deg) rotateY(${(dx*12).toFixed(2)}deg) translateZ(0)`;
            });
            comp.addEventListener('mouseleave', () => {
                comp.style.transform = '';
            });
        }
    })();

    // Ensure counters render quickly on mobile and are readable
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(el=>{
        const target = parseInt(el.getAttribute('data-count')||0,10);
        if (target <= 0) { el.textContent = '0'; return; }
        // shorter animation on low-power devices
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReducedMotion) {
            el.textContent = target;
            return;
        }
        let current = 0;
        const duration = Math.min(900, Math.max(300, target * 3));
        const stepTime = 15;
        const step = Math.max(1, Math.floor(target / Math.ceil(duration / stepTime)));
        const int = setInterval(()=>{
          current += step;
          if (current >= target) { current = target; clearInterval(int); }
          el.textContent = current;
        }, stepTime);
    });

});

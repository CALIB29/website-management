// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('menu-toggle');
    const appContainer = document.querySelector('.app-container');
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');

    // Logic to toggle sidebar with menu button
    if (menuButton && appContainer) {
        menuButton.addEventListener('click', () => {
            appContainer.classList.toggle('sidebar-collapsed');
        });
    }

    // Logic to close sidebar on link click in mobile view
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                appContainer.classList.add('sidebar-collapsed');
            }
        });
    });
});

// Theme toggle functionality
const themeSwitch = document.getElementById('theme-switch');
const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

// Check for saved user preference, if any, on load of the website
const currentTheme = localStorage.getItem('theme') || 'light';

// Set the theme based on the saved preference or system preference
if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
    document.documentElement.setAttribute('data-theme', 'dark');
    if (themeSwitch) themeSwitch.checked = true;
} else {
    document.documentElement.setAttribute('data-theme', 'light');
    if (themeSwitch) themeSwitch.checked = false;
}

// Listen for toggle changes
if (themeSwitch) {
    themeSwitch.addEventListener('change', function() {
        if (this.checked) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });
}

// Listen for system theme changes
prefersDarkScheme.addEventListener('change', e => {
    if (!localStorage.getItem('theme')) { // Only if user hasn't set a preference
        if (e.matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
            if (themeSwitch) themeSwitch.checked = true;
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            if (themeSwitch) themeSwitch.checked = false;
        }
    }
});

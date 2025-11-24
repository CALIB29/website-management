document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('menu-toggle');
    const appContainer = document.querySelector('.app-container');

    if (menuButton && appContainer) {
        menuButton.addEventListener('click', function() {
            appContainer.classList.toggle('sidebar-collapsed');
        });
    }
});

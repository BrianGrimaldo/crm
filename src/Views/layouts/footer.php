        </div> <!-- End content-area -->
    </main> <!-- End main-content -->

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            const themeIcon = themeToggleBtn.querySelector('i');
            
            if (document.documentElement.getAttribute('data-theme') === 'dark') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }

            themeToggleBtn.addEventListener('click', () => {
                let theme = document.documentElement.getAttribute('data-theme');
                if (theme === 'dark') {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                    themeIcon.classList.replace('fa-sun', 'fa-moon');
                } else {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeIcon.classList.replace('fa-moon', 'fa-sun');
                }
            });
        }
    </script>
</body>
</html>

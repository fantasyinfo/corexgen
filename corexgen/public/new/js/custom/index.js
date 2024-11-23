
document.addEventListener('DOMContentLoaded', function() {
    // Ensure sidebar is open on desktop initially
    if (window.innerWidth > 768) {
        document.body.classList.remove('sidebar-collapsed');
    }

    // Toggle sidebar
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.body.classList.toggle('sidebar-collapsed');
    });

    // Close sidebar when clicking overlay on mobile
    document.querySelector('.sidebar-overlay').addEventListener('click', function() {
        document.body.classList.add('sidebar-collapsed');
    });

    // Prevent auto-close when clicking on submenu links on mobile
    document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.stopPropagation();
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.body.classList.remove('sidebar-collapsed');
        } else {
            document.body.classList.add('sidebar-collapsed');
        }
    });

    // DataTable initialization
    $('#userTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true,
        "responsive": true
    });

    // Theme toggle functionality
    const themeToggleBtn = document.getElementById('themeToggle');
    const themeIcon = themeToggleBtn.querySelector('i');

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        themeIcon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    }

    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    setTheme(savedTheme);

    themeToggleBtn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });
});


$(function () {
    // enable tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // enable the datatables
    $('#dbTable').DataTable({
        "paging": true,
        "searching": true,
        "info": true,
        "responsive": true
    });

  })


      // Function to toggle the filter section
      function openFilters() {
        const filterSection = document.getElementById('filter-section');
        if (filterSection.style.display === 'block') {
            filterSection.style.display = 'none';
            localStorage.setItem('filterVisible', 'false'); // Save state
        } else {
            filterSection.style.display = 'block';
            localStorage.setItem('filterVisible', 'true'); // Save state
        }
    }

     
  // Function to check filter state on page load
      document.addEventListener('DOMContentLoaded', function () {
        const filterSection = document.getElementById('filter-section');
        const filterVisible = localStorage.getItem('filterVisible');
        if (filterVisible === 'true') {
            filterSection.style.display = 'block';
        } else {
            filterSection.style.display = 'none';
        }


    });

    document.getElementById('clearFilter').addEventListener('click', function() {
        // Get the base URL without query parameters
        const baseUrl = window.location.href.split('?')[0];
        
        // Redirect to the base URL
        window.location.href = baseUrl;
    });
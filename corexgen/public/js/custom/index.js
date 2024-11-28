document.addEventListener("DOMContentLoaded", function () {
    // dselect init

    if ($(".searchSelectBox").length > 0) {
        $(".searchSelectBox").each(function () {
            // `this` refers to the current DOM element in the loop
            dselect(this, {
                search: true, // Enable search functionality
            });
        });
    }

    // Ensure sidebar is open on desktop initially
    if (window.innerWidth > 768) {
        document.body.classList.remove("sidebar-collapsed");
    }

    // Toggle sidebar
    document
        .getElementById("sidebarToggle")
        .addEventListener("click", function () {
            document.body.classList.toggle("sidebar-collapsed");
        });

    // Close sidebar when clicking overlay on mobile
    document
        .querySelector(".sidebar-overlay")
        .addEventListener("click", function () {
            document.body.classList.add("sidebar-collapsed");
        });

    // Prevent auto-close when clicking on submenu links on mobile
    document
        .querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]')
        .forEach(function (link) {
            link.addEventListener("click", function (e) {
                if (window.innerWidth <= 768) {
                    e.stopPropagation();
                }
            });
        });

    // Handle window resize
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            document.body.classList.remove("sidebar-collapsed");
        } else {
            document.body.classList.add("sidebar-collapsed");
        }
    });

    // Theme toggle functionality
    const themeToggleBtn = document.getElementById("themeToggle");
    const themeIcon = themeToggleBtn.querySelector("i");

    function setTheme(theme) {
        document.documentElement.setAttribute("data-bs-theme", theme);
        localStorage.setItem("theme", theme);
        themeIcon.className = theme === "dark" ? "fas fa-moon" : "fas fa-sun";
    }

    const savedTheme =
        localStorage.getItem("theme") ||
        (window.matchMedia("(prefers-color-scheme: dark)").matches
            ? "dark"
            : "light");
    setTheme(savedTheme);

    themeToggleBtn.addEventListener("click", () => {
        const currentTheme =
            document.documentElement.getAttribute("data-bs-theme");
        setTheme(currentTheme === "dark" ? "light" : "dark");
    });
});

$(function () {
    // enable tooltip
    $('[data-toggle="tooltip"]').tooltip();
});

// Function to toggle the filter section

// Function to check filter state on page load
document.addEventListener("DOMContentLoaded", function () {
    const filterToggle = document.getElementById("filterToggle");
    const filterSidebar = document.getElementById("filterSidebar");
    const closeFilter = document.getElementById('closeFilter');

    if (filterToggle) {
        // Toggle filter sidebar
        filterToggle.addEventListener("click", function () {
            filterSidebar.classList.toggle("show");
        });

        // Close filter sidebar
        closeFilter.addEventListener("click", function () {
            filterSidebar.classList.remove("show");
        });
    }
});

// Get the drop zone element
const dropZone = document.querySelector(".drop-zone");
const fileInput = document.querySelector("#csvFile");

if (dropZone) {
    // Click handler (you already have this)
    dropZone.addEventListener("click", function () {
        fileInput.click();
    });

    // File input change handler (you already have this)
    fileInput.addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
            this.nextElementSibling.textContent = file.name;
        }
    });

    // Add drag and drop event listeners
    dropZone.addEventListener("dragover", function (e) {
        e.preventDefault();
        dropZone.style.backgroundColor = "#f8f9fa";
        dropZone.style.borderColor = "#0d6efd";
    });

    dropZone.addEventListener("dragleave", function (e) {
        e.preventDefault();
        dropZone.style.backgroundColor = "";
        dropZone.style.borderColor = "#ddd";
    });

    dropZone.addEventListener("drop", function (e) {
        e.preventDefault();
        dropZone.style.backgroundColor = "";
        dropZone.style.borderColor = "#ddd";

        const files = e.dataTransfer.files;
        if (files.length) {
            fileInput.files = files;
            // Trigger the change event manually
            const event = new Event("change", {
                bubbles: true,
            });
            fileInput.dispatchEvent(event);

            // Update the text
            fileInput.nextElementSibling.textContent = files[0].name;
        }
    });
}

$("#deleteModal").on("show.bs.modal", function (event) {
    console.log("first");
    var button = $(event.relatedTarget); // The button that triggered the modal
    var roleId = button.data("id"); // Get the role ID
    var route = button.data("route"); // Get the delete route

    // Set the form action to the appropriate route
    var form = $("#deleteForm");
    form.attr("action", route);
});

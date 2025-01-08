document.addEventListener("DOMContentLoaded", function () {
    // Select2 initialization
    if (typeof $ !== 'undefined' && $(".searchSelectBox").length > 0) {
        $(".searchSelectBox").select2({
            placeholder: "Please select an option",
            minimumResultsForSearch: 5,
        });
    }

    // Date input initialization
    const dateInputs = document.querySelectorAll('input[type="date"]');
    if (dateInputs.length > 0 && typeof flatpickr !== 'undefined') {
        dateInputs.forEach((input) => {
            flatpickr(input, {
                // enableTime: true,
                altInput: true,
                defaultDate: input.value,
            });
        });
    }

    // Sidebar functionality
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebarOverlay = document.querySelector(".sidebar-overlay");
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]');

    if (sidebarToggle && window.innerWidth > 768) {
        document.body.classList.remove("sidebar-collapsed");
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener("click", function () {
            document.body.classList.toggle("sidebar-collapsed");
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", function () {
            document.body.classList.add("sidebar-collapsed");
        });
    }

    if (sidebarLinks.length > 0) {
        sidebarLinks.forEach(function (link) {
            link.addEventListener("click", function (e) {
                if (window.innerWidth <= 768) {
                    e.stopPropagation();
                }
            });
        });
    }

    // Window resize handler
    const handleResize = function () {
        if (document.querySelector(".sidebar")) {  // Only run if sidebar exists
            if (window.innerWidth > 768) {
                document.body.classList.remove("sidebar-collapsed");
            } else {
                document.body.classList.add("sidebar-collapsed");
            }
        }
    };
    window.addEventListener("resize", handleResize);

    // Theme toggle functionality
    const themeToggleBtn = document.getElementById("themeToggle");
    if (themeToggleBtn) {
        const themeIcon = themeToggleBtn.querySelector("i");
        if (themeIcon) {
            const setTheme = function (theme) {
                document.documentElement.setAttribute("data-bs-theme", theme);
                localStorage.setItem("theme", theme);
                themeIcon.className = theme === "dark" ? "fas fa-moon" : "fas fa-sun";
            };

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
        }
    }
});

// Tooltip initialization
if (typeof $ !== 'undefined') {
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
}

// Filter sidebar functionality
document.addEventListener("DOMContentLoaded", function () {
    const filterToggle = document.getElementById("filterToggle");
    const filterSidebar = document.getElementById("filterSidebar");
    const closeFilter = document.getElementById("closeFilter");

    if (filterToggle && filterSidebar) {
        filterToggle.addEventListener("click", function () {
            filterSidebar.classList.toggle("show");
        });

        if (closeFilter) {
            closeFilter.addEventListener("click", function () {
                filterSidebar.classList.remove("show");
            });
        }
    }
});

// Drop zone functionality
const dropZone = document.querySelector(".drop-zone");
const fileInput = dropZone ? document.querySelector("#csvFile") : null;

if (dropZone && fileInput) {
    dropZone.addEventListener("click", function () {
        fileInput.click();
    });

    fileInput.addEventListener("change", function () {
        const file = this.files[0];
        if (file && this.nextElementSibling) {
            this.nextElementSibling.textContent = file.name;
        }
    });

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
            const event = new Event("change", { bubbles: true });
            fileInput.dispatchEvent(event);

            if (fileInput.nextElementSibling) {
                fileInput.nextElementSibling.textContent = files[0].name;
            }
        }
    });
}

// Delete modal functionality
if (typeof $ !== 'undefined') {
    $("#deleteModal").on("show.bs.modal", function (event) {
        const button = $(event.relatedTarget);
        const route = button.data("route");
        const form = $("#deleteForm");
        
        if (form && route) {
            form.attr("action", route);
        }
    });
}

// Toast initialization
const toasts = document.querySelectorAll(".toast");
if (toasts.length > 0 && typeof bootstrap !== 'undefined') {
    toasts.forEach((toastEl) => {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    });
}
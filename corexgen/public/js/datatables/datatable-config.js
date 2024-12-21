// public/js/datatable-config.js
class DataTableConfig {
    constructor(tableId, options = {}) {
        this.tableId = tableId;
        this.options = options;
        this.table = null;
        this.defaultConfig = {
            processing: true,
            serverSide: true,
            stateSave: true,
            orderClasses: false,
            start: 0,
            length: 10,
            searching: true,
            sScrollX: "100%",
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"],
            ],
            order: [],
            language: {
                lengthMenu: "_MENU_ per page",
                processing:
                    '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            },
        };

        this.initializeFilterHandlers();
        this.initializeBulkDelete();
    }

    getConfig() {
        // Process columns to handle render functions
        const processedColumns = this.options.columns.map((column) => {
            if (
                column.render &&
                typeof column.render === "string" &&
                column.render.startsWith("function")
            ) {
                // Convert the string function to an actual function
                column.render = new Function("return " + column.render)();
            }
            return column;
        });

        return {
            ...this.defaultConfig,
            ...this.options,
            columns: processedColumns,
            ajax: {
                url: this.options.ajaxUrl,
                data: (d) => {
                    // Add all filter values
                    $("[data-filter]").each(function () {
                        d[$(this).data("filter")] = $(this).val();
                    });

                    // Allow custom data manipulation
                    if (this.options.additionalData) {
                        this.options.additionalData(d);
                    }
                },
            },
        };
    }

    initializeFilterHandlers() {
        // Handle filter button click
        $(document).on("click", "#filterBtn", () => {
            this.table?.ajax.reload();
            // Close filter sidebar if exists
            const filterSidebar = document.getElementById("filterSidebar");
            if (filterSidebar) {
                filterSidebar.classList.remove("show");
            }
        });

        // Handle clear filter button click
        $(document).on("click", "#clearFilter", () => {
            // Reset all filter inputs
            $("[data-filter]").each(function () {
                $(this).val("");
            });

            // Reload table
            this.table?.ajax.reload();
        });

        // Optional: Handle enter key on filter inputs
        let debounceTimer;
        $(document).on("keypress", "[data-filter]", (e) => {
            if (e.which === 13) {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.table?.ajax.reload();
                }, 300); // 300ms debounce
            }
        });
    }

    initializeSelectAll() {
        $(document).on("change", "#select-all", function () {
            const isChecked = $(this).prop("checked");
            $(".bulk-select").prop("checked", isChecked);
        });

        $(document).on("change", ".bulk-select", function () {
            const allChecked =
                $(".bulk-select:checked").length === $(".bulk-select").length;
            $("#select-all").prop("checked", allChecked);
        });
    }

    initializeBulkDelete() {
        const self = this;

        // Handle bulk delete button click
        $(document).on("click", "#bulk-delete-btn", function () {
            const selectedIds = $(".bulk-select:checked")
                .map(function () {
                    return $(this).data("id");
                })
                .get();

            if (selectedIds.length > 0) {
                // Show confirmation modal
                const bulkDeleteModal = new bootstrap.Modal(
                    document.getElementById("bulkDeleteModal")
                );
                bulkDeleteModal.show();

                // Handle confirm button click
                $("#confirmDeleteBtn")
                    .off("click")
                    .on("click", function () {
                        $.ajax({
                            url: self.options.bulkDeleteUrl,
                            method: "POST",
                            data: {
                                ids: selectedIds,
                                _token: self.options.csrfToken,
                            },
                            success: function (response) {
                                // Hide delete modal
                                bulkDeleteModal.hide();

                                // Show success message
                                const successModal = new bootstrap.Modal(
                                    document.getElementById("successModal")
                                );
                                $("#successModal .modal-body").text(
                                    response.message
                                );
                                successModal.show();

                                // Reload table and reset checkboxes
                                self.table.ajax.reload();
                                $("#select-all").prop("checked", false);
                            },
                            error: function (error) {
                                bulkDeleteModal.hide();

                                // Show error message
                                const alertModal = new bootstrap.Modal(
                                    document.getElementById("alertModal")
                                );
                                $("#alertModal .modal-body").text(
                                    "An error occurred while deleting items."
                                );
                                alertModal.show();
                            },
                        });
                    });
            } else {
                // Show no items selected message
                const alertModal = new bootstrap.Modal(
                    document.getElementById("alertModal")
                );
                $("#alertModal .modal-body").text(
                    "No items selected for deletion."
                );
                alertModal.show();
            }
        });
    }
    init() {
        this.table = $(this.tableId).DataTable(this.getConfig());
        this.initializeSelectAll();
        return this.table;
    }
}

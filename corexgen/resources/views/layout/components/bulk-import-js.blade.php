      <script>
          document.addEventListener('DOMContentLoaded', function() {
              let bulkImportForm = document.querySelector('#bulkImportForm');


              if (bulkImportForm) {
                  bulkImportForm.addEventListener('submit', async function(e) {
                      e.preventDefault();



                      const formData = new FormData(this);
                      const response = await fetch('{{ route(getPanelRoutes($module . '.import')) }}', {
                          method: 'POST',
                          headers: {
                              'X-CSRF-TOKEN': '{{ csrf_token() }}'
                          },
                          body: formData
                      });

                      const result = await response.json();
                      if (result.success) {

                          $('#bulkImportModal').hide();
                          $('.modal-backdrop').remove(); // Remove the backdrop

                          // Show success message
                          const successModal = new bootstrap.Modal(
                              document.getElementById("successModal")
                          );


                          $("#successModal .modal-body").text(
                              result.message
                          );
                          successModal.show();

                          //location.reload();
                      } else {
                          $('#bulkImportModal').hide();
                          $('.modal-backdrop').remove(); // Remove the backdrop

                          const alertModal = new bootstrap.Modal(
                              document.getElementById("alertModal")
                          );
                          $("#alertModal .modal-body").text(
                              "result.message || 'Import failed. Please check the file format.'"
                          );
                          alertModal.show();

                      }
                  });
              }
          });
      </script>

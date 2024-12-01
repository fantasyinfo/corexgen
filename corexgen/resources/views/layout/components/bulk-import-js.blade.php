      <script>
          document.addEventListener('DOMContentLoaded', function() {
              let bulkImportForm = document.querySelector('#bulkImportForm');
              console.log(bulkImportForm);

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
                          alert(result.message);
                          location.reload();
                      } else {
                          alert(result.message || 'Import failed. Please check the file format.');
                      }
                  });
              }
          });
      </script>

<h6>Select Products & Services</h6>
<div class="row mb-4 align-items-center">
    <div class="col-lg-12">
        <select id="selectProduct" class="form-select searchSelectBox">
            <option value="">Select product/service</option>
            @if (isset($products) && $products->isNotEmpty())
                @foreach ($products as $p)
                    <option value="{{ $p->id }}">{{ $p->title }}
                        ({{ $p->type }})
                    </option>
                @endforeach
            @endif
        </select>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-secondary me-2" id="addCustomRow">
        Add Custom Row
    </button>
</div>

<div class="products-container">
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Qty / Per Hour</th>
                <th>Rate ({{ getSettingValue('Currency Symbol') }})</th>
                <th>Tax</th>
                <th class="text-end" style="width:200px;">Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be dynamically added -->
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end">Sub Total:</td>
                <td class="text-end" id="subTotal">{{ getSettingValue('Currency Symbol') }} 0.00
                    {{ getSettingValue('Currency Code') }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end">Discount:</td>
                <td>
                    <div class="input-group">
                        <input type="number" class="form-control" id="discount" name="discount" value="0"
                            min="0" max="100" step="0.01">
                        <span class="input-group-text">%</span>
                    </div>
                </td>
                <td class="text-end" id="discountAmount">{{ getSettingValue('Currency Symbol') }} 0.00
                    {{ getSettingValue('Currency Code') }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end">Tax:</td>
                <td class="text-end" id="totalTax">{{ getSettingValue('Currency Symbol') }} 0.00
                    {{ getSettingValue('Currency Code') }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end">Adjustment:</td>
                <td>
                    <input type="number" class="form-control" id="adjustment" name="adjustment" value="0"
                        step="0.01">
                </td>
                <td class="text-end" id="adjustmentAmount">{{ getSettingValue('Currency Symbol') }} 0.00
                    {{ getSettingValue('Currency Code') }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5" class="text-end fw-bold">Total:</td>
                <td class="text-end fw-bold" id="total">{{ getSettingValue('Currency Symbol') }} 0.00
                    {{ getSettingValue('Currency Code') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            const products = @json($products);
            const taxOptions = @json($tax);
            const productDetails = JSON.parse(@json($proposal->product_details['products'] ?? '[]'));
            const additionalFields = JSON.parse(@json($proposal->product_details['additional_fields'] ?? '{}'));

            const table = $('.products-container table tbody');
            let rowCounter = 0;

            // Populate existing data in edit mode
            if (productDetails.length > 0) {
                productDetails.forEach(product => {
                    addRow(product);
                });
            }

            // Set additional fields
            $('#discount').val(additionalFields.discount || 0);
            $('#adjustment').val(additionalFields.adjustment || 0);
            updateTotals();

            // Event: Product Selection
            $('#selectProduct').on('change', function() {
                const productId = $(this).val();
                const product = products.find(p => p.id == productId);

                if (product) {
                    const existingRow = table.find(`tr[data-product-id="${productId}"]`);
                    if (existingRow.length) {
                        // Increment quantity if product already exists
                        const qtyInput = existingRow.find('.qty-input');
                        qtyInput.val(parseInt(qtyInput.val()) + 1).trigger('change');
                    } else {
                        // Add new row if product doesn't exist
                        addRow(product);
                    }
                }
                $(this).val(''); // Reset dropdown
                updateTotals();
            });

            // Event: Add Custom Row
            $('#addCustomRow').on('click', function() {
                addRow();
            });

            // Event: Adjustment and Discount Change
            $('#adjustment, #discount').on('input', function() {
                updateTotals();
            });

            // Function: Add Row
            function addRow(product = null) {
                rowCounter++;
                const newRow = `
        <tr data-row="${rowCounter}" data-product-id="${product ? product.id || '' : ''}">
            <td>
                <input type="text" class="form-control" name="product_title[]" 
                    value="${product ? product.title || '' : ''}" required>
            </td>
            <td>
                <textarea class="form-control" name="product_description[]" rows="2" required>${product ? product.description || '' : ''}</textarea>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" class="form-control qty-input" name="product_qty[]" 
                        value="${product ? product.qty || 1 : 1}" min="1" step="1" required>
                    <span class="input-group-text">Unit | Hr</span>
                </div>
            </td>
            <td>
                <input type="number" class="form-control rate-input" name="product_rate[]" 
                    value="${product ? product.rate || '' : ''}" step="0.01" required>
            </td>
            <td>
                <select name="product_tax[]" class="form-select tax-select">
                    ${generateTaxOptions(product ? product.tax : null)}
                </select>
            </td>
            <td class="amount-column text-end">0.00</td>
            <td class="action-column">
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
                table.append(newRow);

                const $newRow = table.find('tr:last');
                initializeRowEvents($newRow);
                updateRowAmount($newRow); // Calculate the amount for the new row
                updateTotals(); // Update the overall totals
            }

            // Function: Generate Tax Options
            function generateTaxOptions(selectedTax = null) {
                return taxOptions
                    .map(tax =>
                        `<option value="${tax.name}" data-rate="${tax.name}" ${selectedTax == tax.id ? 'selected' : ''}>${tax.name}</option>`
                    )
                    .join('');
            }

            // Function: Initialize Events for Row
            function initializeRowEvents($row) {
                $row.find('.qty-input, .rate-input, .tax-select').off('input change').on('input change',
                    function() {
                        updateRowAmount($row);
                    });

                $row.find('.remove-row').off('click').on('click', function() {
                    $row.remove();
                    updateTotals();
                });
            }

            // Function: Update Row Amount
            function updateRowAmount($row) {
                const qty = parseFloat($row.find('.qty-input').val()) || 0;
                const rate = parseFloat($row.find('.rate-input').val()) || 0;
                const taxRate = parseFloat($row.find('.tax-select option:selected').data('rate')) || 0;

                const amount = qty * rate;
                $row.find('.amount-column').text(amount.toFixed(2));
                $row.data('amount', amount);
                $row.data('tax', (amount * taxRate / 100).toFixed(2));

                updateTotals();
            }

            // Function: Update Totals
            function updateTotals() {
                let subTotal = 0;
                let totalTax = 0;

                table.find('tr').each(function() {
                    subTotal += parseFloat($(this).data('amount')) || 0;
                    totalTax += parseFloat($(this).data('tax')) || 0;
                });

                const discount = parseFloat($('#discount').val()) || 0;
                const adjustment = parseFloat($('#adjustment').val()) || 0;
                const discountAmount = (subTotal * discount) / 100;
                const total = subTotal - discountAmount + totalTax + adjustment;

                $('#subTotal').text(' {{ getSettingValue('Currency Symbol') }} ' + subTotal.toFixed(2) +
                    ' {{ getSettingValue('Currency Code') }} ');

                $('#totalTax').text(' {{ getSettingValue('Currency Symbol') }} ' + totalTax.toFixed(2) +
                    ' {{ getSettingValue('Currency Code') }} ');

                $('#discountAmount').text(' {{ getSettingValue('Currency Symbol') }} ' + discountAmount.toFixed(
                    2) + ' {{ getSettingValue('Currency Code') }} ');

                $('#adjustmentAmount').text(' {{ getSettingValue('Currency Symbol') }} ' + adjustment.toFixed(2) + ' {{ getSettingValue('Currency Code') }} ');

        

                $('#total').text(' {{ getSettingValue('Currency Symbol') }} ' + total.toFixed(2) +
                    ' {{ getSettingValue('Currency Code') }} ');
            }
        });
    </script>
@endpush

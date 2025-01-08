 <!-- Lead Basic Details -->
 <div class="row g-4">
    <!-- Company & Contact Information -->
    <div class="col-md-6 ">
        <h6 class="text-muted">Tasks Details</h6>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th scope="row"> Type</th>
                    <td id="billable">Billable</td>
                </tr>
                <tr>
                    <th scope="row"> Related To</th>
                    <td id="relatedTo">Related to</td>
                </tr>
                <tr>
                    <th scope="row"> Hourly Rate</th>
                    <td id="hourlyRate">Hourly Rate</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Status, Priority, and Stage -->
    <div class="col-md-6">
        <h6 class="text-muted">Stage, Priority</h6>
        <div class="d-flex align-items-center gap-3 mb-3">
            <table class="table table-bordered">
                <tbody>
                  
                    <tr>
                        <th scope="row">Priority</th>
                        <td >
                            <div id="priority"></div>
                        </td>
                    </tr>

                </tbody>
            </table>





        </div>
        <h6 class="text-muted">Dates</h6>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th scope="row"><i class="fas fa-calendar"></i> Start Date </th>
                    <td id="startDate">N/A</td>
                </tr>
                <tr>
                    <th scope="row"><i class="fas fa-calendar"></i> Due Date</th>
                    <td id="dueDate">N/A</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>


{{-- project details --}}
<div class="row">
    <h6 class="text-muted">Description (Details)</h6>
    <p id="detailsView">Project details.</p>
</div>
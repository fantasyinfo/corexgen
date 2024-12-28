 <!-- Lead Basic Details -->
 <div class="row g-4">
     <!-- Company & Contact Information -->
     <div class="col-md-6 ">
         <h6 class="text-muted">Lead Details</h6>
         <table class="table table-bordered">
             <tbody>
                 <tr>
                     <th scope="row"><i class="fas fa-building"></i> Type</th>
                     <td id="type">Indivisual / Company</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-building"></i> Company</th>
                     <td id="companyName">Company Name</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-user"></i> Contact</th>
                     <td id="contactName">Contact Person Name</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-envelope"></i> Email</th>
                     <td id="email">Contact Email</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-phone"></i> Phone</th>
                     <td id="phone">Contact Phone</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-dollar-sign"></i> Value</th>
                     <td id="value">Contact Value</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-phone"></i> Contact Method</th>
                     <td id="pcm">Contact Method</td>
                 </tr>
             </tbody>
         </table>
     </div>

     <!-- Status, Priority, and Stage -->
     <div class="col-md-6">
         <h6 class="text-muted">Stage, Priority, Source & Group</h6>
         <div class="d-flex align-items-center gap-3 mb-3">
             <table class="table table-bordered">
                 <tbody>
                     <tr>
                         <th scope="row">Stage / Status</th>
                         <td >
                             <div id="stage"></div>
                         </td>
                     </tr>
                     <tr>
                         <th scope="row">Priority</th>
                         <td >
                             <div id="priority"></div>
                         </td>
                     </tr>
                     <tr>
                         <th scope="row">Source</th>
                         <td >
                             <div id="source"></div>
                         </td>
                     </tr>
                     <tr>
                         <th scope="row">Group</th>
                         <td >
                             <div id="group"></div>
                         </td>
                     </tr>
                     <tr>
                         <th scope="row">Score</th>
                         <td >
                             <div id="score"></div>
                         </td>
                     </tr>
                 </tbody>
             </table>





         </div>
         <h6 class="text-muted">Dates</h6>
         <table class="table table-bordered">
             <tbody>
                 <tr>
                     <th scope="row"><i class="fas fa-calendar"></i> Last Contacted</th>
                     <td id="lastContactedDate">N/A</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-calendar"></i> Last Activity</th>
                     <td id="lastActivityDate">N/A</td>
                 </tr>
                 <tr>
                     <th scope="row"><i class="fas fa-calendar"></i> Follow Up</th>
                     <td id="followUpDate">N/A</td>
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #111827;
            color: #e5e7eb;
        }

        .step-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            transition: all 0.3s;
            background-color: #1f2937;
            border: 2px solid #374151;
            color: #9ca3af;
        }

        .step-item.active .step-circle {
            background-color: #6366f1;
            border-color: #6366f1;
            color: white;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .step-item.completed .step-circle {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
            color: white;
        }

        .step-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: #9ca3af;
        }

        .step-item.active .step-text {
            color: #e5e7eb;
        }

        input,
        select {
            background-color: #1f2937;
            border: 1px solid #374151;
            color: #e5e7eb;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            width: 100%;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        label {
            color: #9ca3af;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        button {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        #nextBtn {
            background-color: #6366f1;
            color: white;
        }

        #nextBtn:hover {
            background-color: #4f46e5;
        }

        #prevBtn {
            background-color: #4b5563;
            color: white;
        }

        #prevBtn:hover {
            background-color: #374151;
        }

        #submitBtn {
            background-color: #22c55e;
            color: white;
        }

        #submitBtn:hover {
            background-color: #16a34a;
        }

        .progress-line {
            height: 2px;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
            width: 0%;
            transition: width 0.5s ease;
            position: absolute;

            left: 0;
        }

        .step-item .fa-check {
            display: none;
        }

        .step-item.completed .fa-check {
            display: block;
        }

        .step-item.completed span {
            display: none;
        }

        #skipSmtpBtn {
            background-color: #9ca3af;
            color: white;
        }

        #skipSmtpBtn:hover {
            background-color: #6b7280;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">


    <div class="container mx-auto px-4 ">
        <div class="max-w-4xl mx-auto">

            <div class="flex flex-col items-center mb-8">
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-purple-500 to-pink-500 text-transparent bg-clip-text">
                        Installation Wizard
                    </span>
                </h1>
                <p class="mt-2 text-lg text-gray-400">
                    Follow the steps to configure your application and get started.
                </p>
                <div class="mt-4 w-24 h-1 bg-gradient-to-r from-indigo-400 via-purple-500 to-pink-500 rounded-full">
                </div>
            </div>


            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex justify-between relative">
                    <!-- Progress Line -->
                    <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700 -z-1">
                        <div class="progress-line h-full bg-hostinger-primary transition-all duration-300"
                            style="width: 0%"></div>
                    </div>

                    <!-- Step Items -->
                    <div class="step-item active" data-step="1">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                            <span>1</span>
                        </div>
                        <span class="step-text">Requirements</span>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                            <span>2</span>
                        </div>
                        <span class="step-text">Verification</span>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                            <span>3</span>
                        </div>
                        <span class="step-text">Database</span>
                    </div>
                    <div class="step-item" data-step="4">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                            <span>4</span>
                        </div>
                        <span class="step-text">SMTP</span>
                    </div>
                    <div class="step-item" data-step="5">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                            <span>5</span>
                        </div>
                        <span class="step-text">Admin</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Card -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-hostinger-primary text-white">
                    <h1 class="text-2xl font-bold">Application Installer</h1>
                    <p class="text-blue-100">Complete the installation in a few simple steps</p>
                </div>

                <form id="installerForm" class="p-6">
                    @csrf

                    <!-- Step 1: Requirements -->
                    <div id="step1" class="step-content">
                        <h2 class="text-xl font-semibold mb-4 dark:text-white">
                            <i class="fas fa-list-check mr-2"></i>System Requirements
                        </h2>
                        <div id="requirementsList" class="grid grid-cols-2 gap-4 mb-6">
                            <!-- Requirements will be dynamically populated -->
                        </div>
                    </div>

                    <!-- Step 2: Purchase Code Verification -->
                    <div id="step2" class="step-content hidden">
                        <h2 class="text-xl font-semibold mb-4 dark:text-white">
                            <i class="fas fa-key mr-2"></i>Purchase Verification
                        </h2>
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-2 dark:text-white">Purchase Code</label>
                            <input type="text" name="purchase_code"
                                class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Enter your purchase code">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Enter the purchase code from your Envato purchase
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Database Configuration -->
                    <div id="step3" class="step-content hidden">
                        <h2 class="text-xl font-semibold mb-4 dark:text-white">
                            <i class="fas fa-database mr-2"></i>Database Configuration
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Database Host</label>
                                <input name="db_host"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    value="localhost">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Database Port</label>
                                <input name="db_port"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    value="3306">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Database Name</label>
                                <input name="db_name"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Username</label>
                                <input name="db_username"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Password</label>
                                <input type="password" name="db_password"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: SMTP Configuration -->
                    <div id="step4" class="step-content hidden">
                        <h2 class="text-xl font-semibold mb-4 dark:text-white">
                            <i class="fas fa-envelope mr-2"></i>SMTP Configuration
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">SMTP Host</label>
                                <input name="smtp_host"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">SMTP Port</label>
                                <input name="smtp_port"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">SMTP Username</label>
                                <input name="smtp_username"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">SMTP Password</label>
                                <input type="password" name="smtp_password"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Encryption</label>
                                <select name="smtp_encryption"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium mb-2 dark:text-white">From Email</label>
                                <input name="mail_from_address" type="email"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">From Name</label>
                                <input name="mail_from_name"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="button" id="skipSmtpBtn" class="px-6 py-2 rounded hover:opacity-90">
                                <i class="fas fa-forward mr-2"></i>Skip SMTP Configuration
                            </button>
                            <p class="text-sm text-gray-500 mt-2">You can configure SMTP settings later in the admin
                                panel</p>
                        </div>
                    </div>

                    <!-- Step 5: Admin Configuration -->
                    <div id="step5" class="step-content hidden">
                        <h2 class="text-xl font-semibold mb-4 dark:text-white">
                            <i class="fas fa-user-shield mr-2"></i>Admin Setup
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Site Name</label>
                                <input name="site_name"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Admin Name</label>
                                <input name="name"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Admin Email</label>
                                <input name="admin_email" type="email"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium mb-2 dark:text-white">Admin Password</label>
                                <input name="admin_password" type="password"
                                    class="w-full px-4 py-2 rounded border dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-6 flex justify-between">
                        <button type="button" id="prevBtn"
                            class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 hidden">
                            <i class="fas fa-arrow-left mr-2"></i>Previous
                        </button>
                        <button type="button" id="nextBtn"
                            class="px-6 py-2 bg-hostinger-primary text-white rounded hover:bg-hostinger-secondary">
                            Next<i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-hostinger-accent text-white rounded hover:bg-green-600 hidden">
                            <i class="fas fa-check mr-2"></i>Complete Installation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-hostinger-primary"></div>
                <p class="dark:text-white" id="loadingText">Please wait...</p>
            </div>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-md w-full">
            <h3 class="text-lg font-bold mb-4 dark:text-white" id="alertTitle">Alert</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-4" id="alertMessage"></p>
            <div class="flex justify-end">
                <button onclick="closeAlertModal()"
                    class="px-4 py-2 bg-hostinger-primary text-white rounded hover:bg-hostinger-secondary">
                    OK
                </button>
            </div>
        </div>
    </div>



    <script>
        function showAlertWithHTML(title, htmlContent) {
            // Create the modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.className =
                'modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';

            // Create the alert modal
            const alertModal = document.createElement('div');
            alertModal.className = 'alert-modal p-6 w-full max-w-md text-red-800 bg-white rounded-lg shadow-lg';

            alertModal.innerHTML = `
        <div class="flex items-start">
            <div class="mr-3 mt-1">
                <i class="fas fa-times-circle text-red-600"></i>
            </div>
            <div>
                <strong class="font-bold">${title}</strong>
                <div class="mt-2">${htmlContent}</div>
            </div>
        </div>
        <div class="flex justify-end mt-4">
            <button id="closeModalButton" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Close</button>
        </div>
    `;

            // Append the alert modal to the overlay
            modalOverlay.appendChild(alertModal);

            // Append the modal overlay to the body
            document.body.appendChild(modalOverlay);

            // Close the modal when the close button is clicked
            document.getElementById('closeModalButton').addEventListener('click', () => {
                modalOverlay.remove();
            });

            // Optionally, remove the modal after some time

        }

        function showSuccessModal(title, htmlContent) {
            // Create the modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.className =
                'modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';

            // Create the success modal
            const successModal = document.createElement('div');
            successModal.className =
                'success-modal relative p-8 w-full max-w-3xl text-center bg-white rounded-2xl shadow-2xl animate__animated animate__fadeInDown';

            successModal.innerHTML = `
        <div class="flex justify-center mb-4">
            <i class="fas fa-check-circle text-green-500 text-6xl animate__animated animate__bounceIn"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-gray-800 mb-2">${title}</h2>
        <p class="text-lg text-gray-600 mb-6">
            ${htmlContent}
        </p>
        
        <div class="flex gap-4 justify-center mb-6">
            <a href="/super-admin-login" class="px-8 py-3 bg-blue-600 text-white rounded-full shadow-md hover:bg-blue-500 transition transform hover:scale-105">
                <i class="fas fa-user-shield mr-2"></i> Super Admin Panel
            </a>
        </div>
        <p class="text-gray-500 text-sm mb-4">
            If you have any questions, feel free to reach out to our support team. We are here to help!
        </p>
        <div id="confettiCanvas"></div>
    `;

            // Append the success modal to the overlay
            modalOverlay.appendChild(successModal);

            // Append the modal overlay to the body
            document.body.appendChild(modalOverlay);

            // Close the modal when clicked outside
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) {
                    modalOverlay.remove();
                }
            });

            // Adding confetti animation using Canvas Confetti library
            const confettiCanvas = document.createElement('canvas');
            document.getElementById('confettiCanvas').appendChild(confettiCanvas);
            const myConfetti = confetti.create(confettiCanvas, {
                resize: true
            });
            myConfetti({
                particleCount: 150,
                spread: 70,
                origin: {
                    y: 0.6
                },
            });


        }



        // Installation Process
        let currentStep = 1;
        const totalSteps = 5;
        const form = document.getElementById('installerForm');
        const nextBtn = document.getElementById('nextBtn');
        const skipSmtpBtn = document.getElementById('skipSmtpBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');

        // Update Progress
        function updateProgress() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.querySelector('.progress-line').style.width = `${progress}%`;

            // Update step status
            document.querySelectorAll('.step-item').forEach((item, index) => {
                const step = index + 1;
                if (step < currentStep) {
                    item.classList.add('completed');
                    item.classList.remove('active');
                } else if (step === currentStep) {
                    item.classList.add('active');
                    item.classList.remove('completed');
                } else {
                    item.classList.remove('active', 'completed');
                }
            });
        }



        // Show Loading
        function showLoading(text = 'Please wait...') {
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingModal').classList.remove('hidden');
        }

        // Hide Loading
        function hideLoading() {
            document.getElementById('loadingModal').classList.add('hidden');
        }

        // Show Alert
        function showAlert(title, message) {
            document.getElementById('alertTitle').textContent = title;
            document.getElementById('alertMessage').textContent = message;
            document.getElementById('alertModal').classList.remove('hidden');
        }

        // Close Alert Modal
        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
        }

        // Check Requirements
        async function checkRequirements() {
            showLoading('Checking system requirements...');
            try {
                const response = await fetch('/installer/requirements');
                const data = await response.json();

                const requirementsList = document.getElementById('requirementsList');
                requirementsList.innerHTML = '';

                // PHP Version
                requirementsList.innerHTML += `
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <span class="dark:text-white">PHP >= 8.1</span>
                        <i class="fas ${data.details.php_version ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i>
                    </div>
                `;

                // Extensions
                // Correctly iterate over the extensions object
                Object.entries(data.details.extensions).forEach(([ext, loaded]) => {
                    requirementsList.innerHTML += `
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="dark:text-white">${ext.toUpperCase()}</span>
                            <i class="fas ${loaded ? 'fa-check text-green-500' : 'fa-times text-red-500'}"></i>
                        </div>
                    `;
                });

                return data.pass;
            } catch (error) {
                showAlert('Error', 'Failed to check system requirements');
                return false;
            } finally {
                hideLoading();
            }
        }

        // Verify Purchase Code
        async function verifyPurchaseCode() {
            const purchaseCode = form.querySelector('[name="purchase_code"]').value;
            if (!purchaseCode) {
                showAlert('Error', 'Please enter your purchase code');
                return false;
            }

            showLoading('Verifying purchase code...');
            try {
                const response = await fetch('/installer/verify-purchase', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({
                        purchase_code: purchaseCode
                    })
                });
                const data = await response.json();

                if (!data.success) {
                    showAlert('Error', data.message);
                    return false;
                }
                return true;
            } catch (error) {
                showAlert('Error', 'Failed to verify purchase code');
                return false;
            } finally {
                hideLoading();
            }
        }

        // Test Database Connection
        async function testDatabase() {
            const dbData = {
                db_host: form.querySelector('[name="db_host"]').value,
                db_port: form.querySelector('[name="db_port"]').value,
                db_name: form.querySelector('[name="db_name"]').value,
                db_username: form.querySelector('[name="db_username"]').value,
                db_password: form.querySelector('[name="db_password"]').value
            };

            showLoading('Testing database connection...');
            try {
                const response = await fetch('/installer/test-database', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(dbData)
                });
                const data = await response.json();

                if (!response.ok) {
                    // Extract errors from data and create formatted HTML
                    const errorMessages = Object.values(data.errors)
                        .flat()
                        .map(error => `<div class="flex items-center text-red-600">
                                  <i class="fas fa-exclamation-circle mr-2"></i>
                                  <span>${error}</span>
                               </div>`)
                        .join('');

                    showAlertWithHTML('Error', errorMessages);
                    return false;
                }

                if (!data.success) {
                    showAlert('Error', data.message);
                    return false;
                }
                return true;
            } catch (error) {
                showAlert('Error', 'Failed to connect to database, use correct details');
                return false;
            } finally {
                hideLoading();
            }
        }

         // Add a global variable to track SMTP skip status
        let skipSmtp = false;

        // Test SMTP Connection
        async function testSmtp() {

            // If SMTP is explicitly skipped, return true
            if (skipSmtp) {
                return true;
            }

            
            const smtpData = {
                smtp_host: form.querySelector('[name="smtp_host"]').value,
                smtp_port: form.querySelector('[name="smtp_port"]').value,
                smtp_username: form.querySelector('[name="smtp_username"]').value,
                smtp_password: form.querySelector('[name="smtp_password"]').value,
                smtp_encryption: form.querySelector('[name="smtp_encryption"]').value,
                mail_from_address: form.querySelector('[name="mail_from_address"]').value,
                mail_from_name: form.querySelector('[name="mail_from_name"]').value
            };

            showLoading('Testing SMTP connection...');
            try {
                const response = await fetch('/installer/test-smtp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(smtpData)
                });
                const data = await response.json();

                if (!response.ok) {
                    // Extract errors from data and create formatted HTML
                    const errorMessages = Object.values(data.errors)
                        .flat()
                        .map(error => `<div class="flex items-center text-red-600">
                                  <i class="fas fa-exclamation-circle mr-2"></i>
                                  <span>${error}</span>
                               </div>`)
                        .join('');

                    showAlertWithHTML('Error', errorMessages);
                    return false;
                }

                if (!data.success) {
                    showAlert('Error', data.message);
                    return false;
                }
                return true;
            } catch (error) {
                showAlert('Error', 'Failed to test SMTP connection');
                return false;
            } finally {
                hideLoading();
            }
        }

        // Handle Next Button
        nextBtn.addEventListener('click', async () => {
            let canProceed = true;

            switch (currentStep) {
                case 1:
                    canProceed = await checkRequirements();
                    break;
                case 2:
                    canProceed = await verifyPurchaseCode();
                    break;
                case 3:
                    canProceed = await testDatabase();
                    break;
                case 4:
                    canProceed = await testSmtp();
                    break;
            }

            if (canProceed) {
                document.querySelector(`#step${currentStep}`).classList.add('hidden');
                currentStep++;
                document.querySelector(`#step${currentStep}`).classList.remove('hidden');

                // Update navigation buttons
                prevBtn.classList.remove('hidden');
                if (currentStep === totalSteps) {
                    nextBtn.classList.add('hidden');
                    submitBtn.classList.remove('hidden');
                }

                updateProgress();
            }
        });

        // Handle Previous Button
        prevBtn.addEventListener('click', () => {
            document.querySelector(`#step${currentStep}`).classList.add('hidden');
            currentStep--;
            document.querySelector(`#step${currentStep}`).classList.remove('hidden');

            // Update navigation buttons
            if (currentStep === 1) {
                prevBtn.classList.add('hidden');
            }
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');

            updateProgress();
        });

        // Handle Form Submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            showLoading('Installing application...');

            try {
                const formData = new FormData(form);

                // If SMTP was skipped, don't send SMTP-related fields
                if (skipSmtp) {
                    formData.delete('smtp_host');
                    formData.delete('smtp_port');
                    formData.delete('smtp_username');
                    formData.delete('smtp_password');
                    formData.delete('smtp_encryption');
                    formData.delete('mail_from_address');
                    formData.delete('mail_from_name');
                }


                const response = await fetch('/installer/install', {
                    method: 'POST',
                    body: formData,
                });
                const data = await response.json();

                if (!response.ok) {
                    const errorMessages = Object.values(data.errors)
                        .flat()
                        .map(
                            (error) =>
                            `<div class="flex items-center text-red-600">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>${error}</span>
                        </div>`
                        )
                        .join('');
                    showAlertWithHTML('Error', errorMessages);
                    return false;
                }

                if (data.status === 'success') {
                    const successMessageContent = `
                Thank you for purchasing our software. We are thrilled to have you on board!<br>
                A welcome email has been sent to your registered email address with your <strong>Buyer Identification Number</strong> to log in to the Buyer Admin Panel.<br>
                Please check your inbox (and spam folder) for the email.
            `;

                    showSuccessModal(
                        'Installation Completed Successfully!',
                        successMessageContent
                    );
                } else {
                    showAlert('Error', data.message);
                }
            } catch (error) {
                if (error.message.includes('ERR_CONNECTION_RESET')) {
                    // Check installation status
                    try {
                        const statusResponse = await fetch('/status');
                        const statusData = await statusResponse.json();

                        if (statusData.status === 'success') {
                            const successMessageContent = `
                        Thank you for purchasing our software. We are thrilled to have you on board!<br>
                        A welcome email has been sent to your registered email address with your <strong>Buyer Identification Number</strong> to log in to the Buyer Admin Panel.<br>
                        Please check your inbox (and spam folder) for the email.
                    `;

                            showSuccessModal(
                                'Installation Completed Successfully!',
                                successMessageContent
                            );
                        } else {
                            showAlert(
                                'Error',
                                'The installation was interrupted. Please try again.'
                            );
                        }
                    } catch (statusError) {
                        showAlert(
                            'Error',
                            'Unable to verify installation status. Please try again.'
                        );
                    }
                } else {
                    showAlert('Error', 'Installation failed. Please try again.');
                }
            } finally {
                hideLoading();
            }
        });



          // Add event listener for skipping SMTP configuration
        skipSmtpBtn.addEventListener('click', () => {
            // Mark SMTP as skipped
            skipSmtp = true;
            
            document.querySelector(`#step${currentStep}`).classList.add('hidden');
            currentStep++;
            document.querySelector(`#step${currentStep}`).classList.remove('hidden');

            // Update navigation buttons
            if (currentStep === totalSteps) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            }

            updateProgress();
        });
        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            updateProgress();
            checkRequirements();
        });
    </script>
</body>

</html>

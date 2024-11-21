<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 text-white">
                <h1 class="text-2xl font-bold">Project Installer</h1>
                <p class="text-blue-100">Configure your application in a few simple steps</p>
            </div>
            
            <form id="installerForm" class="p-6">
                @csrf
                <div id="stepRequirements">
                    <h2 class="text-xl font-semibold mb-4">System Requirements</h2>
                    <div id="requirementsList" class="grid grid-cols-2 gap-4 mb-4">
                        <!-- Requirements will be dynamically populated -->
                    </div>
                    <button type="button" id="nextRequirements" class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                        Next: Database Configuration
                    </button>
                </div>

                <div id="stepDatabase" class="hidden">
                    <h2 class="text-xl font-semibold mb-4">Database Configuration</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input name="db_host" placeholder="Database Host" class="border p-2 rounded" value="localhost">
                        <input name="db_port" placeholder="Database Port" class="border p-2 rounded" value="3306">
                        <input name="db_name" placeholder="Database Name" class="border p-2 rounded" required>
                        <input name="db_username" placeholder="Database Username" class="border p-2 rounded" required>
                        <input name="db_password" type="password" placeholder="Database Password" class="border p-2 rounded col-span-2">
                    </div>
                    <div class="mt-4">
                        <button type="button" id="testConnection" class="w-full py-2 px-4 bg-green-600 text-white rounded hover:bg-green-700">
                            Test Database Connection
                        </button>
                    </div>
                </div>

                <div id="stepApplication" class="hidden">
                    <h2 class="text-xl font-semibold mb-4">Application Setup</h2>
                    <div class="grid grid-cols-1 gap-4">
                        <input name="site_name" placeholder="Site Name" class="border p-2 rounded" required>
                        <input name="name" placeholder="Full Name" class="border p-2 rounded" required>
                        <input name="admin_email" type="email" placeholder="Admin Email" class="border p-2 rounded" required>
                        <input name="admin_password" type="password" placeholder="Admin Password" class="border p-2 rounded" required>
                        <input name="purchase_code" placeholder="Purchase Code" class="border p-2 rounded" required>
                    </div>
                    <button type="submit" class="w-full mt-4 py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Complete Installation
                    </button>
                </div>
            </form>
        </div>
    </div>

 <!-- Tailwind Modal for Installation Complete -->
<div id="installationModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Installation Completed</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Congratulations! Your application has been successfully installed. Click the button below to complete the setup and proceed to the login page.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button id="completeSetupButton" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Complete Setup & Go to Login
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('installerForm');
        const requirementsList = document.getElementById('requirementsList');
        const stepRequirements = document.getElementById('stepRequirements');
        const stepDatabase = document.getElementById('stepDatabase');
        const stepApplication = document.getElementById('stepApplication');
        const nextRequirements = document.getElementById('nextRequirements');
        const testConnection = document.getElementById('testConnection');
        const installationModal = document.getElementById('installationModal');
        const completeSetupButton = document.getElementById('completeSetupButton');

        // Load system requirements
        async function loadRequirements() {
            try {
                const response = await fetch('/installer/requirements');
                const data = await response.json();
                
                requirementsList.innerHTML = Object.entries(data.details)
                    .map(([key, status]) => `
                        <div class="flex items-center ${status ? 'text-green-600' : 'text-red-600'}">
                            ${status 
                                ? '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>'
                                : '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>'
                            }
                            ${key}
                        </div>
                    `).join('');

                nextRequirements.disabled = !data.pass;
            } catch (error) {
                console.error('Error loading requirements:', error);
            }
        }

        // Navigation between steps
        nextRequirements.addEventListener('click', function() {
            stepRequirements.classList.add('hidden');
            stepDatabase.classList.remove('hidden');
        });

        testConnection.addEventListener('click', async function() {
            const formData = new FormData(form);
            try {
                const response = await fetch('/installer/test-database', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    stepDatabase.classList.add('hidden');
                    stepApplication.classList.remove('hidden');
                } else {
                    alert('Database connection failed: ' + result.message);
                }
            } catch (error) {
                console.error('Connection test failed:', error);
            }
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/installer/install', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Show the modal for installation completion
                    installationModal.classList.remove('hidden');
                } else {
                    console.error('Installation failed:', result.message);
                    alert('Installation failed: ' + result.message);
                }
            } catch (error) {
                console.error('Installation process failed:', error);
                alert('Installation process failed. Please try again.');
            }
        });

        // Handle Complete Setup button click
        completeSetupButton.addEventListener('click', async function() {
            try {
                // Step 1: Update .env file
                const updateResponse = await fetch('/installer/update-env', {
                    method: 'POST',
                    
                });
                const updateResult = await updateResponse.json();

                if (updateResult.status === 'success') {
                    console.log('Environment file updated successfully.');
                    
                    // Step 2: Redirect to the login page
                    window.location.href = '/login';
                } else {
                    console.error('Failed to update environment file:', updateResult.message);
                    alert('Environment update failed. Please contact support.');
                }
            } catch (error) {
                console.error('Failed to update environment file:', error);
                alert('Failed to complete setup. Please try again.');
            }
        });

        // Initial load of requirements
        loadRequirements();
    });
</script>
</body>
</html>
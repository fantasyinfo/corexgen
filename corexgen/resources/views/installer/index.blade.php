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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('installerForm');
        const requirementsList = document.getElementById('requirementsList');
        const stepRequirements = document.getElementById('stepRequirements');
        const stepDatabase = document.getElementById('stepDatabase');
        const stepApplication = document.getElementById('stepApplication');
        const nextRequirements = document.getElementById('nextRequirements');
        const testConnection = document.getElementById('testConnection');

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
                
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                }
            } catch (error) {
                console.error('Installation failed:', error);
            }
        });

        // Initial load of requirements
        loadRequirements();
    });
    </script>
</body>
</html>
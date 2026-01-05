<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$config = require 'config.php';

$client->setClientId($config['GOOGLE_CLIENT_ID']);
$client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($config['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope(['https://www.googleapis.com/auth/userinfo.email', 
                   'https://www.googleapis.com/auth/spreadsheets',
                   'https://www.googleapis.com/auth/drive.readonly']); // Add Drive scope for Picker

$client->setAccessType('offline');
$authUrl = $client->createAuthUrl();

// Store the auth URL in session for redirect.php
session_start();
$_SESSION['auth_url'] = $authUrl;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Auth with Spreadsheet Picker</title>
    <!-- Google Picker API -->
    <script type="text/javascript" src="https://apis.google.com/js/api.js"></script>
</head>
<body>
    <h1>Google Spreadsheet Writer</h1>
    
    <a href="<?= $authUrl ?>" id="auth-link">Sign in with Google</a>
    
    <div id="picker-container" style="display:none;">
        <h3>Select a Spreadsheet:</h3>
        <button onclick="showPicker()">Choose Spreadsheet</button>
        <input type="text" id="selected-spreadsheet" placeholder="No spreadsheet selected" readonly style="width: 300px;">
        <input type="hidden" id="selected-spreadsheet-id">
        
        <div id="write-data-section" style="display:none; margin-top: 20px;">
            <h3>Enter Data to Write:</h3>
            <input type="text" id="data-to-write" placeholder="Enter data to write to spreadsheet">
            <button onclick="writeToSpreadsheet()">Write to Spreadsheet</button>
        </div>
    </div>

    <script>
        // Load the Google Picker API
        function loadPicker() {
            gapi.load('picker', function() {
                console.log('Picker API loaded');
                document.getElementById('picker-container').style.display = 'block';
            });
        }

        // Show the Google Picker
        function showPicker() {
            const accessToken = gapi.auth.getToken().access_token;
            
            const view = new google.picker.DocsView(google.picker.ViewId.SPREADSHEETS)
                .setIncludeFolders(true)
                .setSelectFolderEnabled(false);

            const picker = new google.picker.PickerBuilder()
                .addView(view)
                .setOAuthToken(accessToken)
                .setDeveloperKey('<?= $config['GOOGLE_API_KEY'] ?? '' ?>') // You need an API key from Google Cloud Console
                .setCallback(pickerCallback)
                .build();
            
            picker.setVisible(true);
        }

        // Handle the picker selection
        function pickerCallback(data) {
            if (data[google.picker.Response.ACTION] === google.picker.Action.PICKED) {
                const doc = data[google.picker.Response.DOCUMENTS][0];
                const spreadsheetId = doc.id;
                const spreadsheetName = doc.name;
                
                // Store the selected spreadsheet ID
                document.getElementById('selected-spreadsheet').value = spreadsheetName;
                document.getElementById('selected-spreadsheet-id').value = spreadsheetId;
                
                // Show the write data section
                document.getElementById('write-data-section').style.display = 'block';
                
                // Store in localStorage for redirect
                localStorage.setItem('selectedSpreadsheetId', spreadsheetId);
                localStorage.setItem('selectedSpreadsheetName', spreadsheetName);
            }
        }

        // Function to write data to the selected spreadsheet
        function writeToSpreadsheet() {
            const spreadsheetId = document.getElementById('selected-spreadsheet-id').value;
            const data = document.getElementById('data-to-write').value;
            
            if (!spreadsheetId) {
                alert('Please select a spreadsheet first');
                return;
            }
            
            if (!data) {
                alert('Please enter data to write');
                return;
            }
            
            // Redirect to write.php with the spreadsheet ID and data
            window.location.href = `write.php?spreadsheet_id=${encodeURIComponent(spreadsheetId)}&data=${encodeURIComponent(data)}`;
        }

        // Check if user is already authenticated
        function checkAuth() {
            const urlParams = new URLSearchParams(window.location.search);
            const accessToken = urlParams.get('access_token');
            
            if (accessToken) {
                // User is authenticated, load the picker
                gapi.auth.setToken({ access_token: accessToken });
                loadPicker();
                document.getElementById('auth-link').style.display = 'none';
            }
        }

        // Initialize on page load
        window.onload = checkAuth;
    </script>
</body>
</html>
<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_email'])) {
    // Not authenticated, redirect to index
    header('Location: index.php');
    exit;
}

require 'vendor/autoload.php';
$config = require 'config.php';

// Get the access token from session
$accessToken = $_SESSION['access_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Spreadsheet - Google Picker</title>
    <!-- Google Picker API -->
    <script type="text/javascript" src="https://apis.google.com/js/api.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .user-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .picker-section {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .button {
            background: #4285f4;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 0;
        }
        .button:hover {
            background: #3367d6;
        }
        .button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        #selected-spreadsheet {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            border-radius: 4px;
        }
        #data-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #f0f8ff;
            border-radius: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            min-height: 100px;
        }
        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .logout {
            float: right;
            background: #f44336;
            color: white;
            padding: 5px 15px;
            text-decoration: none;
            border-radius: 4px;
        }
        .logout:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h2>
        <p>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        <a href="logout.php" class="logout">Logout</a>
        <div style="clear: both;"></div>
    </div>

    <div class="picker-section">
        <h2>Select a Google Spreadsheet</h2>
        <p>Choose a spreadsheet where you want to add data. Make sure you have edit access to the spreadsheet.</p>
        <div id="loading" style="display:none;">Loading...</div>
        <button class="button" onclick="showPicker()" id="picker-button">Open Google Picker</button>
        
        <div id="selected-spreadsheet">
            <strong>Selected Spreadsheet:</strong>
            <span id="spreadsheet-name">None selected</span>
            <input type="hidden" id="spreadsheet-id">
        </div>
        
        <div id="data-form">
            <h3>Enter Data to Add</h3>
            <form id="write-form">
                <div>
                    <label for="data-input">Data to add:</label>
                    <textarea id="data-input" placeholder="Enter the data you want to add to the spreadsheet..."></textarea>
                </div>
                
                <div>
                    <label for="column-select">Add to column:</label>
                    <select id="column-select">
                        <option value="A">Column A</option>
                        <option value="B">Column B</option>
                        <option value="C">Column C</option>
                        <option value="D">Column D</option>
                        <option value="E">Column E</option>
                    </select>
                </div>
                
                <button type="button" class="button" onclick="submitData()">Add to Spreadsheet</button>
                <button type="button" class="button" onclick="resetSelection()" style="background: #666;">Select Different Spreadsheet</button>
            </form>
            
            <div id="message"></div>
        </div>
    </div>
    
    <div id="recent-spreadsheets" style="display: none;">
        <h3>Recently Selected</h3>
        <div id="recent-list"></div>
    </div>

    <script>

    function submitData() {
    const spreadsheetId = document.getElementById('spreadsheet-id').value;
    const dataValue = document.getElementById('data-input').value;
    const column = document.getElementById('column-select').value;
    const messageEl = document.getElementById('message');

    if (!spreadsheetId || !dataValue) {
        alert('Please select a spreadsheet and enter data.');
        return;
    }

    const range = `${column}:${column}`; 
    // FIXED: Added https:// and ensured backticks are used
    const url = `https://sheets.googleapis.com/v4/spreadsheets/${spreadsheetId}/values/${range}:append?valueInputOption=USER_ENTERED`;

    fetch(url, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${accessToken}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            values: [[dataValue]] // This 2D array structure is correct
        })
    })
    .then(async response => {
        // Handle non-JSON or error responses before parsing
        const isJson = response.headers.get('content-type')?.includes('application/json');
        const data = isJson ? await response.json() : null;

        if (!response.ok) {
            const errorMsg = data?.error?.message || 'The server returned an error page. Check your API scopes.';
            throw new Error(errorMsg);
        }
        return data;
    })
    .then(result => {
        messageEl.innerHTML = `<div class="success">Data added successfully!</div>`;
        document.getElementById('data-input').value = ''; 
    })
    .catch(error => {
        console.error('API Error:', error);
        messageEl.innerHTML = `<div class="error">Error: ${error.message}</div>`;
    });
}


        
    let pickerApiLoaded = false;
    let accessToken = '<?php echo $accessToken['access_token']; ?>';
    // Initialize the loading process
window.onload = function() {
    loadPickerApi();
};


    
    // Load Google Picker API directly
    function loadPickerApi() {
        gapi.load('picker', {
            callback: function() {
                pickerApiLoaded = true;
                document.getElementById('picker-button').disabled = false;
                document.getElementById('picker-button').textContent = 'Open Google Picker';
                document.getElementById('loading').style.display = 'none';
                console.log('Picker loaded');
            },
            onerror: function() {
                document.getElementById('loading').innerHTML = 
                    '<div class="error">Failed to load Google Picker. Please refresh.</div>';
            },
            timeout: 10000, // 10 seconds timeout
            ontimeout: function() {
            // REQUIRED: This runs if the 10-second limit is reached
            console.error('Google Picker load timed out.');
            alert('Loading took too long. Please check your internet connection and refresh.');
        }
        });
    }
    
    function showPicker() {
        if (!pickerApiLoaded) {
            alert('Google Picker is still loading. Please wait.');
            return;
        }
        
        if (!accessToken) {
            alert('No access token. Please log in again.');
            return;
        }
        
        // Simple picker setup
        const view = new google.picker.DocsView(google.picker.ViewId.SPREADSHEETS)
            .setIncludeFolders(true);
            
        const picker = new google.picker.PickerBuilder()
            .addView(view)
            .setOAuthToken(accessToken)
            .setDeveloperKey('<?php echo $config['GOOGLE_API_KEY'] ?? ''; ?>')
            .setCallback(function(data) {
                if (data.action === google.picker.Action.PICKED) {
                    const doc = data.docs[0];
                    document.getElementById('spreadsheet-name').textContent = doc.name;
                    document.getElementById('spreadsheet-id').value = doc.id;
                    document.getElementById('data-form').style.display = 'block';
                    document.getElementById('picker-button').textContent = 'Change Selection';
                }
            })
            .build();
            
        picker.setVisible(true);
    }
    
    // Load on window load
    window.onload = function() {
        if (typeof gapi === 'undefined') {
            document.getElementById('loading').innerHTML = 
                '<div class="error">Failed to load Google APIs. Check internet connection.</div>';
            return;
        }
        
        loadPickerApi();
    };
</script>
   
</body>
</html>
<?php
session_start(); // Add this at the top
require 'vendor/autoload.php';

$client = new Google_Client();
$config = require 'config.php';

$client->setClientId($config['GOOGLE_CLIENT_ID']);
$client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($config['GOOGLE_REDIRECT_URI']);

// Add all required scopes
$client->addScope('email');
$client->addScope('profile');
$client->addScope('https://www.googleapis.com/auth/spreadsheets');
$client->addScope('https://www.googleapis.com/auth/drive.file');

$client->setAccessType('offline');
$client->setPrompt('consent'); // This ensures refresh_token is returned

if (!isset($_GET['code'])) {
    // No auth code, redirect to auth
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    try {
        // Exchange auth code for access token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Check for errors
        if (isset($token['error'])) {
            echo '<h3>Error fetching access token</h3>';
            echo '<pre>' . print_r($token, true) . '</pre>';
            exit;
        }
        
        // Debug: Show the token structure
        echo '<h3>Token Received</h3>';
        echo '<pre>' . print_r($token, true) . '</pre>';
        
        // Set the access token properly
        $client->setAccessToken($token);
        
        // Verify the token is valid
        if (!$client->getAccessToken()) {
            echo '<h3>Error: No valid access token received</h3>';
            exit;
        }
        
        // Check if token is expired
        if ($client->isAccessTokenExpired()) {
            echo '<h3>Token expired</h3>';
            // Try to refresh if we have a refresh token
            if (isset($token['refresh_token'])) {
                $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
                $newToken = $client->getAccessToken();
                $token = array_merge($token, $newToken);
                $client->setAccessToken($token);
            }
        }
        
        // Store token in session for later use
        $_SESSION['access_token'] = $token;
        
        // Get user info
        try {
            $oauth2 = new Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            
            // Store user info in session
            $_SESSION['user_email'] = $userInfo->email;
            $_SESSION['user_name'] = $userInfo->name;
            $_SESSION['user_picture'] = $userInfo->picture ?? null;
            
            echo '<h3>User Information</h3>';
            echo '<pre>' . print_r($userInfo, true) . '</pre>';
            
        } catch (Exception $e) {
            echo '<h3>Error fetching user info</h3>';
            echo '<p>' . $e->getMessage() . '</p>';
        }
        
        // Now try to access Google Sheets
        try {
            $service = new Google_Service_Sheets($client);
            
            // If you have a spreadsheet ID from session, write to it
            if (isset($_SESSION['selected_spreadsheet_id']) && isset($_SESSION['data_to_write'])) {
                $spreadsheetId = $_SESSION['selected_spreadsheet_id'];
                $data = $_SESSION['data_to_write'];
                
                // Clear session variables
                unset($_SESSION['selected_spreadsheet_id']);
                unset($_SESSION['data_to_write']);
                
                // Write to spreadsheet
                $range = 'Sheet1!A:A';
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues();
                
                $nextRow = 1;
                if ($values) {
                    $nextRow = count($values) + 1;
                }
                
                $writeRange = 'Sheet1!A' . $nextRow;
                $values = [[
                    $data,
                    date('Y-m-d H:i:s'),
                    $_SESSION['user_email'] ?? 'Unknown User'
                ]];
                
                $body = new Google_Service_Sheets_ValueRange([
                    'values' => $values
                ]);
                
                $params = ['valueInputOption' => 'USER_ENTERED'];
                $result = $service->spreadsheets_values->update($spreadsheetId, $writeRange, $body, $params);
                
                echo '<h3>Success!</h3>';
                echo '<p>Data written to spreadsheet.</p>';
                echo '<p>Rows updated: ' . $result->getUpdatedCells() . '</p>';
                
            } else {
                // No spreadsheet selected yet
                echo '<h3>Authentication Successful!</h3>';
                echo '<p>You are now logged in as: ' . ($_SESSION['user_email'] ?? 'User') . '</p>';
                echo '<p><a href="picker.php">Go to Spreadsheet Picker</a></p>';
                echo '<p><a href="index.php">Go back to Home</a></p>';
            }
            
        } catch (Exception $e) {
            echo '<h3>Error accessing Google Sheets</h3>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<p>Make sure you have the correct scopes and permissions.</p>';
        }
        
    } catch (Exception $e) {
        echo '<h3>Exception during authentication</h3>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . print_r(error_get_last(), true) . '</pre>';
    }
}

// Optional: Debug function to check session
echo '<hr><h3>Session Data</h3>';
echo '<pre>' . print_r($_SESSION, true) . '</pre>';
?>
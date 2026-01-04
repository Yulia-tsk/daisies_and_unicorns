<?php

require 'vendor/autoload.php';

$client = new Google_Client();
$config = require 'config.php';

$client = new Google_Client();
$client->setClientId($config['GOOGLE_CLIENT_ID']);
$client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($config['GOOGLE_REDIRECT_URI']);

if( ! isset($_GET['code']) ) {
    $client->addScope('email');
    $client->addScope(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/spreadsheets']);
    
    $client->setAccessType('offline');
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    // You can store the token in a session or database for later use
    // For demonstration, we'll just display it
     echo '<h3>Access Token</h3>';
     echo '<pre>' . print_r($token, true) . '</pre>';
    $client->setAccessToken($token['access_token']);

    $oath2 = new Google_Service_Oauth2($client);
    $userInfo = $oath2->userinfo->get();
    var_dump($userInfo);
}   
// 2. Initialize the Google Sheets Service
    $service = new Google_Service_Sheets($client);

    // 3. Define your Spreadsheet ID and the data to store
    // Find the ID in your sheet URL: docs.google.com[SPREADSHEET_ID]/edit
    $spreadsheetId = '1lem9fBTKUvQmPvbJy5AfXWaI-EwljEaQRban96ThVuU'; 
    $range = 'Sheet1!A2'; // The sheet name and starting cell

    // 4. Prepare the data to be stored (must be a 2D array)
    $values = [
        ["User Authenticated", date('Y-m-d H:i:s')]
    ];
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);

    // 5. Append the data to the next available row
    $params = ['valueInputOption' => 'RAW'];
    try {
        $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
        echo "Data stored successfully! Rows updated: " . $result->getUpdates()->getUpdatedRows();
    } catch (Exception $e) {
        echo 'Error storing data: ' . $e->getMessage();
    }

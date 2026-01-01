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
    $client->addScope(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/documents']);
    $client->setAccessType('offline');
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    // You can store the token in a session or database for later use
    // For demonstration, we'll just display it
    // echo '<h3>Access Token</h3>';
    // echo '<pre>' . print_r($token, true) . '</pre>';
    $client->setAccessToken($token['access_token']);

    $oath2 = new Google_Service_Oauth2($client);
    $userInfo = $oath2->userinfo->get();
    var_dump($userInfo);
}   
<?php
require 'vendor/autoload.php';

$client = new Google_Client();
$config = require 'config.php';

$client = new Google_Client();
$client->setClientId($config['GOOGLE_CLIENT_ID']);
$client->setClientSecret($config['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($config['GOOGLE_REDIRECT_URI']);
$client->addScope('email');
$client->addScope(['https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/documents']);
$client->setAccessType('offline');
$authUrl = $client->createAuthUrl();
          

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Auth</title>
</head>
<body>
    <a href="<?= $authUrl ?>">Sign in with Google</a>
</body>
</html>
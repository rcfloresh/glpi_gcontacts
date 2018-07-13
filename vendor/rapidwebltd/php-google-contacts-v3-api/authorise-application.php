<?php

// After filling in the clientID, clientSecret and redirectUri (within 'config.json'), you should visit this page
// to get the authorisation URL.

// Note that the redirectUri value should point towards a hosted version of 'redirect_handler.php'.

require_once '../../../vendor/autoload.php';

use rapidweb\googlecontacts\helpers\GoogleHelper;

function registration($conf)
{
    $client = GoogleHelper::getClient($conf);

    $authUrl = GoogleHelper::getAuthUrl($client);

    header('Location: ' . $authUrl);
}

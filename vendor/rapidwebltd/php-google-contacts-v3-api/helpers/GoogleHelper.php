<?php

namespace rapidweb\googlecontacts\helpers;

abstract class GoogleHelper
{
    private static $_config;

    public static function initConfig(
        $clientID,
        $clientSecret,
        $redirectUri,
        $developerKey,
        $refreshToken
    ) {
        self::$_config = new \stdClass();
        self::$_config->clientID = $clientID;
        self::$_config->clientSecret = $clientSecret;
        self::$_config->redirectUri = $redirectUri;
        self::$_config->developerKey = $developerKey;
        self::$_config->refreshToken = $refreshToken;
    }

    private static function loadConfig($customConfig)
    {
        $mainconf = json_decode(file_get_contents(MAIN_SETTINGS));
        if($customConfig == null) {
            self::$_config->refreshToken = "";
            self::$_config->developerKey = "";
        }else{
            self::$_config->refreshToken = $customConfig->refreshToken;
            self::$_config->developerKey = $customConfig->developerKey;
        }
        self::$_config->clientID = $mainconf->server->clientID;
        self::$_config->clientSecret = $mainconf->server->clientSecret;
        self::$_config->redirectUri  = $mainconf->server->redirectUri;

        return self::$_config;
    }

    public static function getClient($customConfig)
    {
        $config = self::loadConfig($customConfig);

        $client = new \Google_Client();

        $client->setApplicationName('Rapid Web Google Contacts API');

        $client->setScopes(array(/*
        'https://apps-apis.google.com/a/feeds/groups/',*/
        'https://www.googleapis.com/auth/userinfo.email',
        /*'https://apps-apis.google.com/a/feeds/alias/',
        'https://apps-apis.google.com/a/feeds/user/',*/
        'https://www.google.com/m8/feeds/',
        /*'https://www.google.com/m8/feeds/user/',*/
        ));

        $client->setClientId($config->clientID);
        $client->setClientSecret($config->clientSecret);
        $client->setRedirectUri($config->redirectUri);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setDeveloperKey($config->developerKey);

        if (isset($config->refreshToken) && $config->refreshToken) {
            $client->refreshToken($config->refreshToken);
        }

        return $client;
    }

    public static function getAuthUrl(\Google_Client $client)
    {
        return $client->createAuthUrl();
    }

    public static function authenticate(\Google_Client $client, $code)
    {
        $client->authenticate($code);
    }

    public static function getAccessToken(\Google_Client $client)
    {
        return json_decode($client->getAccessToken());
    }
}

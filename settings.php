<?php

define("SETTINGS",GLPI_PLUGIN_DOC_DIR."/googlecontactapi");
define("MAIN_SETTINGS",GLPI_PLUGIN_DOC_DIR."/googlecontactapi/config.json");

if($CFG_GLPI["languages"][$_SESSION['glpilanguage']][3] == "ru")
    define("LANGUAGE", "ru");
elseif($CFG_GLPI["languages"][$_SESSION['glpilanguage']][3] == "fr")
    define("LANGUAGE", "fr");
else
    define("LANGUAGE", "en");

if(!is_dir(SETTINGS)){
    mkdir(SETTINGS);
}

if(!file_exists(MAIN_SETTINGS)){
    $server = new stdClass();
    $server->clientID = "";
    $server->clientSecret = "";
    $server->redirectUri = "";
    $main = new stdClass();
    $main->server = $server;
    file_put_contents(MAIN_SETTINGS, json_encode($main));
}


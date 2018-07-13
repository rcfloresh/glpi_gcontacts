<?php
include ("../../../inc/includes.php");

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('googlecontactsapi') || !$plugin->isActivated('googlecontactsapi')) {
   Html::displayNotFoundError();
}

//check for ACLs
if (PluginGooglecontactsapiContacts::canView()) {
   //View is granted: display the list.

   //Add page header
   Html::header(
      __('My example plugin', 'googlecontactsapi'),
      $_SERVER['PHP_SELF'],
      'assets',
      'PluginGooglecontactsapiContacts',
      'Contacts'
   );

   Search::show('PluginGooglecontactsapiContacts');

   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}
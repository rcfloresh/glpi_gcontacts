<?php
 
function plugin_version_googlecontactsapi() {
   return array('name'           => "Google Contact API",
                'version'        => '1.3.1',
                'author'         => 'Triton Studio',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://github.com/TritonStudio/glpi_gcontacts',
                'minGlpiVersion' => '9.1');
}

function plugin_googlecontactsapi_check_config() {
    return true;
}
 
function plugin_googlecontactsapi_check_prerequisites() {
    return true;
}
function plugin_init_googlecontactsapi() 
{
   global $PLUGIN_HOOKS,$CFG_GLPI,$DB;
   
   Plugin::registerClass('PluginGooglecontactsapiContacts');

   if (version_compare(GLPI_VERSION,'9.1','ge')) {
      if (class_exists('PluginGooglecontactsapiContacts')) {
         Link::registerTag(PluginGooglecontactsapiContacts::$tags);
      }
   }
   // Display a menu entry ?
   $PLUGIN_HOOKS['menu_toadd']['googlecontactsapi'] = array('plugins' => 'PluginGooglecontactsapiContacts');
   $PLUGIN_HOOKS["helpdesk_menu_entry"]['googlecontactsapi'] = true;
   $PLUGIN_HOOKS['item_update']['googlecontactsapi'] = [
   'User'    => 'plugin_googlecontactsapi_updateitem_called'
	];
	$PLUGIN_HOOKS['item_add']['googlecontactsapi'] = [
   'User'    => 'plugin_googlecontactsapi_additem_called'
	];
   $PLUGIN_HOOKS['csrf_compliant']['googlecontactsapi'] = true;
   $PLUGIN_HOOKS['add_css']['googlecontactsapi']        = 'callContainet.css';
}

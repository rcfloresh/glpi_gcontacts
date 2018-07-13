<?php
 
class PluginGooglecontactsapiContacts extends CommonDBTM{
	static $tags = '[Googlecontactsapi_ID]';
	
	static function canCreate() {
      return true;
   }

   static function canView() {
      return true;
   }
   
   static function getMenuName() {
      return __('Google Contact API');
   }
}
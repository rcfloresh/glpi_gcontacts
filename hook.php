<?php

require_once 'contact.php';
require_once 'settings.php';

function plugin_googlecontactsapi_getDropdown() {
   // Table => Name
   return array('PluginGooglecontactsapiDropdown' => __("Plugin Example Dropdown", 'googlecontactsapi'));
}

function plugin_googlecontactsapi_giveItem($type,$ID,$data,$num) {
   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi.glpi_plugin_googlecontactsapi_contacts.name" :
         $out = "<a href='".Toolbox::getItemTypeFormURL('PluginGooglecontactsapiContacts')."?id=".$data['id']."'>";
         $out .= $data[$num][0]['name'];
         if ($_SESSION["glpiis_ids_visible"] || empty($data[$num][0]['name'])) {
            $out .= " (".$data["id"].")";
         }
         $out .= "</a>";
         return $out;
   }
   return "";
}

function plugin_googlecontactsapi_updateitem_called (CommonDBTM $item) {
    if(!IsAuth())
        return;
    global $DB;
    $json = file_get_contents(MAIN_SETTINGS);
    $mainconf = json_decode($json);
    $company = "";
    $query = "select name FROM glpi_entities WHERE id = ".$item->fields['entities_id'].";";
    foreach ($DB->request($query) as $profile_rows) {
        $company = $profile_rows["name"];
    }
    foreach ($mainconf as $gmail => $userInfo) {
        if($gmail == "server") continue;
        UpdateContactForNote($item->fields['id'], array(
            'company' => $company,
            'note' => $item->fields['id'],
            'name' => $item->fields['firstname']." ".$item->fields['realname'],
            'email' => $item->input['_useremails'],
            'numbers' => array(
                $item->fields['phone'],
                $item->fields['phone2'],
                $item->fields['mobile']
            )
        ), $userInfo
        );
    }
}

function plugin_googlecontactsapi_additem_called (CommonDBTM $item) {
    if(!IsAuth())
        return;
    global $DB;
    $json = file_get_contents(MAIN_SETTINGS);
    $mainconf = json_decode($json);
    $company = "";
    $query = "select name FROM glpi_entities WHERE id = ".$item->fields['entities_id'].";";
    foreach ($DB->request($query) as $profile_rows) {
        $company = $profile_rows["name"];
    }
    foreach ($mainconf as $gmail => $userInfo) {
        if ($gmail == "server") continue;
        CreateNewContact(
            array(
                'company' => $company,
                'note' => $item->fields['id'],
                'name' => $item->fields['firstname'] . " " . $item->fields['realname'],
                'email' => $item->input['_useremails'],
                'numbers' => array(
                    $item->fields['phone'],
                    $item->fields['phone2'],
                    $item->fields['mobile']
                )
            ), $userInfo
        );
    }
}

function plugin_googlecontactsapi_addWhere($link, $nott, $type, $ID, $val, $searchtype) {
    $searchopt = &Search::getOptions($type);
    $table     = $searchopt[$ID]["table"];
    $field     = $searchopt[$ID]["field"];

    $SEARCH = Search::makeTextSearch($val,$nott);
    switch ($table.".".$field) {
        case "glpi.glpi_plugin_googlecontactsapi_contacts.caller" :
            return $link." `$table`.`$field` = '$val' ";
    }
    return "";
}

function plugin_googlecontactsapi_install() { return true; }
 
function plugin_googlecontactsapi_uninstall() { return true; }
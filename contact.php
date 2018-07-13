<?php
require_once 'vendor/autoload.php';

use rapidweb\googlecontacts\factories\ContactFactory;

function CreateNewContact($contact, $user){
	if(!IsAuth())
		return;
	ContactFactory::create($contact, $user);
}

function UpdateContactForNote($search, $update_contact, $user){
	if(!IsAuth())
		return;
	$contacts = ContactFactory::getAll($user);

	foreach($contacts as $contact){
		if(empty($contact->content))
			continue;
		
		if($contact->content == $search){
			ContactFactory::submitUpdates($contact, $update_contact, $user);
			return;
		}
	}

	CreateNewContact($update_contact, $user);
}

function IsAuth(){
	$json = file_get_contents(MAIN_SETTINGS);
	$conf = json_decode($json)->server;
	if(isset($conf->clientID))
        if(isset($conf->clientSecret))
            if(isset($conf->redirectUri))
		        return true;
	return false;
}
function GoogleAccountsExists(){
    $json = file_get_contents(MAIN_SETTINGS);
    $conf = json_decode($json);
    if(count((array)$conf) > 1)
        return true;
    return false;
}
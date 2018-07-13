<?php
require_once '../contact.php';
require_once '../vendor/rapidwebltd/php-google-contacts-v3-api/authorise-application.php';
include ("../../../inc/includes.php");
require_once '../settings.php';
require_once '../locale/'.LANGUAGE.'.php';
use rapidweb\googlecontacts\factories\ContactFactory;
use rapidweb\googlecontacts\helpers\GoogleHelper;

$local = GetLanguage();

if(isset($_GET['code']) && !empty($_GET['code'])){
    $code = $_GET['code'];
    $client = GoogleHelper::getClient(null);
    GoogleHelper::authenticate($client, $code);
    $accessToken = GoogleHelper::getAccessToken($client);

    if (!isset($accessToken->refresh_token)) {
        echo 'Google did not respond with a refresh token. You can still use the Google Contacts API, but you may to re-authorise your application in the future. ';
    } else {
        $json = file_get_contents("https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=" . $accessToken->access_token);
        $gmail = json_decode($json)->email;
        $conf = new stdClass();
        $conf->refreshToken = $accessToken->refresh_token;
        $conf->developerKey = "";
        $temp = json_decode(file_get_contents(MAIN_SETTINGS));
        $temp->{$gmail} = $conf;
        file_put_contents(MAIN_SETTINGS,json_encode($temp));
    }
    header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?'));
}

$json = file_get_contents(MAIN_SETTINGS);
$mainconf = json_decode($json);

if(isset($_GET['action']) && $_GET['action'] === "add"){
	$conf = new stdClass();
	$conf->developerKey = "";
    $conf->refreshToken = "";
	registration($conf);
}

if(isset($_GET['action']) && $_GET['action'] === "delete"){
    $gmail = $_GET['gmail'];
    unset($mainconf->{$gmail});
    $str = json_encode($mainconf);
    file_put_contents(MAIN_SETTINGS, $str);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?'));
}

if(isset($_GET['action']) && $_GET['action'] == "redactor"){
    $conf = new stdClass();
    $conf->developerKey = $_GET['developerKey'];
    $conf->refreshToken = $_GET['refreshToken'];
    $mainconf->{$_GET['gmail']} = $conf;
    $str = json_encode($mainconf);
    file_put_contents(MAIN_SETTINGS, $str);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?'));
}

if(isset($_GET['action']) && $_GET['action'] === "server"){
    $conf = new stdClass();
    $conf->clientID = $_GET['clientID'];
    $conf->clientSecret = $_GET['clientSecret'];
    $conf->redirectUri = $_GET['redirectUri'];
    $mainconf->server = $conf;
    $str = json_encode($mainconf);
    file_put_contents(MAIN_SETTINGS, $str);
    header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?'));
}

if(isset($_GET['action']) && $_GET['action'] === "sync"){
	global $DB;
	$emails = array();
	$query = "select users_id as id, email from glpi_useremails;";
	foreach ($DB->request($query) as $row){
		$id = $row['id'];
		if (isset($emails[$id]))
			$emails[$id][] = $row['email'];
		else
			$emails[$id] = array($row['email']);
	}
	$query = "select u.id, u.realname as surname, u.firstname, u.phone as phone1, u.mobile as phone2, u.phone2 as phone3, e.completename as companyname 
	from glpi_users u left join  glpi_entities e on u.entities_id = e.id
	WHERE (u.phone <> '') OR (u.mobile <> '') OR (u.phone2 <> '');";
    foreach ($mainconf as $gmail => $userInfo) {
        if($gmail == "server") continue;
        $contacts = ContactFactory::getAll($userInfo);

        foreach ($DB->request($query) as $profile_rows) {
            foreach ($contacts as $contact) {
                if ($contact->content == $profile_rows['id'])
                    goto next;
            }
            $id = $profile_rows['id'];
            $email = $emails[$id];
            $name = $profile_rows['surname'];
            $firstname = $profile_rows['firstname'];
            $companyname = $profile_rows['companyname'];
            CreateNewContact(
                array(
                    'company' => $companyname,
                    'note' => $id,
                    'name' => $firstname . " " . $name,
                    'email' => $email,
                    'numbers' => array(
                        $profile_rows['phone1'],
                        $profile_rows['phone2'],
                        $profile_rows['phone3']
                    )
                ), $userInfo
            );
            next:
        }
    }
    header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?'));
}

// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('googlecontactsapi') || !$plugin->isActivated('googlecontactsapi')) {
   Html::displayNotFoundError();
}

if ($_SESSION["glpiactiveprofile"]["interface"] == "central") {
   Html::header("Google Contacts Api", $_SERVER['PHP_SELF'],"plugins","plugingooglecontactsapicontacts","");
} else {
   Html::helpHeader("Google Contacts Api", $_SERVER['PHP_SELF']);
}

$itemtype='PluginGooglecontactsapiContacts';
$css=Html::css('callContainet.css');

?>


    <div>
        <div id="main"></div>
        <div align="center" id="loader">
            <img src="spinner-loader.gif"><br><h1 style="color: white"><?php echo $local['sync_now'] ?></h1>
        </div>
        <script>
            function loading() {
                document.getElementById('main').style.display = 'block';
                document.getElementById('loader').style.display = 'block';
            }

            function deleteforgmail(gmail) {
                $("#action_" + gmail).attr("value","delete");
                $("#"+gmail).submit();
            }

            function redactorforgmail(gmail) {
                $("#action_" + gmail).attr("value","redactor");
                $("#"+gmail).submit();
            }

            function addnewuser() {
                $("#action_other").attr("value","add");
                $("#other").submit();
            }

            function sync() {
                loading();
                $("#action_other").attr("value","sync");
                $("#other").submit();
            }
            
            function server() {
                $("#action_server").attr("value","server");
                $("#server").submit();
            }
        </script>
        <table class='tab_cadre_fixe'>
            <tbody>
			<tr>
				<td>
                    <div style='text-align: center; font-size:20px;'>
					    <?php echo $local['main_label'] ?> <span style='color:blue;'>G</span><span style='color:red;'>o</span><span style='color:yellow;'>o</span><span style='color:blue;'>g</span><span style='color:green;'>l</span><span style='color:red;'>e</span> Contact API v1.3
				    </div>
                </td>
			</tr>
            <?php foreach ($mainconf as $gmail => $userInfo){ ?>
                <?php if($gmail == "server") continue; ?>
            <tr class='tab_bg_1'>
                <td>
                    <div style='text-align: center;'>
                        <form id="<?php echo strtok($gmail,'@') ?>" method='get'
                              action='<?php echo strtok($_SERVER["REQUEST_URI"],'?')?>'>
                        <details>
                            <summary><?php echo $local['account_settings'] ?> - <?php echo $gmail ?></summary><br>
                            <table class='tab_format'>
                                <tbody style='text-align: left;'>
                                    <?php foreach($userInfo as $key => $value){ ?>
                                        <tr class='normalcriteria headerRow'>
                                            <td style='width:50px'><span><?php echo $key ?></span></td>
                                            <td><input autocomplete="off" style='width: 90%;' value='<?php echo $value?>' name='<?php echo $key?>' id='<?php echo $key?>'></td>
                                        </tr>
                                    <?php } ?>
                                    <input type="hidden" value='<?php echo $gmail?>' name='gmail'>
                                    <input id="action_<?php echo strtok($gmail,'@') ?>" type="hidden" value='' name='action'>
                                </tbody>
                            </table><br>
                            <div align="center">
                                <a class="custom_btn" onclick="redactorforgmail('<?php echo strtok($gmail,'@') ?>');"><?php echo $local['update_accaunt'] ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="custom_btn" onclick="deleteforgmail('<?php echo strtok($gmail,'@') ?>');"><?php echo $local['delete_accaunt'] ?></a>
                            </div>
                        </details>
                        </form>
                    </div>
                </td>
            </tr>
            <?php }?>
            <tr>
                <td>
                    <div style='text-align: center;'>
                        <form id="server" method='get'
                              action='<?php echo strtok($_SERVER["REQUEST_URI"],'?')?>'>
                        <details>
                            <summary><?php echo $local['sevres_settings'] ?></summary><br>
                            <table class='tab_format'>
                                <tbody style='text-align: left;'>
                                <?php foreach($mainconf->server as $key => $value){ ?>
                                    <tr class='normalcriteria headerRow'>
                                        <td style='width:50px'><span><?php echo $key?></span></td>
                                        <td><input style='width: 90%;' value='<?php echo $value?>' name='<?php echo $key?>' id='<?php echo $key?>'></td>
                                    </tr>
                                <?php } ?>
                                <input id="action_server" type="hidden" value='' name='action'/>
                                </tbody>
                            </table><br>
                            <div align="center">
                                <a class="custom_btn" target="_blank" href="https://console.developers.google.com/"><?php echo $local['get_data'] ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="custom_btn" onclick="server()"><?php echo $local['save'] ?></a>
                            </div>
                        </details>
                        </form>
                    </div>
                </td>
            </tr>
			<tr>
				<td style='text-align: center;'><div>
                    <form id="other" method='get'
                          action='<?php echo strtok($_SERVER["REQUEST_URI"],'?')?>'>
                        <input id="action_other" type="hidden" value='' name='action'/>

                        <?php if(IsAuth()) {?>
                            <a class="custom_btn" onclick="addnewuser()"><?php echo $local['new_accaunt'] ?></a>
                            <?php if(GoogleAccountsExists()) {?>
                            &nbsp;&nbsp;&nbsp;&nbsp;<a class="custom_btn" onclick="sync()"><?php echo $local['sync'] ?></a>
                            <?php }?>
                        <?php } else {?>
                            <span><?php echo $local['error'] ?></span>
                        <?php }?>
                    </form>
				</div></td>
			</tr>
            </tbody>
        </table>
    </div>

<?php Html::footer(); ?>

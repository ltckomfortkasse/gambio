<?php
/**
 * Komfortkasse
 * Installer
 * @version 1.4.3.1-gambio (2.6.0.1)
 * 
 * use these SQL statements to delete the configuration entries in order to re-install the plugin:
 * delete from configuration_group where configuration_group_title='Komfortkasse';
 * delete from configuration where configuration_key like 'KOMFORTKASSE%';
 * delete from language_section_phrases where phrase_name like 'KOMFORTKASSE%' or phrase_value='Komfortkasse';
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
?>
<html>
<head>
<title>Komfortkasse Installer</title>
</head>
<body>
	<font face="Verdana,Arial,Helvetica"> <img
		src="images/komfortkasse_eu.png" border="0"><br />
		<h3>Auto Installer</h3>
<?php $steps = 9; $step=0; ?>
Note: if the installer exits before step <?php echo $steps; ?> without an error message, enable error reporting in this install.php file. (Uncomment lines 13 and 14.)

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Including files...

<?php
require_once ('../../includes/configure.php');
require_once ('../../includes/application_top_callback.php');
require_once ('Komfortkasse_Config.php');
?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Determining Languages...

<?php
require_once (DIR_WS_CLASSES . 'language.php');
$lng = new language();
echo "installed: ";
foreach ($lng->catalog_languages as $iso => $l) {
    echo $iso . ' ';
}
echo ' (main: ' . $lng->language ['code'] . ')';
?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Determining Configuration Group ID...

<?php
$config_group_q = xtc_db_query("SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " where configuration_group_title='Komfortkasse'");
$config_group_a = xtc_db_fetch_array($config_group_q);
$config_group_id = $config_group_a ['configuration_group_id'];
if ($config_group_id) {
    echo 'Configuration group ID for "Komfortkasse" already exists, switching to update mode... ';
    $update = true;
}

if (!$update) {
    $config_group_q1 = xtc_db_query("SELECT max(configuration_group_id) as maxid FROM " . TABLE_CONFIGURATION_GROUP);
    $config_group_a1 = xtc_db_fetch_array($config_group_q1);
    $config_group_id1 = $config_group_a1 ['maxid'] + 1;
    
    $config_group_q2 = xtc_db_query("SELECT max(configuration_group_id) as maxid FROM " . TABLE_CONFIGURATION);
    $config_group_a2 = xtc_db_fetch_array($config_group_q2);
    $config_group_id2 = $config_group_a2 ['maxid'] + 1;
    
    $config_group_id = max($config_group_id1, $config_group_id2);
}

echo $config_group_id;
?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Checking Language additions...

<?php
$file = DIR_WS_LANGUAGES . $lng->language ['directory'] . '/admin/configuration.php';
if (file_exists($file)) {
    
    // Gambio 2.0
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strstr($line, Komfortkasse_Config::status_cancelled_cod) !== FALSE) {
            $found = 1;
            break;
        }
    }
    if (!$found) {
        echo "<br/>ERROR: The configuration translation has not been added to your language files (e.g. " . $file . "). Please add the lines to the language file(s) (you can find them in the /callback/komfortkasse/lang folder) and start the installer again.";
        echo "<br/>Additionally, add the folowing line:<br/><b>define('BOX_CONFIGURATION_" . $config_group_id . "','Komfortkasse');</b>";
        die();
    } else {
        echo "ok";
    }
} else {
    
    // Gambio 2.1+
    

    echo "updating phrases for languages: ";
    
    foreach ($lng->catalog_languages as $iso => $l) {
        echo $iso . ' ';
        
        $lang_table_q = xtc_db_query("SHOW TABLES LIKE 'language_phrases_edited'");
        $lang_table_a = xtc_db_fetch_array($lang_table_q);
        $version = empty($lang_table_a) ? '2.1' : '2.3';
        
        if ($version == '2.1') {
            // Gambio 2.1-2.2
            $lang_section_q = xtc_db_query("SELECT language_section_id FROM language_sections where section_name like '%/admin/configuration.php' and language_id=" . $l ['id']);
            $lang_section_a = xtc_db_fetch_array($lang_section_q);
            $lang_section = $lang_section_a ['language_section_id'];
        } else if ($version == '2.3') {
            // Gambio 2.3+
            $lang_section = $l ['id'];
        }
        
        if ($lang_section) {
            
            if ($l ['code'] == 'de') {
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_EXPORT_TITLE', 'Export Bestellungen', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_EXPORT_DESC', 'Export von Bestellungen aktiv', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_UPDATE_TITLE', 'Update Bestellungen', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_UPDATE_DESC', 'Update von Bestellungen aktiv', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_TITLE', 'Zahlungsart Codes fuer Vorkasse', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_DESC', 'Alle Zahlungsart Codes die bei Vorkasse-Bestellungen exportiert werden sollen. Beispiel: moneyorder,eustandardtransfer', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_TITLE', 'Vorkasse: Status-Codes offen', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_DESC', 'Bestellstatus-Codes die fuer den Export beruecksichtigt werden sollen (offene Bestellungen) (kommagetrennt)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_TITLE', 'Vorkasse: Status Zahlung erhalten', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_DESC', 'Bestellstatus, auf den Bestellungen gesetzt werden sollen zu denen eine Vorkasse Zahlung eingegangen ist.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_TITLE', 'Vorkasse: Status storniert', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_DESC', 'Bestellstatus, auf den Vorkasse-Bestellungen gesetzt werden sollen die storniert wurden.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_INVOICE_TITLE', 'Zahlungsart Codes fuer Rechnung', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_INVOICE_DESC', 'Alle Zahlungsart Codes die bei Zahlung auf Rechnung exportiert werden sollen. Beispiel: invoice', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_INVOICE_TITLE', 'Rechnung: Status-Codes offen', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_INVOICE_DESC', 'Bestellstatus-Codes die fuer den Export beruecksichtigt werden sollen (offene Rechnungen) (kommagetrennt)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_INVOICE_TITLE', 'Rechnung: Status Zahlung erhalten', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_INVOICE_DESC', 'Bestellstatus, auf den Bestellungen gesetzt werden sollen zu denen eine Zahlung zu einer Rechnung eingegangen ist.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_INVOICE_TITLE', 'Rechnung: Status keine Zahlung/Inkasso', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_INVOICE_DESC', 'Bestellstatus, auf den Bestellungen bei Zahlung auf Rechnung gesetzt werden sollen bei denen keine Zahlung eingeht.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_COD_TITLE', 'Zahlungsart Codes fuer Nachnahme', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_COD_DESC', 'Alle Zahlungsart Codes die bei Nachname exportiert werden sollen. Beispiel: cod', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_COD_TITLE', 'Nachname: Status-Codes versendet', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_COD_DESC', 'Bestellstatus-Codes die fuer den Export beruecksichtigt werden sollen (versendete Nachnahme-Bestellungen) (kommagetrennt)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_COD_TITLE', 'Nachnahme: Status Zahlung erhalten', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_COD_DESC', 'Bestellstatus, auf den Nachnahme-Bestellungen gesetzt werden sollen zu denen eine Zahlung eingegangen ist.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_COD_TITLE', 'Nachnahme: Status Zahlung ungeklaert', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_COD_DESC', 'Bestellstatus, auf den Nachnahme-Bestellungen gesetzt werden sollen bei denen keine Zahlung eingeht.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ENCRYPTION_TITLE', 'Verschluesselung', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ENCRYPTION_DESC', 'Auswahl der Verschluesselungstechnik. Nicht aendern! Wird automatisch von Komfortkasse gesetzt.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACCESSCODE_TITLE', 'Zugriffscode (verschluesselt)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACCESSCODE_DESC', 'Verschluesselter Zugriffscode. Nicht aendern! Wird automatisch von Komfortkasse gesetzt.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_APIKEY_TITLE', 'API Schluessel', $version);
                insert_language($lang_section, 'KOMFORTKASSE_APIKEY_DESC', 'Schluessel fuer den Zugriff auf die Komfortkasse API. Nicht aendern! Wird automatisch von Komfortkasse gesetzt.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PUBLICKEY_TITLE', 'Oeffentlicher Schluessel', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PUBLICKEY_DESC', 'Schluessel zur Verschluesselung der Daten die an Komfortkasse gesendet werden. Nicht aendern! Wird automatisch von Komfortkasse gesetzt.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PRIVATEKEY_TITLE', 'Privater Schluessel', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PRIVATEKEY_DESC', 'Schluessel zur Entschluesselung der Daten die von Komfortkasse empfangen werden. Nicht aendern! Wird automatisch von Komfortkasse gesetzt.', $version);
                insert_language($lang_section, 'BOX_CONFIGURATION_' . $config_group_id, 'Komfortkasse', $version);
            } else {
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_EXPORT_TITLE', 'Export orders', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_EXPORT_DESC', 'Activate export of orders', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_UPDATE_TITLE', 'Update orders', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACTIVATE_UPDATE_DESC', 'Activate update of orders', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_TITLE', 'Prepayment: Payment type codes', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_DESC', 'All payment type codes that should be exported for prepayment orders. Example: moneyorder,eustandardtransfer', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_TITLE', 'Prepayment: State open', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_DESC', 'Order states that should be exported (open orders) (comma-separated)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_TITLE', 'Prepayment: State paid', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_DESC', 'Order state that should be set when prepayment has been received.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_TITLE', 'Prepayment: State cancelled', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_DESC', 'Order state that should be set when a prepayment order has been cancelled.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_INVOICE_TITLE', 'Invoice: Payment type codes', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_INVOICE_DESC', 'All payment type codes that should be exported for invoice orders. Example: invoice', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_INVOICE_TITLE', 'Invoice: State open', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_INVOICE_DESC', 'Order states that should be exported (open invoices) (comma-separated)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_INVOICE_TITLE', 'Invoice: State paid', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_INVOICE_DESC', 'Order state that should be set when an invoice has been paid.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_INVOICE_TITLE', 'Invoice: State no payment/debt collection', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_INVOICE_DESC', 'Order state that should be set when an invoice was not paid.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_COD_TITLE', 'COD: Payment type codes', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PAYMENT_CODES_COD_DESC', 'All payment type codes that should be exported for COD (cash on delivery) orders. Example: cod', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_COD_TITLE', 'COD: State dispatched', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_OPEN_COD_DESC', 'Order states that should be exported (dispatched COD parcel) (comma-separated)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_COD_TITLE', 'COD: State paid', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_PAID_COD_DESC', 'Order state that should be set when a COD order has been paid.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_COD_TITLE', 'COD: State payment unresolved', $version);
                insert_language($lang_section, 'KOMFORTKASSE_STATUS_CANCELLED_COD_DESC', 'Order state that should be set when a COD order was not paid.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ENCRYPTION_TITLE', 'Encryption', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ENCRYPTION_DESC', 'Encryption technology. Do not change! Is set automatically by komfortkasse.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACCESSCODE_TITLE', 'Access code (encrypted)', $version);
                insert_language($lang_section, 'KOMFORTKASSE_ACCESSCODE_DESC', 'Encrypted access code. Do not change! Is set automatically by komfortkasse.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_APIKEY_TITLE', 'API Key', $version);
                insert_language($lang_section, 'KOMFORTKASSE_APIKEY_DESC', 'Key for accessing the API. Do not change! Is set automatically by komfortkasse.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PUBLICKEY_TITLE', 'Public key', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PUBLICKEY_DESC', 'Key for encrypting data that is sent to komfortkasse. Do not change! Is set automatically by komfortkasse.', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PRIVATEKEY_TITLE', 'Private key', $version);
                insert_language($lang_section, 'KOMFORTKASSE_PRIVATEKEY_DESC', 'Key for decrypting data that is received from komfortkasse. Do not change! Is set automatically by komfortkasse.', $version);
                insert_language($lang_section, 'BOX_CONFIGURATION_' . $config_group_id, 'Komfortkasse', $version);
            }
        }
    }
    
    echo " - ok";
}


?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Checking Admin Menu...
<?php
$found = 0;
$file = DIR_FS_DOCUMENT_ROOT . 'system/conf/admin_menu/menu_komfortkasse.xml';
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ((strstr($line, 'Komfortkasse') !== FALSE) && (strstr($line, 'gID=' . $config_group_id) !== FALSE)) {
            $found = 1;
            break;
        }
    }
}

if (!$found) {
    echo "creating menu xml file... ";
    if (file_exists($file)) {
        unlink($file);
    }
    copy('menu_komfortkasse_template.xml', $file);
    $content = file_get_contents($file);
    $content = str_replace('{GID}', $config_group_id, $content);
    file_put_contents($file, $content);
    echo "ok";
} else {
    echo "ok";
}

?>
  
<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Creating Configuration Group...
<?php
if (!$update) {
    $sql_data_array = array ('configuration_group_id' => $config_group_id,'configuration_group_title' => 'Komfortkasse','configuration_group_description' => 'Komfortkasse Konfiguration','sort_order' => $config_group_id,'visible' => 1 
    );
    xtc_db_perform(TABLE_CONFIGURATION_GROUP, $sql_data_array);
} else {
    echo 'skipping because of update mode';
}
?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Creating Configuration ...

<?php
$sort_order = 1;
include_once 'install_defaults.php';

insert_configuration($config_group_id, Komfortkasse_Config::activate_export, KOMFORTKASSE_ACTIVATE_EXPORT_DEFAULT, null, 'xtc_cfg_select_option(array(\'true\', \'false\'),', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::activate_update, KOMFORTKASSE_ACTIVATE_UPDATE_DEFAULT, null, 'xtc_cfg_select_option(array(\'true\', \'false\'),', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::payment_methods, KOMFORTKASSE_PAYMENT_METHODS_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_open, KOMFORTKASSE_STATUS_OPEN_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_paid, KOMFORTKASSE_STATUS_PAID_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_cancelled, KOMFORTKASSE_STATUS_CANCELLED_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::payment_methods_invoice, KOMFORTKASSE_PAYMENT_CODES_INVOICE_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_open_invoice, KOMFORTKASSE_STATUS_OPEN_INVOICE_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_paid_invoice, KOMFORTKASSE_STATUS_PAID_INVOICE_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_cancelled_invoice, KOMFORTKASSE_STATUS_CANCELLED_INVOICE_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::payment_methods_cod, KOMFORTKASSE_PAYMENT_CODES_COD_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_open_cod, KOMFORTKASSE_STATUS_OPEN_COD_DEFAULT, null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_paid_cod, KOMFORTKASSE_STATUS_PAID_COD_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::status_cancelled_cod, KOMFORTKASSE_STATUS_CANCELLED_COD_DEFAULT, 'xtc_get_order_status_name', 'xtc_cfg_pull_down_order_statuses(', $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::encryption, '', null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::accesscode, '', null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::publickey, '', null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::privatekey, '', null, null, $sort_order);
$sort_order++;

insert_configuration($config_group_id, Komfortkasse_Config::apikey, '', null, null, $sort_order);
$sort_order++;

?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
Modifying .htaccess file...
<?php
$ok = 0;
if (rename('.htaccess', '_htaccess.beforeinstall') === TRUE) {
    if (rename('_htaccess.afterinstall', '.htaccess') === TRUE) {
        $ok = 1;
    }
}
if ($ok) {
    echo "ok";
} else {
    echo "Important: your .htaccess file could not be changed. For improved security, please change your .htaccess file so that the install.php script cannot be executed, or rename install.php.";
}

?>

<br /> <br /> <b><?php echo ++$step;?>/<?php echo $steps;?></b>
		Finished. Please <b><a
			href="<?php echo (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG?>admin/clear_cache.php"
			target="_new">empty the modules cache</a></b> and <a
		href="<?php echo (ENABLE_SSL ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG?>admin/configuration.php?gID=<?php echo $config_group_id; ?>"
		target="_new">check the configuration</a>. </a><br /> (If you cannot
		access this link, please login to your admin panel and open the
		Komfortkasse configuration from the menu - should be the last menu
		entry.)<br /> <br /> <br />
		<h3>Instant order transmission</h3> New orders will be read
		periodically from your online shop. Additionally, you can activate <b>instant
			order transmission</b>, which will transmit any new order
		immediately. This way, your customer will receive payment information
		immediately. We encourage you to activate instant order transmission.
		In order to activate instant order transmission, edit the following
		files:<br /> <br /> <b>/admin/orders.php</b>, around line 228 <b>and</b>
		339: <pre>
xtc_db_query("insert into ".TABLE_ORDERS_STATUS_HISTORY." ...
<b>
// BEGIN Komfortkasse
include_once '../callback/komfortkasse/Komfortkasse.php';
$k = new Komfortkasse();
$k->notifyorder($oID);
// END Komfortkasse
</b>
$order_updated = true;
</pre> <br /> <b>/includes/modules/payment/eustandardtransfer.php</b>,
		or any other payment module that will be used with Komfortkasse (e.g.
		banktransfer, moneyorder), in function after_process, at the end of
		the function: <pre>
<b>
// BEGIN Komfortkasse
include_once './callback/komfortkasse/Komfortkasse.php';
$k = new Komfortkasse();
$k->notifyorder($insert_id);
// END Komfortkasse
</b>
</pre> <br /> <b>/lang/[your
			languages]/modules/payment/eustandardtransfer.php</b>, or any other
		payment module that will be used with Komfortkasse (e.g. banktransfer,
		moneyorder), change the MODULE_PAYMENT_[...]_TEXT_DESCRIPTION constant
		(e.g. MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION): <pre>
<b>
// german
define('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION', '&lt;br /&gt;Sie erhalten nach Bestellannahme die Kontodaten in einer gesonderten E-Mail.');

// english
define('MODULE_PAYMENT_EUTRANSFER_TEXT_DESCRIPTION', '&lt;br /&gt;After your order is confirmed, you will receive payment details in a separate e-mail.');
</b>
</pre>

	</font>
</body>
</html>

<?php


function insert_language($id, $phrase_name, $phrase_value, $version)
{
    if ($version == '2.1') {
        // prüfen ob wert besteht -> wenn ja, update, sonst insert
        $where = "phrase_name like '" . $phrase_name . "' and language_section_id=" . $id;
        $check_s = xtc_db_query("SELECT language_section_id FROM language_section_phrases where " . $where);
        $check_a = xtc_db_fetch_array($check_s);
        
        if ($check_a ['language_section_id']) {
            $sql_data_array = array ('phrase_value' => $phrase_value 
            );
            xtc_db_perform('language_section_phrases', $sql_data_array, 'update', $where);
        } else {
            $sql_data_array = array ('language_section_id' => $id,'phrase_name' => $phrase_name,'phrase_value' => $phrase_value 
            );
            xtc_db_perform('language_section_phrases', $sql_data_array);
        }
    } else if ($version == '2.3') {
        // prüfen ob wert besteht -> wenn ja, update, sonst insert
        $where = "phrase_name like '" . $phrase_name . "' and language_id=" . $id . " and section_name='configuration'";
        $check_s = xtc_db_query("SELECT language_id FROM language_phrases_edited where " . $where);
        $check_a = xtc_db_fetch_array($check_s);
        
        if ($check_a ['language_id']) {
            $sql_data_array = array ('phrase_text' => $phrase_value,'date_modified' => date("Y-m-d H:i:s") 
            );
            xtc_db_perform('language_phrases_edited', $sql_data_array, 'update', $where);
        } else {
            $sql_data_array = array ('language_id' => $id,'section_name' => 'configuration','phrase_name' => $phrase_name,'phrase_text' => $phrase_value,'date_modified' => date("Y-m-d H:i:s") 
            );
            xtc_db_perform('language_phrases_edited', $sql_data_array);
        }
    }

}


function insert_configuration($config_group_id, $config_key, $config_value, $use_function, $set_function, $sort_order)
{
    // prüfen ob wert besteht -> wenn ja, update, sonst insert
    $where = "configuration_group_id=" . $config_group_id . " and configuration_key='" . $config_key . "'";
    $check_s = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " where " . $where);
    $check_a = xtc_db_fetch_array($check_s);
    
    if ($check_a ['configuration_id']) {
        $sql_data_array = array ('use_function' => $use_function,'set_function' => $set_function,'sort_order' => $sort_order 
        );
        xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array, 'update', $where);
    } else {
        $sql_data_array = array ('configuration_group_id' => $config_group_id,'configuration_key' => $config_key,'configuration_value' => $config_value,'use_function' => $use_function,'set_function' => $set_function,'sort_order' => $sort_order 
        );
        xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
    }

}

?>


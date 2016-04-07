<?php

/** 
 * Komfortkasse
 * Config Class
 * 
 * @version 1.4.0.3-gambio
 */
class Komfortkasse_Config {
	const activate_export = 'KOMFORTKASSE_ACTIVATE_EXPORT';
	const activate_update = 'KOMFORTKASSE_ACTIVATE_UPDATE';
	const payment_methods = 'KOMFORTKASSE_PAYMENT_CODES';
	const status_open = 'KOMFORTKASSE_STATUS_OPEN';
	const status_paid = 'KOMFORTKASSE_STATUS_PAID';
	const status_cancelled = 'KOMFORTKASSE_STATUS_CANCELLED';
	const encryption = 'KOMFORTKASSE_ENCRYPTION';
	const accesscode = 'KOMFORTKASSE_ACCESSCODE';
	const apikey = 'KOMFORTKASSE_APIKEY';
	const publickey = 'KOMFORTKASSE_PUBLICKEY';
	const privatekey = 'KOMFORTKASSE_PRIVATEKEY';
	const payment_methods_invoice = 'KOMFORTKASSE_PAYMENT_CODES_INVOICE';
	const status_open_invoice = 'KOMFORTKASSE_STATUS_OPEN_INVOICE';
	const status_paid_invoice = 'KOMFORTKASSE_STATUS_PAID_INVOICE';
	const status_cancelled_invoice = 'KOMFORTKASSE_STATUS_CANCELLED_INVOICE';
	const payment_methods_cod = 'KOMFORTKASSE_PAYMENT_CODES_COD';
	const status_open_cod = 'KOMFORTKASSE_STATUS_OPEN_COD';
	const status_paid_cod = 'KOMFORTKASSE_STATUS_PAID_COD';
	const status_cancelled_cod = 'KOMFORTKASSE_STATUS_CANCELLED_COD';
	
	// changing constants at runtime is necessary for init, therefore save them in cache
	private static $cache = array ();
	
	public static function setConfig($constant_key, $value) {
		
		$sql_data_array = array (
				'configuration_value' => $value 
		);
		xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array, 'update', "configuration_key='" . $constant_key . "'");
		// cache leeren
		unset(self::$cache[$constant_key]);
		// nicht cachen, erst beim get() -> so kann man feststellen ob der wert erfolgreich gespeichert werden konnte (und nicht z.B. zu lang ist) self::$cache [$constant_key] = $value;
	}
	public static function getConfig($constant_key, $order=null) {
		if (!array_key_exists($constant_key, self::$cache)) {
			$config_q = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " where configuration_key='" . $constant_key . "'");
			$config_a = xtc_db_fetch_array($config_q);
			$config = $config_a ['configuration_value'];
			self::$cache [$constant_key] = $config;
		}
		
		return self::$cache [$constant_key];
	}
	public static function getRequestParameter($key) {
		if ($_POST [$key])
			return urldecode($_POST [$key]);
		else
			return urldecode($_GET [$key]);
	}
	
	public static function getVersion() {
		include(DIR_FS_CATALOG . 'release_info.php');
		return $gx_version;
	}
}
?>
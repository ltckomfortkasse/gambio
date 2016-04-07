<?php

/**
 * Komfortkasse Order Class
 * in KK, an Order is an Array providing the following members:
 * number, date, email, customer_number, payment_method, amount, currency_code, exchange_rate, language_code
 * status: data type according to the shop system
 * delivery_ and billing_: _firstname, _lastname, _company, _street, _postcode, _city, _countrycode
 * products: an Array of item numbers
 * @version 1.4.0.3-gambio
 */

class Komfortkasse_Order
{
    
    // return all order numbers that are "open" and relevant for transfer to kk
    public static function getOpenIDs()
    {
        $ret = array ();
        
        if (Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open) != '' && Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods) != '') {
            $sql = "select orders_id from " . TABLE_ORDERS . " where orders_status in (" . Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open) . ") and ( ";
            $paycodes = preg_split('/,/', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods));
            for($i = 0; $i < count($paycodes); $i++) {
                $sql .= " payment_method like '" . $paycodes [$i] . "' ";
                if ($i < count($paycodes) - 1) {
                    $sql .= " or ";
                }
            }
            $sql .= " )";
            $orders_q = xtc_db_query($sql);
            
            while ( $orders_a = xtc_db_fetch_array($orders_q) ) {
                $ret [] = $orders_a ['orders_id'];
            }
        }
        
        if (Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_invoice) != '' && Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_invoice) != '') {
            $sql = "select orders_id from " . TABLE_ORDERS . " where orders_status in (" . Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_invoice) . ") and ( ";
            $paycodes = preg_split('/,/', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_invoice));
            for($i = 0; $i < count($paycodes); $i++) {
                $sql .= " payment_method like '" . $paycodes [$i] . "' ";
                if ($i < count($paycodes) - 1) {
                    $sql .= " or ";
                }
            }
            $sql .= " )";
            $orders_q = xtc_db_query($sql);
            
            while ( $orders_a = xtc_db_fetch_array($orders_q) ) {
                $ret [] = $orders_a ['orders_id'];
            }
        }
        
        if (Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_cod) != '' && Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_cod) != '') {
            $sql = "select orders_id from " . TABLE_ORDERS . " where orders_status in (" . Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open_cod) . ") and ( ";
            $paycodes = preg_split('/,/', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods_cod));
            for($i = 0; $i < count($paycodes); $i++) {
                $sql .= " payment_method like '" . $paycodes [$i] . "' ";
                if ($i < count($paycodes) - 1) {
                    $sql .= " or ";
                }
            }
            $sql .= " )";
            $orders_q = xtc_db_query($sql);
            
            while ( $orders_a = xtc_db_fetch_array($orders_q) ) {
                $ret [] = $orders_a ['orders_id'];
            }
        }
        
        return $ret;
    
    }


    public static function getOrder($number)
    {
        require_once DIR_WS_CLASSES . 'order.php';
        
        $order = new order($number);
        if (empty($number) || empty($order)) {
            return null;
        }
        
        $total_q = xtc_db_query("SELECT value FROM " . TABLE_ORDERS_TOTAL . " where orders_id=" . $number . " and class='ot_total'");
        $total_a = xtc_db_fetch_array($total_q);
        $total = $total_a ['value'];
        
        $lang_q = xtc_db_query("SELECT l.code, o.gm_orders_code FROM " . TABLE_ORDERS . " o join " . TABLE_LANGUAGES . " l on l.directory=o.language where o.orders_id=" . $number);
        $lang_a = xtc_db_fetch_array($lang_q);
        $lang = $lang_a ['code'];
        
        $ret = array ();
        $ret ['number'] = $number;
        $ret ['date'] = date("d.m.Y", strtotime($order->info ['date_purchased']));
        $ret ['email'] = $order->customer ['email_address'];
        $ret ['customer_number'] = $order->customer ['csID'];
        $ret ['payment_method'] = $order->info ['payment_method'];
        $ret ['amount'] = $total;
        $ret ['currency_code'] = $order->info ['currency'];
        $ret ['exchange_rate'] = $order->info ['currency_value'];
        $ret ['language_code'] = $lang . '-' . $order->billing ['country_iso_2'];
        $ret ['delivery_firstname'] = $order->delivery ['firstname'];
        $ret ['delivery_lastname'] = $order->delivery ['lastname'];
        $ret ['delivery_company'] = $order->delivery ['company'];
        $ret ['delivery_street'] = $order->delivery ['street_address'];
        $ret ['delivery_postcode'] = $order->delivery ['postcode'];
        $ret ['delivery_city'] = $order->delivery ['city'];
        $ret ['delivery_countrycode'] = $order->delivery ['country_iso_2'];
        $ret ['billing_firstname'] = $order->billing ['firstname'];
        $ret ['billing_lastname'] = $order->billing ['lastname'];
        $ret ['billing_company'] = $order->billing ['company'];
        $ret ['billing_street'] = $order->billing ['street_address'];
        $ret ['billing_postcode'] = $order->billing ['postcode'];
        $ret ['billing_city'] = $order->billing ['city'];
        $ret ['billing_countrycode'] = $order->billing ['country_iso_2'];
        
        $order_products = $order->products;
        foreach ($order_products as $product) {
            if ($product ['model']) {
                $ret ['products'] [] = $product ['model'];
            } else {
                $ret ['products'] [] = $product ['name'];
            }
        }
        
        if ($lang_a ['gm_orders_code'] != '') {
            $ret ['invoice_number'] [] = $lang_a ['gm_orders_code'];
        }
        
        return $ret;
    
    }


    public static function updateOrder($order, $status, $callbackid)
    {
        xtc_db_query("update " . TABLE_ORDERS . " set orders_status = '" . xtc_db_input($status) . "', last_modified = now() where orders_id = '" . xtc_db_input($order ['number']) . "'");
        xtc_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . xtc_db_input($order ['number']) . "', '" . xtc_db_input($status) . "', now(), '0', 'Komfortkasse ID " . $callbackid . "')");
    
    }


    
    public static function getInvoicePdfPrepare()
    {
        global $kkdir;
        $kkdir = getcwd();
        chdir('../../admin');
        define('SUPPRESS_REDIRECT', true);
        require_once('includes/application_top.php');
    }
    
    public static function getInvoicePdf($invoicenumber, $order_id=null)
    {
        if ($order_id == null) {
            $order_q = xtc_db_query("SELECT o.orders_id, l.code as languages_code, l.languages_id, o.language FROM " . TABLE_ORDERS . " o join " . TABLE_LANGUAGES . " l on l.directory=o.language where o.gm_orders_code='" . $invoicenumber . "'");
            $order_a = xtc_db_fetch_array($order_q);
            $order_id = $order_a ['orders_id'];
        } else {
            $order_q = xtc_db_query("SELECT o.orders_id, l.code as languages_code, l.languages_id, o.language FROM " . TABLE_ORDERS . " o join " . TABLE_LANGUAGES . " l on l.directory=o.language where o.orders_id='" . $order_id . "'");
            $order_a = xtc_db_fetch_array($order_q);
        }
        
        if ($order_id) {
            $_GET['oID'] = $order_id;
            $_GET['type'] = 'invoice';
            
            $coo_lang_file_master = MainFactory::create_object('LanguageTextManager', array(), true);
            @$coo_lang_file_master->init_section('lang/'.$order_a['language'].'/admin/gm_pdf_order.php', $order_a['languages_id']);
            $_SESSION['language_code'] = $order_a['languages_code'];
            $_SESSION['language'] = $order_a['language'];
            $_SESSION['languages_id'] = $order_a['languages_id'];
            require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/' . $_SESSION['language'] . '.php');
            
            global $kkdir;
            chdir($kkdir);
            require_once ('../../admin/gm_pdf_order.php');
        }
    
    }
}

?>
<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! isTableExist('redirect_to_strlow_url') ) {
    $dhd = new StrTolowUrls;
}

if( StrTolowUrls::getState() == 'on' && StrTolowUrls::is_needle_redirect() ) {
    StrTolowUrls::redirectOn();
}

class StrTolowUrls {

    public static $tableName = 'redirect_to_strlow_url';
    public static $state = '';

    public function __construct() {
        $this->classInitial();
    }

    private function classInitial() {

        global $wpdb;

        if ( !$wpdb ) {
            die('Connection failed: wpdb');
        }

        if( ! $this->isTableExist() ) {
            $this->createDBTable();
        }

    }

    private function isTableExist() {
        global $wpdb;
        $tn = self::$tableName;
        $sql = "SHOW TABLES LIKE '%{$tn}%'";
        return $wpdb->get_results($sql);
    }

    private function createDBTable() {
        global $wpdb;
        $tn = self::$tableName;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$tn} (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                get_pars_allow VARCHAR(255) NULL, 
                state VARCHAR(255) DEFAULT 'on'
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
        $this->insertInit();
    }

    private function insertInit() {
        global $wpdb;
        $wpdb->insert( self::$tableName, ['state' => 'on'] );
    }

    public static function updateStateInDb( $state ) {
        global $wpdb;
        $wpdb->update(self::$tableName, array('state'=>$state), array('id'=>1));
        self::$state = $state;
    }

    public static function updateGetParsInDb( $get_pars ) {
        global $wpdb;
        $wpdb->update(self::$tableName, array('get_pars_allow' => $get_pars), array('id'=>1));
    }

    public static function getState() {
        global $wpdb;
        $table = self::$tableName;
        $sql = "SELECT `state` FROM {$table} WHERE `id` = 1";
        return self::$state = $wpdb->get_var($sql);
    }

    private static function removeQueryStringParameter($url, $varname)
    {
        $parsedUrl = parse_url($url);
        $query = array();

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            unset($query[$varname]);
        }

        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = !empty($query) ? '?' . http_build_query($query) : '';

        $host = $parsedUrl['host'] ?? '';
        $scheme = $parsedUrl['scheme'] ?? '';

        return $scheme . '://' . $host . $path . $query;
    }

    public static function redirectOn() {

        $url = $_SERVER['REQUEST_URI'] ?? '';

        $url_parts = explode('?', $url);

        if( !isset($url_parts[1])) return;

        $get_params = explode( '&', $url_parts[1]);

        $gets_allow_params = self::get_allow_params();

        $get_params_lowercase = [];
        foreach( $get_params as $get_param ) {
            $get_param_arr = explode('=', $get_param);
            if( !in_array( trim($get_param_arr[0]), $gets_allow_params)) {
                $get_param = strtolower($get_param);
            }
            $get_params_lowercase[] = $get_param;
        }

        $url_gets_lowercase = implode('&', $get_params_lowercase);
        $url_lowercase = $url_parts[0] . '?' . $url_gets_lowercase;

        exit( wp_redirect( $url_lowercase ) );

    }

    public static function get_allow_params() {
        global $wpdb;
        $table = self::$tableName;
        $sql = "SELECT `get_pars_allow` FROM {$table} WHERE `id` = 1";
        $res = $wpdb->get_var($sql);
        if( $res )
            return json_decode($res);
        return false;
    }

    public static function save_get_parametres( $data ) {

        if( !self::is_data_valide_from_form_admin($data)) return;

        $options = [];
        foreach( $data as $key => $field ) {
            if ( stripos($key, 'allow_get_params_') !== false ) {
                if($field) {
                    $options [] = trim($field);
                }
            }
        }

        $json_urls = json_encode( $options );

        self::updateGetParsInDb( $json_urls );
    }

    protected static function is_data_valide_from_form_admin( $data ) {
        if(
            (
                $data['allow_gets_save'] && !empty($data['allow_gets_save'])
                || $data['allow_gets_save_add'] && !empty($data['allow_gets_save_add'])
            )
            && isset($data['action'])
            && $data['action'] == 'update'
            && isset($data['_wpnonce'])
            && (stripos($data['_wp_http_referer'], '?page=url-seo-rules' ) !== false )
        ) {
            return true;
        }
        return false;
    }

    public static function is_needle_redirect() {
        $allow_params = self::get_allow_params();
        $url = $_SERVER['REQUEST_URI'];
        if($allow_params) {
            foreach ($allow_params as $allow_param) {
                $url = self::removeQueryStringParameter($url, $allow_param);
            }
        }
        if( strtolower($url) !== $url ) {
            return true;
        }
        return false;
    }

}

<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! isTableExist('double_hyphen_disable') ) {
    $dhd = new DoubleHyphenDisable;
}

if( isset($_POST['double_def_update']) ) {
    DoubleHyphenDisable::updateDataInDb( $_POST['double_def'] ?? '' );
}

if( DoubleHyphenDisable::getState() == 'on' ) {
    DoubleHyphenDisable::doubleHyphenDisable();
}

class DoubleHyphenDisable {

    public static $tableName = 'double_hyphen_disable';
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
                state VARCHAR(255) DEFAULT 'on'
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
        $this->insertInit();
    }

    private function insertInit() {
        global $wpdb;
        $wpdb->insert( self::$tableName, ['state' => ''] );
    }

    public static function updateDataInDb( $value ) {
        global $wpdb;
        $wpdb->update(self::$tableName, array('state'=>$value), array('id'=>1));
    }

    public static function getState() {
        global $wpdb;
        $table = self::$tableName;
        $sql = "SELECT `state` FROM {$table} WHERE `id` = 1";
        return self::$state = $wpdb->get_var($sql);
    }

    public static function doubleHyphenDisable() {
        if( str_replace('--', '-', $_SERVER['REQUEST_URI'] ) != $_SERVER['REQUEST_URI'] ) {
            self::doubleHyphenRemove( $_SERVER['REQUEST_URI'] );
        }
    }

    public static function doubleHyphenRemove( $url ) {
        $url = str_replace('--', '-', $url );
        if( str_replace('--', '-', $url ) != $url ) {
            self::doubleHyphenRemove( $url );
        }
        exit( wp_redirect( $url ) );
    }
}

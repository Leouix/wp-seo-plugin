<?php

class StrictPagination {

    public function __construct()
    {
        if( is_admin() ) {
            $this->classInitial();
        }
    }

    public static function redirect_on() {

        if( stripos( $_SERVER['REQUEST_URI'], 'page/' ) !== false ) {

            $current_page_url = $_SERVER['REQUEST_URI'];
            $allow_pagination_pages = self::get_all_pagination_pages();

            if( $allow_pagination_pages )
                $allow_pagination_pages = json_decode($allow_pagination_pages);

            $all_pagination_pages = [];
            if( is_array( $allow_pagination_pages) ) {
                foreach($allow_pagination_pages as $i => $allow_pagination_page) {

                    $value = trim($allow_pagination_page, '/');
                    if( stripos( $_SERVER['HTTP_HOST'], 'localhost') !== false ) {

                        $local_domain = trim($_SERVER['REQUEST_URI'], '/');
                        $local_domain = explode('/', $local_domain);
                        $local_domain = $local_domain[0] ?? '';
                        $value = $local_domain . '/' . $value;
                    }

                    $all_pagination_pages[$i] = $value;
                }



            }

            $get_get_query = explode('?', $current_page_url);
            $get_pars = $get_get_query[1] ?? '';

            $get_new_url = explode('/page/', $get_get_query[0]);

            if ($get_pars)
                $get_pars = '?' . $get_pars;

            $new_url = $get_new_url[0] . $get_pars;

            if($new_url === '') {
                $new_url = '/';
            }
            if( ! in_array(trim($new_url, '/'), $all_pagination_pages) ) {
                wp_redirect( $new_url );
                exit;
            }

        }

    }

    public static function get_all_pagination_pages() {
        global $wpdb;
        $sql = 'SELECT url_pages_with_pagination FROM allow_pagination_pages';
        return $wpdb->get_var($sql);
    }

    public function classInitial() {

        global $wpdb;

        if ( !$wpdb ) {
            die('Connection failed: wpdb');
        }

        if( ! $this->is_pagination_table_exist() ) {
            $this->createDBTable();
        }

    }

    private function createDBTable() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE allow_pagination_pages (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                url_pages_with_pagination VARCHAR(250)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
        $this->insert_data_in_db();
    }

    public function is_pagination_table_exist( $table_name = 'allow_pagination_pages' ) {

        if( !$table_name ) return;

        global $wpdb;

        $sql = "SHOW TABLES LIKE '%".$table_name."%'";
        $result = $wpdb->get_results($sql);

        return $result;
    }

    private function update_data_in_db( $json_url ) {
        global $wpdb;

        if( !$json_url ) return;
        $wpdb->update('allow_pagination_pages', array('url_pages_with_pagination'=>$json_url), array('id'=>1));
    }

    private function insert_data_in_db( $json_urls = '' ) {
        global $wpdb;
        $tablename = 'allow_pagination_pages';
        $sql = $wpdb->prepare("INSERT INTO `$tablename` (`url_pages_with_pagination`) values (%s)", $json_urls);
        $wpdb->query($sql);

        $wpdb->insert( 'allow_pagination_pages', [ 'id' => '', 'url_pages_with_pagination' => $json_urls] );

    }

    public function save_urls( $data  ) {

        // table has two column: id:int|primary, url_pages_with_pagination:json
        if( !$this->is_data_valide_from_form_admin($data)) return;

        $options = [];
        foreach( $data as $key => $field ) {
            if ( stripos($key, 'allow_page_pagination_') !== false ) {
                if($field) {
                    $field = $this->rm_http_host( $field );
                    $options [] = $field;
                }
            }
        }

        $json_urls = json_encode( $options );

        $this->update_data_in_db( $json_urls );

    }

    private function rm_http_host( $url ) {
        if( stripos($url, 'http') !== false ) {
            $url_arr = parse_url($url);
            $new_url =  $url_arr['path'];
            if(isset($url_arr['query'])) {
                $new_url .=  '?' . $url_arr['query'];
            }
            return $new_url;
        }
        return $url;
    }

    protected function is_data_valide_from_form_admin( $data ) {
        if(
            (
            $data['save'] && !empty($data['save'])
            || $data['save_add'] && !empty($data['save_add'])
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

}

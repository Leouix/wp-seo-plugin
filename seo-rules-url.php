<?php
/*
 * Plugin Name: Seo Rules Url
 * Description: On pages type 'page' forbids url view '/page/%d%', execlude specific urls
 * Version:     1.0 beta
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_settings_link' );
function add_settings_link( array $links ) {
    $url = get_admin_url() . "options-general.php?page=url-seo-rules";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'codechief') . '</a>';
    $links[] = $settings_link;
    return $links;
}

add_action('template_redirect', 'get_frontend_part_plugin');
function get_frontend_part_plugin() {
    if( is_admin() )  return;
    include_once('pagination/FormFieldsAllowPagination.php');
    StrictPagination::redirect_on();
}

add_action('admin_menu', 'get_options_page_in_admin');
function get_options_page_in_admin() {
    add_options_page('Seo Rules Url', null, 'manage_options', 'seo-rules-url', 'allpagination_options_page');
}

function allpagination_options_page()
{

    include_once('pagination/FormFieldsAllowPagination.php');
    include_once('pagination/allow-pagination-options.php');

    include_once('redirectDoubleHyppen/DoubleHyphenDisable.php');

    include_once('redirectToStrlower/RedirectToStrlowUrl.php');
    include_once('redirectToStrlower/redirect-to-strlower-options.php');

    ?>
    <div id="seo-options-plugin">

        <div class="strict-pagination">
            <h2>1. Strict pagination rules.</h2>
            <p class="p-subtitle">Pagination only works on said pages. </p>
            <form id="form-allow_pages_pagination" method="post" action="">
                <?php settings_fields( 'app_plugin_options_group' ); ?>

                <table id="form-allow_pages_pagination-table">

                    <?php if($default) { ?>
                        <tr valign="top">
                            <th scope="row"><label for="allow_page_pagination__0">Page url </label></th>
                            <td><input type="text" id="allow_page_pagination__0" name="allow_page_pagination__0" value="/blog/"  /></td>

                        </tr>
                    <?php } ?>

                    <?php
                    if($allow_page_paginations) {
                        foreach($allow_page_paginations as $index => $allow_page_pagination) {
                            ?>
                            <tr valign="top">
                                <th scope="row"><label for="allow_page_pagination_<?=$index?>">Page url </label></th>
                                <td><input type="text" id="allow_page_pagination_<?=$index?>" name="allow_page_pagination_<?=$index?>" value="<?=$allow_page_pagination?>" /></td>

                            </tr>
                        <?php }
                    }
                    ?>

                </table>

                <div class="form-inputs">
                    <input type="submit" name="save" value="Save">
                    <input type="submit" name="save_add" value="Add">
                </div>

            </form>

            <h3 class="allow_page_pagination-caption">Another requests like '/page/{n}/' will be redirected to the url without '/page/{n}/'</h3>
            <p class="allow_page_pagination-caption"><b>For example redirect:</b> about-us/page/2/ => about-us/</p>
            <p class="allow_page_pagination-caption"><b>For example redirect:</b> contacts/page/2/ => contacts/</p>
        </div>

        <hr>

        <div class="double-def">
            <form id="form-double-def" method="post" action="">
                <label>2. Overwrite '---' on '-' in posts urls?
                    <input id="double-def" type="checkbox" name="double_def" <?= DoubleHyphenDisable::$state ? 'checked' : ''; ?> >
                </label>
                <input type="hidden" name="double_def_update">
            </form>
        </div>

        <hr>

        <div class="lower-urls">
            <h2 class="low-title">3. Redirect url to lowercase</h2>

            <form id="form-state-to-lowercase" action="" method="post">
                <input id="state-to-low" type="checkbox" name="state_to_low" <?= StrTolowUrls::$state == 'on' ? 'checked' : '' ?>>
                <input type="hidden" name="state_to_low_update">
            </form>

            <p class="p-subtitle">For example: from https:example.com/?gEt=SomevAluE to https:example.com/?get=somevalue</p>

            <form id="form-allow-get-params" method="post" action="">
                <?php settings_fields( 'app_plugin_options_group' ); ?>

                <table id="form-allow_get-parameters">

                    <?php if($gets_default) { ?>
                        <tr valign="top">
                            <th scope="row"><label for="allow_get_params__0">Skip get parameter </label></th>
                            <td><input type="text" id="allow_get_params__0" name="allow_get_params__0" value="utm"  /></td>

                        </tr>
                    <?php } ?>

                    <?php
                    if($allow_get_params) {
                        foreach($allow_get_params as $index => $allow_get_param) {

                            ?>
                            <tr valign="top">
                                <th scope="row"><label for="allow_get_params_<?=$index?>">Skip get-parameter </label></th>
                                <td><input type="text" id="allow_get_params_<?=$index?>" name="allow_get_params_<?=$index?>" value="<?=$allow_get_param?>" /></td>

                            </tr>
                        <?php }
                    }
                    ?>

                </table>

                <div class="form-inputs">
                    <input type="submit" name="allow_gets_save" value="Save">
                    <input type="submit" name="allow_gets_save_add" value="Add">
                </div>

            </form>
        </div>

        <script>
            let doubleDefInput = document.querySelector('#double-def');
            let doubleDefForm = document.querySelector('#form-double-def');
            doubleDefInput.addEventListener('change', function() {
                doubleDefForm.submit();
            })
            let strToLowUrlsForm = document.querySelector('#form-state-to-lowercase');
            let strToLowUrlsInput = document.querySelector('#state-to-low');
            strToLowUrlsInput.addEventListener('change', function() {
                strToLowUrlsForm.submit();
            })
        </script>

        <style>
            #form-allow_pages_pagination input[type="text"]{
                width: 95%;
            }
            #form-allow_pages_pagination-table {
                width: 100%;
            }
            #form-allow_pages_pagination-table th {
                width: 77px;
            }

            .form-inputs {
                margin: 15px 0;
            }
            .allow_page_pagination-caption {
                margin: 3px;
            }
            .form-inputs input {
                padding: 5px 20px;
                margin-right: 5px;
                text-transform: uppercase;
                font-size: 12px;
                cursor: pointer;
            }
            .form-inputs input:first-child {
                background: #4597ebba;
                border: 1px solid #4597ebba;
                color: #fff;
                border-radius: 3px;
            }
            .form-inputs input:nth-child(2) {
                background: #30d72dcc;
                border: 1px solid #30d72dcc;
                color: #fff;
                border-radius: 3px;
            }

            #form-double-def {
                font-size: 16px;
                font-weight: 500;
            }

            #double-def {
                margin-left: 5px;
            }

            .low-title,
            #form-state-to-lowercase {
                display: inline-block;
            }

            #seo-options-plugin h2 {
                margin-bottom: 0;
            }

            #seo-options-plugin p.p-subtitle {
                margin-top: 0;
            }

            #form-state-to-lowercase {
                margin-bottom: 0;
                margin-left: 5px;
            }

        </style>

    </div>
    <?php
}

add_action('template_redirect', 'redirect_to_strlow_url');
function redirect_to_strlow_url() {
    include_once('redirectToStrlower/RedirectToStrlowUrl.php');
}

add_action('template_redirect', 'double_hyphen_disable');
function double_hyphen_disable() {
    include('redirectDoubleHyppen/DoubleHyphenDisable.php');
}

function isTableExist( $table ) {
    global $wpdb;
    $sql = "SHOW TABLES LIKE '%{$table}%'";
    return $wpdb->get_results($sql);
}



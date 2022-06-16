<?php

if( isset($_POST['allow_gets_save']) && $_POST['allow_gets_save'] == 'Save') {
    StrTolowUrls::save_get_parametres( $_POST );
}

$add_get_params = false;
if( isset($_POST['allow_gets_save_add']) && !empty($_POST['allow_gets_save_add']) ) {
    StrTolowUrls::save_get_parametres( $_POST );
    $add_get_params = true;
}

$gets_default = false;
$allow_get_params = StrTolowUrls::get_allow_params();
if( !$allow_get_params ) {
    $gets_default = true;
}

if(isset($_POST['state_to_low_update'])) {
    StrTolowUrls::updateStateInDb( $_POST['state_to_low'] ?? '' );
}

if( $add_get_params ) {
    $allow_get_params[] = '';
}

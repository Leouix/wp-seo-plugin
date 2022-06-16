<?php

$ffap = new StrictPagination;

if( isset($_POST['save']) && $_POST['save'] == 'Save') {
    $ffap->save_urls( $_POST );
}

$add_url = false;
if( isset($_POST['save_add']) && !empty($_POST['save_add']) ) {
    $ffap->save_urls( $_POST );
    $add_url = true;
}

$allow_page_paginations = $ffap->get_all_pagination_pages();

if($allow_page_paginations)
    $allow_page_paginations = json_decode($allow_page_paginations);

if($add_url) {
    $allow_page_paginations[] = '';
}

$default = false;
if( !$allow_page_paginations ) {
    $allow_page_paginations = ['https://example.com/page-news'];
    $default = true;
}

<?php
define('JS_JQUERY', 0);
define('JS_JQUERY_UI', 1);
define('JS_BOOTSTRAP', 2);
define('JQUERY_VALIDATE', 3);
define('JQUERY_TOUCH', 4);
//define('JS_TINYNAV', 5);
define('JS_BOOTSTRAP_FH', 6);
define('JS_BOOTSTRAP_SW', 7);
define('JS_DATATABLE', 8);
define('JS_CHART', 9);
define('JS_METISMENU', 10);
define('JS_BOOTBOX', 11);
define('JS_DATATABLE_ODATA', 12);
define('JS_CRYPTO_MD5_JS', 13);
define('JS_JCROP', 14);
define('JS_TYPEAHEAD', 15);
define('JS_CHEET', 16);
define('JS_FLIPSIDE', 20);
define('JS_LOGIN', 21);

define('CSS_JQUERY_UI', 0);
define('CSS_BOOTSTRAP', 1);
define('CSS_BOOTSTRAP_FH', 2);
define('CSS_BOOTSTRAP_SW', 3);
define('CSS_DATATABLE', 4);
define('CSS_JCROP', 5);
define('CSS_FONTAWESOME', 6);

global $jsArray;
$jsArray = array(
        JS_JQUERY => array(
            'no' => array(
                'no'  => '/js/common/jquery.js',
                'min' => '/js/common/jquery.min.js'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.js',
                'min' => '//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'
            )
        ),
        JS_JQUERY_UI => array(
            'no' => array(
                'no'  => '/js/common/jquery-ui.js',
                'min' => '/js/common/jquery-ui.min.js'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js',
                'min' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'
            )
        ),
        JS_BOOTSTRAP => array(
            'no' => array(
                'no'  => '/js/common/bootstrap.js',
                'min' => '/js/common/bootstrap.min.js'
            ),
            'cdn' => array(
                'no'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js',
                'min' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'
            )
        ),
        JQUERY_VALIDATE => array(
            'no' => array(
                'no'  => '/js/common/jquery.validate.js',
                'min' => '/js/common/jquery.validate.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js'
            )
        ),
        JQUERY_TOUCH => array(
            'no' => array(
                'no'  => '/js/common/jquery.ui.touch-punch.min.js',
                'min' => '/js/common/jquery.ui.touch-punch.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js'
            )
        ),
        JS_BOOTSTRAP_FH => array(
            'no' => array(
                'no'  => '/js/common/bootstrap-formhelpers.js',
                'min' => '/js/common/bootstrap-formhelpers.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/js/bootstrap-formhelpers.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/js/bootstrap-formhelpers.min.js'
            )
        ),
        JS_BOOTSTRAP_SW => array(
            'no' => array(
                'no'  => '/js/common/bootstrap-switch.js',
                'min' => '/js/common/bootstrap-switch.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js'
            )
        ),
        JS_DATATABLE => array(
            'no' => array(
                'no'  => '/js/common/jquery.dataTables.js',
                'min' => '/js/common/jquery.dataTables.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.15/js/jquery.dataTables.js',
                'min' => '//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js'
            )
        ),
        JS_CHART => array(
            'no' => array(
                'no'  => '/js/common/Chart.js',
                'min' => '/js/common/Chart.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.min.js'
            )
        ),
        JS_METISMENU => array(
            'no' => array(
                'no'  => '/js/common/metisMenu.js',
                'min' => '/js/common/metisMenu.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.7.0/metisMenu.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.7.0/metisMenu.min.js'
            )
        ),
        JS_BOOTBOX => array(
            'no' => array(
                'no'  => '/js/common/bootbox.js',
                'min' => '/js/common/bootbox.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js'
            )
        ),
        JS_DATATABLE_ODATA => array(
            'no' => array(
                'no'  => '/js/common/jquery.dataTables.odata.js',
                'min' => '/js/common/jquery.dataTables.odata.js',
            ),
            'cdn' => array(
                'no'  => '/js/common/jquery.dataTables.odata.js',
                'min' => '/js/common/jquery.dataTables.odata.js',
            )
        ),
        JS_CRYPTO_MD5_JS => array(
            'no' => array(
                'no'  => '/js/common/md5.js',
                'min' => '/js/common/md5.js',
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.9-1/crypto-js.min.js',
            )
        ),
        JS_JCROP => array(
            'no' => array(
                'no'  => '/js/common/jquery.Jcrop.js',
                'min' => '/js/common/jquery.Jcrop.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.5/js/Jcrop.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.5/js/Jcrop.min.js'
            )
        ),
        JS_TYPEAHEAD => array(
            'no' => array(
                'no'  => '/js/common/typeahead.bundle.js',
                'min' => '/js/common/typeahead.bundle.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js'
            )
        ),
        JS_CHEET => array(
            'no' => array(
                'no'  => '/js/common/cheet.js',
                'min' => '/js/common/cheer.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdn.rawgit.com/namuol/cheet.js/master/cheet.min.js',
                'min' => '//cdn.rawgit.com/namuol/cheet.js/master/cheet.min.js'
            )
        ),
        JS_FLIPSIDE => array(
            'no' => array(
                'no'  => '/js/common/flipside.js',
                'min' => '/js/common/flipside.min.js'
            ),
            'cdn' => array(
                'no'  => '/js/common/flipside.js',
                'min' => '/js/common/flipside.min.js'
            )
        ),
        JS_LOGIN => array(
            'no' => array(
                'no'  => '/js/common/login.js',
                'min' => '/js/common/login.min.js'
            ),
            'cdn' => array(
                'no'  => '/js/common/login.js',
                'min' => '/js/common/login.min.js'
            )
        )
);

global $cssArray;
$cssArray = array(
    CSS_JQUERY_UI => array(
        'no' => array(
                'no'  => '/css/common/jquery-ui.css',
                'min' => '/css/common/jquery-ui.min.css'
            ),
            'cdn' => array(
                'no'  => '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css',
                'min' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css'
            )
    ),
    CSS_BOOTSTRAP => array(
            'no' => array(
                'no'  => '/css/common/bootstrap.css',
                'min' => '/css/common/bootstrap.min.css'
            ),
            'cdn' => array(
                'no'  => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css',
                'min' => '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'
            )
    ),
    CSS_BOOTSTRAP_FH => array(
        'no' => array(
                'no'  => '/css/common/bootstrap-formhelpers.css',
                'min' => '/css/common/bootstrap-formhelpers.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.css',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-formhelpers/2.3.0/css/bootstrap-formhelpers.min.css'
            )
    ),
    CSS_BOOTSTRAP_SW => array(
            'no' => array(
                'no'  => '/css/common/bootstrap-switch.css',
                'min' => '/css/common/bootstrap-switch.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.css',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/css/bootstrap3/bootstrap-switch.min.css'
            )
    ),
    CSS_DATATABLE => array(
        'no' => array(
                'no'  => '/css/common/jquery.dataTables.css',
                'min' => '/css/common/jquery.dataTables.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.15/css/jquery.dataTables.css',
                'min' => '//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css'
            )
    ),
    CSS_JCROP => array(
        'no'  => array(
            'no'  => '/css/common/jquery.Jcrop.css',
            'min' => '/css/common/jquery.Jcrop.min.css'
        ),
        'cdn' => array(
            'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.5/css/Jcrop.css',
            'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.5/css/Jcrop.min.css'
        )
    ),
    CSS_FONTAWESOME => array(
        'no'  => array(
            'no'  => '/css/common/font-awesome.css',
            'min' => '/css/common/font-awesome.min.css'
        ),
        'cdn' => array(
            'no'  => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css',
            'min' => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'
        )
    )
);

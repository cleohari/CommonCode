<?php
define('JQUERY_VALIDATE', 3);
define('JQUERY_TOUCH', 4);
define('JS_BOOTSTRAP_FH', 6);
define('JS_DATATABLE', 8);
define('JS_CHART', 9);
define('JS_METISMENU', 10);
define('JS_BOOTBOX', 11);
define('JS_CRYPTO_MD5_JS', 13);
define('JS_JCROP', 14);
define('JS_TYPEAHEAD', 15);
define('JS_CHEET', 16);
define('JS_FLIPSIDE', 20);
define('JS_LOGIN', 21);

define('CSS_BOOTSTRAP_FH', 2);
define('CSS_DATATABLE', 4);
define('CSS_JCROP', 5);

global $jsArray;
$jsArray = array(
        JQUERY_VALIDATE => array(
            'no' => array(
                'no'  => '/js/common/jquery.validate.js',
                'min' => '/js/common/jquery.validate.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js'
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
        JS_DATATABLE => array(
            'no' => array(
                'no'  => '/js/common/jquery.dataTables.js',
                'min' => '/js/common/jquery.dataTables.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.24/js/jquery.dataTables.js',
                'min' => '//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js'
            )
        ),
        JS_CHART => array(
            'no' => array(
                'no'  => '/js/common/Chart.js',
                'min' => '/js/common/Chart.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js'
            )
        ),
        JS_METISMENU => array(
            'no' => array(
                'no'  => '/js/common/metisMenu.js',
                'min' => '/js/common/metisMenu.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.7.9/metisMenu.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/metisMenu/2.7.9/metisMenu.min.js'
            )
        ),
        JS_BOOTBOX => array(
            'no' => array(
                'no'  => '/js/common/bootbox.js',
                'min' => '/js/common/bootbox.min.js'
            ),
            'cdn' => array(
                'no'  => array('src' => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.js', 'hash'=> 'sha384-BHF5LmonG4E0P/YVoGa+evSQ0kCfsV79+40QpNopa3jVcY6Yq17QvbXgSDbN4Kl5'),
                'min' => array('src' => '//cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js', 'hash'=> 'sha384-sC7Bdgy9YlmFGE+HcLLS8ACec+aVuGTAprh0Wenq1mdKJs4DxPUthlbtbNxTbSOu')
            )
        ),
        JS_CRYPTO_MD5_JS => array(
            'no' => array(
                'no'  => '/js/common/md5.js',
                'min' => '/js/common/md5.js',
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.3.0/crypto-js.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/crypto-js/3.3.0/crypto-js.min.js',
            )
        ),
        JS_JCROP => array(
            'no' => array(
                'no'  => '/js/common/jquery.Jcrop.js',
                'min' => '/js/common/jquery.Jcrop.min.js'
            ),
            'cdn' => array(
                'no'  => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/js/Jcrop.js',
                'min' => '//cdnjs.cloudflare.com/ajax/libs/jquery-jcrop/2.0.4/js/Jcrop.min.js'
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
                'min' => '/js/common/cheet.min.js'
            ),
            'cdn' => array(
                'no'  => array('src' => 'https://cdn.rawgit.com/namuol/cheet.js/0.3.3/cheet.min.js', 'hash'=> 'sha384-8sTXxKn53rkirkXu5gKBBpFxoK/zmAefVSPu6IvC29DIKRkU94ep9TNs6tgyxde4'),
                'min' => array('src' => 'https://cdn.rawgit.com/namuol/cheet.js/0.3.3/cheet.min.js', 'hash'=> 'sha384-8sTXxKn53rkirkXu5gKBBpFxoK/zmAefVSPu6IvC29DIKRkU94ep9TNs6tgyxde4')
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
    CSS_DATATABLE => array(
        'no' => array(
                'no'  => '/css/common/jquery.dataTables.css',
                'min' => '/css/common/jquery.dataTables.min.css'
            ),
            'cdn' => array(
                'no'  => '//cdn.datatables.net/1.10.24/css/jquery.dataTables.css',
                'min' => '//cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css'
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
    )
);

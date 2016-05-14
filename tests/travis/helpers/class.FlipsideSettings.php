<?php
class FlipsideSettings
{
    public static $dataset = array(
        'auth' => array(
            'type' => 'SQLDataSet',
            'params' => array(
                'dsn'  => 'mysql:host=localhost;dbname=auth',
                'host' => 'localhost',
                'user' => 'root',
                'pass' => ''
            )
        )
    );
}
?>

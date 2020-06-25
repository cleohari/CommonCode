<?php
class FlipsideSettings
{
    public static $dataset = array(
        'authentication' => array(
            'type' => 'SQLDataSet',
            'params' => array(
                'dsn' => 'sqlite:/tmp/auth.sq3'
            )
        ),
        'pending_authentication' => array(
            'type' => 'SQLDataSet',
            'params' => array(
                'dsn' => 'sqlite::memory:'
            )
        ),
	'memory' => array(
           'type' => 'SQLDataSet',
           'params' => array(
                'dsn' => 'sqlite::memory:'
           )
        ),
	'profiles' => array(
           'type' => 'SQLDataSet',
           'params' => array(
                'dsn' => 'sqlite::memory:'
           )
	)
    );

    public static $authProviders = array(
        'Flipside\\Auth\\SQLAuthenticator' => array(
            'current' => true,
            'pending' => true,
            'supplement' => false
        )
    );
}

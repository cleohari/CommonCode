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
                'dsn' => 'sqlite:/tmp/pending.sq3'
            )
        )
    );

    public static $authProviders = array(
        'Auth\\SQLAuthenticator' => array(
            'current' => true,
            'pending' => true,
            'supplement' => false
        )
    );
}

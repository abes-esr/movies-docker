<?php

//Variable utilisée pour faire fonctionner l'autocompletion
$wgServer = getenv('WIKIBASE_SCHEME') . "://" . getenv('WIKIBASE_URL_PUBLIQUE');

// LOAD NUKE EXTENSION
wfLoadExtension( 'Nuke' );
wfLoadExtension( 'LDAPProvider' );
wfLoadExtension( 'PluggableAuth' );
wfLoadExtension( 'LDAPAuthentication2');

$LDAPProviderDomainConfigProvider = function() {
    $config = [
        'levant.abes.fr' => [
            'connection' => [               
                'server' => getenv('LDAP_SERVER'),
                'port' => getenv('LDAP_PORT'),
                'user' => getenv('LDAP_USER'),
                'pass' => getenv('LDAP_PASS'),
                'enctype' => 'clear',
                'options' => [
                    'LDAP_OPT_DEREF' => 1
                ],
                'basedn' => 'ou=personnels,ou=utilisateurs,dc=levant,dc=abes,dc=fr',
                'userbasedn' => 'ou=personnels,ou=utilisateurs,dc=levant,dc=abes,dc=fr',
                'groupbasedn' => 'ou=personnels,ou=utilisateurs,dc=levant,dc=abes,dc=fr',
                'searchattribute' => 'samaccountname',
                'usernameattribute' => 'samaccountname',
                'realnameattribute' => 'cn',
                'emailattribute' => 'mail',
                'grouprequest' => 'MediaWiki\\Extension\\LDAPProvider\\UserGroupsRequest\\UserMemberOf::factory',
                'presearchusernamemodifiers' => [
                    'spacestounderscores',
                    'lowercase'
                ]
            ]
        ]
    ];
		return new \MediaWiki\Extension\LDAPProvider\DomainConfigProvider\InlinePHPArray($config);
	};


	// Si la connexion locale est également prise en charge, ces variables globales sont toujours nécessaires 
$wgPluggableAuth_EnableLocalLogin  =  true ; 
$LDAPAuthentication2AllowLocalLogin  =  true ; 
$wgPluggableAuth_Class  =  "MediaWiki\\Extension\\LDAPAuthentication2\\PluggableAuth" ; 
$wgPluggableAuth_ButtonLabel  =  "Connexion (PluggableAuth)" ;


$wgPluggableAuth_Config [ 'Log In (LDAP)' ]  =  [ 
    'plugin'  =>  'LDAPAuthentication2' , 
    'data'  =>  [ 
        'domain'  =>  getenv('LDAP_DOMAIN')
    ] 
];

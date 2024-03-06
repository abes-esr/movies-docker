<?php

//Variable utilisée pour faire fonctionner l'autocompletion (mais warning dans jobrunner...)
$wgServer = getenv('WIKIBASE_SCHEME') . "://" . getenv('WIKIBASE_URL_PUBLIQUE');

$wgLogos = [
	'1x' => "/img/wikibase_logo.png",	    // path to 1x version
    'icon' => "/img/icon_100x100.png",			// A version of the logo without wordmark and tagline
];

$wgSitename = "Wikibase Movies";

// LOAD NUKE EXTENSION
wfLoadExtension( 'Nuke' );

// LDAP
wfLoadExtension( 'LDAPProvider' );
wfLoadExtension( 'PluggableAuth' );
wfLoadExtension( 'LDAPAuthentication2');
wfLoadExtension( 'LDAPAuthorization' );
wfLoadExtension( 'LDAPUserInfo' );
wfLoadExtension( 'LDAPGroups' );

//Voir les droits du Wiki : wiki/Special:ListGroupRights

// Disable anonymous editing
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['user']['edit'] = true;
$wgGroupPermissions['user']['delete'] = true;
$wgGroupPermissions['sysop']['edit'] = true;

$wgGroupPermissions['bot']['edit'] = true;
$wgGroupPermissions['bot']['noratelimit'] = true;

$wgGroupPermissions['*']['createaccount'] = false;
$wgGroupPermissions['*']['autocreateaccount'] = true;

// Pour que les users puissent ajouter des déclarations avec une propriété de type URL :
$wgGroupPermissions['user']['skipcaptcha'] = true;
//Autre possibilité : https://phabricator.wikimedia.org/T356398

// Authentification LDAP 
$LDAPProviderDomainConfigProvider = function() {
    $config = [
        'levant.abes.fr' => [
            'connection' => [               
                'server' => getenv('LDAP_SERVER'),
                'port' => getenv('LDAP_PORT'),
                'user' => getenv('LDAP_USER'),
                'pass' => getenv('LDAP_PASS'),
                'enctype' => 'ssl',
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
            ],
			'userinfo' => [],
			'authorization' => [
				'rules' => [
					'attributes' => [					
						'mail' => explode(',', getenv('LDAP_MAILS'))
					]
				]
			],
			'groupsync' => [
				'mapping' => [
					'user' => 'CN=Utilisateur_GED,OU=GED_EXAGED,OU=Groupes de securite,DC=levant,DC=abes,DC=fr'
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


$wgPluggableAuth_Config [ 'Connexion LDAP' ]  =  [ 
    'plugin'  =>  'LDAPAuthentication2' , 
    'data'  =>  [ 
        'domain'  =>  getenv('LDAP_DOMAIN')
    ] 
];

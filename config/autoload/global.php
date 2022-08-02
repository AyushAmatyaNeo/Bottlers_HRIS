<?php

return [
    'db' => [
        'driver' => 'oci8',
        'connection_string' => '(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = 10.255.0.103)(PORT = 1521))
        (CONNECT_DATA =
        (SERVER = DEDICATED)
        (SERVICE_NAME = orcl)
        )
        )',
        
//    'username' => 'HRIS',
//    'password' => 'NEO_HRIS',

 'username' => 'BNL_GIT',
 'password' => 'BNL_GIT',
          
   // 'username' => 'HRIS_DEMO_2018',
   //  'password' => 'HRIS_DEMO_2018',

    //     'username' => 'JGI1129',
    // 'password' => 'JGI1129',

//         'username' => 'JGI7475_HM',
//         'password' => 'JGI7475_HM',
        
//        'username' => 'HRIS_TRAVEL',
//        'password' => 'HRIS_TRAVEL',
          
//        'username' => 'LAXMI_HRIS_APR28',
//        'password' => 'LAXMI_HRIS_APR28',
        
//          'username' => 'HRIS_DEMO',
//          'password' => 'HRIS_DEMO',
        
//        'username' => 'JWL_HRIS_APR5',
//        'password' => 'JWL_HRIS_APR5',
//        
//        'username' => 'JWL_HRIS_APR4',
//        'password' => 'JWL_HRIS_APR4',
//        
     //   'username' => 'ITNEPAL_HRIS_APR2',
      //  'password' => 'ITNEPAL_HRIS_APR2',
        
//        'username'      => 'HRIS_JWL',
//        'password'      => 'HRIS_JWL',
//        
//        'username' => 'HRIS_MODERN',
//        'password' => 'HRIS_MODERN',
        
   //     'username'=>'HRIS_VIANET',
     //   'password'=>'HRIS_VIANET',
        
        'platform_options' => ['quote_identifiers' => false]
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ],
    ],
];

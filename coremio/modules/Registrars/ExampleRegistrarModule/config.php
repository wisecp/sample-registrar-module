<?php 
return [
    'meta'     => [
        'name'    => 'ExampleRegistrarModule',
        'version' => '1.0',
        'logo'    => 'logo.png',
    ],
    'settings' => [
        // whois-types : If it is desired to indicate that (registrant, administrative, technical, billing) communication types are supported in the module, "true" must be defined.
        'whois-types'      => true,

        // dns-record-types : If the addDnsRecord() function is defined in the module class, the indices you define here will determine the dns types.
        'dns-record-types' => [
            'A',
            'AAAA',
            'MX',
            'CNAME',
            'TXT',
        ],

        // dns-digest-types : If the addDnsSecRecord() function is defined in the module class, the indices you define here will determine the dns digest types.
        'dns-digest-types'     => [
            1               => 'SHA-1 (1)',
            2               => 'SHA-256 (2)',
            3               => 'GOST R 34.11-94 (3)',
            4               => 'SHA-384 (4)',
        ],

        // dns-algorithms : If the addDnsSecRecord() function is defined in the module class, the indices you define here will determine the dns sec algorithms.

        'dns-algorithms'       => [
            1               => 'RSA/MD5 (1)',
            2               => 'Diffie-Hellman (2)',
            3               => 'DSA/SHA-1 (3)',
            4               => 'Elliptic Curve (4)',
            5               => 'RSA/SHA-1 (5)',
            6               => 'DSA-NSEC3-SHA1 (6)',
            7               => 'RSASHA1-NSEC3-SHA1 (7)',
            8               => 'RSA/SHA-256 (8)',
            10               => 'RSA/SHA-512 (10)',
            12               => 'ECC-GOST (12)',
            13              => 'ECDSA Curve P-256 with SHA-256 (13)',
            14              => 'ECDSA Curve P-384 with SHA-384 (14)',
            252             => 'Indirect (252)',
            253             => 'Private DNS (253)',
            254             => 'Private OID (254)',
        ],

        // dc-fields : You can use it to obtain some private information from the customer before registering or transferring the domain name.
        'doc-fields' => [
            // Documentation for the us tld
            'us'    => [
                'field1' => [
                    'type' => 'select',
                    // required: If it is a required parameter for the module, make it mandatory.
                    'required' => true, // Required only for order steps.
                    'name' => 'Choose a option', // Multiple language: ['en' => 'Choose a option', 'de' => 'Wählen Sie eine Option']
                    'description' => 'Sample description for field', // Multiple language: ['en' => 'English desc' , 'de' => 'Deutschland description']
                    'options' => [
                        'option1' => 'Sample option 1', // ['en' => 'Sample option 1','de' => 'Beispieloption 1']
                        'option2' => 'Sample option 2', // ['en' => 'Sample option 1','de' => 'Beispieloption 1']
                        'option3' => 'Sample option 3', // ['en' => 'Sample option 1','de' => 'Beispieloption 1']
                    ],
                ],
                'field2' => [
                    'type' => 'text',
                    'required' => false, // Required only for order steps.
                    'name' => 'An example input box', // Multiple language: ['en' => 'An example input box', 'de' => 'Ein Beispiel-Eingabefeld']
                    'description' => 'Sample description for field', // Multiple language: ['en' => 'English desc' , 'de' => 'Deutschland description']
                ],
                'field3' => [
                    'type' => 'file',
                    // Required only for order steps.
                    /*
                     * If the target field is not empty and you want it to be mandatory, do the following
                     * 'required' =>  ['field1' => "NOT_EMPTY"],
                     * Or you can specify that it is required if it is equal to a specified value.
                     * 'required' => ['field1' => ["option1","option3"]],
                     * Or if the type of the target field is a "text" and it contains the value you type:
                     * 'required' => ['fieldText1' => ["Phrase 1","Phrase 2"]],
                     * Or to say that it is mandatory no matter what:
                     * 'required' => true,
                     */
                    'required' => true,
                    'name' => 'An example input file',
                    'description' => 'Sample description for field', // Multiple language: ['en' => 'English desc' , 'de' => 'Deutschland description']
                    'allowed_ext' => 'jpg,jpeg,png,gif,zip,rar',
                    'max_file_size' => 3,
                ],
            ],
            // Documentation for the ru tld
            'ru' => [
                'field1' => [
                    'type' => 'select',
                    'name' => 'Choose a option',
                    'options' => [
                        'option1' => 'Sample option 1',
                        'option2' => 'Sample option 2',
                        'option3' => 'Sample option 3',
                    ],
                ],
                'field2' => [
                    'type' => 'text',
                    'name' => 'An example input box',
                ],
            ],
        ],

        'username'          => '',
        'password'          => '',
        'test-mode'         => 0,
        'whidden-amount'    => 0,
        'whidden-currency'  => 4,
        'adp'               => false,
        'cost-currency'     => 4,
    ],
];

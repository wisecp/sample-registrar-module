<?php

    class ExampleRegistrarModule extends RegistrarModule
    {
        // Variables and functions previously defined in this class were transferred to the ‘RegistrarModule’ parent.
        // The $this->api variable defined in functions is an imaginary variable.

        function __construct(){
            parent::__construct(__CLASS__);
        }

        public function config_fields($data=[]):array
        {
            // $data                : Retrieves previously saved data.
            // 'name'               : Name of the Configuration option
            // 'description'        : Description of the configuration option
            // 'type'               : Type of configuration option
            // 'wrap_width'         : Wrapper width of the configuration option, percentage % (5,10,20,30,30,40,40,50,...100)
            // 'width'              : Width of configuration option, percent % (5,10,20,30,40,50,...100)
            // 'rows'               : Number of lines of text field type
            // 'value'              : Default Value
            // 'placeholder'        : Value to appear if field is left blank
            // 'options'            : The value that should be defined in String or Array type
            // 'checked'            : Used to determine the status of approval box, true or false
            // 'is_tooltip'         : Shows the description information in balloon, true or false should be written
            // 'dec_pos'            : Determines the position of description information, type "L" to show under the name, type 'R' to show under the item

            return [
                'username'          => [
                    'name'              => "Username",
                    'description'       => "Description for text box field",
                    'type'              => "text",
                    'value'             => $data["username"] ?? '',
                    'placeholder'       => "Sample placeholder",
                ],
                'apiKey'          => [
                    'type'              => "password",
                    'name'              => "API Key",
                    'description'       => "Description for password box field",
                    'value'             => $data["apiKey"] ?? '',
                    'placeholder'       => "Sample placeholder",
                ],
                'test-mode'          => [
                    'type'              => "approval",
                    'name'              => "Test Mode",
                    'description'       => "Description for confirm button",
                    'checked'           => $data["test-mode"] ?? false, // true or false
                ],
                'example4'          => [
                    'type'              => "dropdown",
                    'name'              => "Drop-down Menu 1",
                    'description'       => "Description for Drop-down menu 1",
                    'options'           => "Option 1,Option 2,Option 3,Option 4",
                    'value'             => $data["example4"] ?? "Option 2",
                ],
                'example5'          => [
                    'type'              => "dropdown",
                    'name'              => "Drop-down Menu 2",
                    'description'       => "Description for Drop-down menu 2",
                    'options'           => [
                        'opt1'     => "Option 1",
                        'opt2'     => "Option 2",
                        'opt3'     => "Option 3",
                        'opt4'     => "Option 4",
                    ],
                    'value'             => $data["example5"] ?? "opt2",
                ],
                'example6'          => [
                    'type'              => "radio",
                    'name'              => "Circular (Radio) Button 1",
                    'description'       => "Description for Circular (Radio) Button 1",
                    'description_pos'   => 'L',
                    'is_tooltip'        => true,
                    'options'           => "Option 1,Option 2,Option 3,Option 4",
                    'value'             => $data["example6"] ?? "Option 2",
                ],
                'example7'          => [
                    'type'              => "radio",
                    'name'              => "Circular (Radio) Button 2",
                    'description'       => "Description for Circular (Radio) Button 2",
                    'description_pos'   => 'L',
                    'is_tooltip'        => true,
                    'options'           => [
                        'sec1'     => "Option 1",
                        'sec2'     => "Option 2",
                        'sec3'     => "Option 3",
                        'sec4'     => "Option 4",
                    ],
                    'value'             => $data["example7"] ?? '',
                ],
                'example8'          => [
                    'type'              => "textarea",
                    'name'              => "Text Field",
                    'description'       => "Description for Text Field",
                    'rows'              => "3",
                    'value'             => $data["example8"] ?? '',
                    'placeholder'       => "Sample placeholder",
                ],
            ];
        }

        // Use this function if you want a test connection button to appear in the settings (Optional)
        public function testConnection($config=[]){
            $username   = $config["settings"]["example1"];
            $password   = $config["settings"]["example2"];
            $sandbox    = $config["settings"]["example3"];

            if(!$username || !$password){
                $this->error = "Please define the API information."; # or $this->lang["error-message-variable"];
                return false;
            }

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true; // test successful
        }

        // Use this method if domain availability checking is supported. (Optional)

        public function questioning($sld=NULL,$tlds=[]){
            $sld = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);

            if(!is_array($tlds)) $tlds = [$tlds];

            $result         = [];

            // $tlds example array output : ['com','net','org']

            $result['com'] = [
                'status'        => "unavailable",
            ];

            $result['net'] = [
                'status'        => "available",
            ];

            $result['org'] = [
                'status'        => "unavailable",
                'premium'       => true,
                'premium_price' => [
                    'amount' => 12345.6789,
                    'currency' => 'USD',
                ]
            ];

            return $result;
        }

        // Required function
        public function register($domain='',$sld='',$tld='',$year=1,$dns=[],$whois=[],$wprivacy=false,$eppCode=''){
            $domain             = idn_to_ascii($domain,0,INTL_IDNA_VARIANT_UTS46);
            $sld                = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);


            $api_params         = [
                'Domain'        => $domain,
                'Year'          => $year,
            ];

            if($eppCode) $api_params['EppCode'] = $eppCode;

            // If the tld contains a document, we process it.
            $require_docs       = $this->config["settings"]["doc-fields"][$tld] ?? [];
            if($require_docs)
            {
                // If there is a document defined in the tld and the user has not sent a document, we give a warning.
                if(!$this->docs)
                {
                    $this->error = "Required documents for domain name not defined";
                    return false;
                }

                // We prepare the obtained document entries to be sent to the domain name provider.
                foreach($require_docs AS $doc_id => $doc)
                {
                    if(($doc["required"] ?? false) && (!isset($this->docs[$doc_id]) || strlen($this->docs[$doc_id]) < 1))
                    {
                        $this->error = 'The document "'.self::get_doc_lang($doc["name"]).'" is not specified!';
                        return false;
                    }

                    $doc_value = $this->docs[$doc_id];

                    if($doc["type"] == "file") $doc_value = base64_encode(file_get_contents($doc_value));

                    $api_params['RequireInformation'][$doc_id] = $doc_value;
                }
            }


            $convert_key = [
                'registrant'        => 'Owner',
                'administrative'    => 'Admin',
                'technical'         => 'Tech',
                'billing'           => 'Bill',
            ];
            $contact_types          = array_keys($convert_key);

            foreach($contact_types AS $w_ct)
            {
                $ct = $convert_key[$w_ct];

                $api_params["Contacts"][$ct] = [
                    'name'              => $whois[$w_ct]["FirstName"] ?? '',
                    'surname'           => $whois[$w_ct]["LastName"] ?? '',
                    'fullname'          => $whois[$w_ct]["Name"] ?? '',
                    'company'           => $whois[$w_ct]["Company"] ?? '',
                    'emailaddr'         => $whois[$w_ct]["EMail"] ?? '',
                    'address1'          => $whois[$w_ct]["AddressLine1"] ?? '',
                    'address2'          => $whois[$w_ct]["AddressLine2"] ?? '',
                    'city'              => $whois[$w_ct]["City"] ?? '',
                    'state'             => $whois[$w_ct]["State"] ?? '',
                    'zip'               => $whois[$w_ct]["ZipCode"] ?? '',
                    'country'           => $whois[$w_ct]["Country"] ?? '',
                    'telnocc'           => $whois[$w_ct]["PhoneCountryCode"] ?? '',
                    'telno'             => $whois[$w_ct]["Phone"] ?? '',
                    'faxnocc'           => $whois[$w_ct]["FaxCountryCode"] ?? '',
                    'faxno'             => $whois[$w_ct]["Fax"] ?? '',
                ];
            }

            $api_params["Dns"] =  array_values($dns); // ['ns1.example.com','ns2.example.com'] etc..

            // Whois Privacy Protection Enable
            if($wprivacy) $api_params["PrivacyProtection"] = 'enable';


            // This result should return if the domain name was registered successfully or was previously registered.

            #$response       = $this->api->register_domain($api_params);
            $response        = [
                'status' => "error",
                'message' => "test error message",
                'entity_id' => 0,
                'PrivacyProtection' => [
                    'status' => "active",
                ],
            ];

            if($response && $response['status'] == 'successful')
            {

                $returnData = [
                    'status' => "SUCCESS",
                    'config' => [
                        'entityID' => $response['entity_id'],
                    ],
                ];

                if($wprivacy)
                    $returnData["whois_privacy"] = ['status' => $response['PrivacyProtection']['status'] == 'active','message' => NULL];

                return $returnData;
            }
            else
            {
                $this->error = $response['message'];
                return false;
            }
        }

        // Required function
        public function transfer($domain='',$sld='',$tld='',$year=1,$dns=[],$whois=[],$wprivacy=false,$eppCode=''){
            return $this->register($domain,$sld,$tld,$year,$dns,$whois,$wprivacy,$eppCode);
        }

        // Required function
        public function renewal($params=[],$domain='',$sld='',$tld='',$year=1,$oduedate='',$nduedate=''){
            $domain   = idn_to_ascii($domain,0,INTL_IDNA_VARIANT_UTS46);
            $sld      = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);
            
            
            // Successful: true, Failed: false
            return true;
        }

        // Required function
        public function ModifyDns($params=[],$dns=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            if($dns) foreach($dns AS $i=>$dn) $dns[$i] = idn_to_ascii($dn,0,INTL_IDNA_VARIANT_UTS46);

            $modifyDns  = $this->api->modify_dns($domain,$dns);
            if(!$modifyDns){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        // Optional function
        public function CNSList($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return [
                [
                    'ns' => "ns1.example.com",
                    'ip' => "215.104.29.144",
                ],
                [
                    'ns' => "ns2.example.com",
                    'ip' => "177.19.63.247",
                ],
                [
                    'ns' => "ns3.example.com",
                    'ip' => "216.186.233.125",
                ],
                [
                    'ns' => "ns4.example.com",
                    'ip' => "208.203.151.89",
                ]
            ];
        }

        // Optional function (Required if the CNSList function is defined.)
        public function addCNS($params=[],$ns='',$ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);
            $ns         = idn_to_ascii($ns,0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */


            return ['ns' => $ns,'ip' => $ip];
        }

        // Optional function (Required if the CNSList function is defined.)
        public function ModifyCNS($params=[],$old=[],$new_ns='',$new_ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $old_ns      = idn_to_ascii($old["ns"],0,INTL_IDNA_VARIANT_UTS46);
            $new_ns      = idn_to_ascii($new_ns,0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return ['ns' => $new_ns,'ip' => $new_ip];
        }

        // Optional function (Required if the CNSList function is defined.)
        public function DeleteCNS($params=[],$ns='',$ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);
            $ns         = idn_to_ascii($ns,0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */
            
            return true;
        }

        // Required function
        public function ModifyWhois($params=[],$whois=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $convert_key = [
                'registrant'        => 'Owner',
                'administrative'    => 'Admin',
                'technical'         => 'Tech',
                'billing'           => 'Bill',
            ];

            $contact_types          = array_keys($convert_key);

            foreach($contact_types AS $w_ct)
            {
                $ct = $convert_key[$w_ct];
                
                $whois_data = [
                    'name'              => $whois[$w_ct]["FirstName"] ?? '',
                    'surname'           => $whois[$w_ct]["LastName"] ?? '',
                    'fullname'          => $whois[$w_ct]["Name"] ?? '',
                    'company'           => $whois[$w_ct]["Company"] ?? '',
                    'emailaddr'         => $whois[$w_ct]["EMail"] ?? '',
                    'address1'          => $whois[$w_ct]["AddressLine1"] ?? '',
                    'address2'          => $whois[$w_ct]["AddressLine2"] ?? '',
                    'city'              => $whois[$w_ct]["City"] ?? '',
                    'state'             => $whois[$w_ct]["State"] ?? '',
                    'zip'               => $whois[$w_ct]["ZipCode"] ?? '',
                    'country'           => $whois[$w_ct]["Country"] ?? '',
                    'telnocc'           => $whois[$w_ct]["PhoneCountryCode"] ?? '',
                    'telno'             => $whois[$w_ct]["Phone"] ?? '',
                    'faxnocc'           => $whois[$w_ct]["FaxCountryCode"] ?? '',
                    'faxno'             => $whois[$w_ct]["Fax"] ?? '',
                ];
                
                

                $modify = $this->api->modify_contact($domain,$ct,$whois_data);
                if(!$modify){
                    $this->error = $this->api->error;
                    return false;
                }
                
            }

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            
            return true;
        }

        // Required function
        public function isInactive($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }
            return $details["status"] !== "active" ? true : false;
        }

        // Required function
        public function ModifyTransferLock($params=[],$status=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $post = [
                'action'          => "change_transfer_lock",
                'domain'          => $domain,
                'status'          => $status == "enable" ? "locked" : "unlocked",
            ];
            // api request code here


            /*
            $this->error = "Error message here";
            return false;
            */

            return true;
        }

        // Required function (Return true if Whois privacy is not supported)
        public function modifyPrivacyProtection($params=[],$status=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify = $this->api->modify_whois_privacy($domain,$status == "enable" ? "true" : "false");
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        // Required function (Return true if Whois privacy is not supported)
        public function purchasePrivacyProtection($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $apply = $this->api->purchase_whois_privacy($domain);
            if(!$apply){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        // Optional function
        public function restore($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            // Failed:
            #$this->error = "Failed restore message";
            #return false;

            // Successful:
            return true;
        }

        // Optional function
        public function suspend($params=[]){
            return true;
        }

        // Optional function
        public function unsuspend($params=[]){
            return true;
        }

        // Optional function
        public function cancelled($params=[]){
            return true;
        }

        // Required function
        public function getAuthCode($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return 'testAuthCode123';
        }

        // Optional function
        public function modifyAuthCode($params=[],$authCode=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify         = $this->api->modify_AuthCode($domain,$authCode);
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        // Required function
        public function sync($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "error message here";
            return false;
            */

            $details            = [
                'creation_date'     => "2023-05-25",
                'expiration_date'   => "2024-05-25",
                'status'            => "Expired",
            ];

            $start              = DateManager::format("Y-m-d",$details["creation_date"]);
            $end                = DateManager::format("Y-m-d",$details["expiration_date"]);
            $status             = $details["status"];

            $return_data    = [
                'creationtime'  => $start,
                'endtime'       => $end,
                'status'        => "unknown",
            ];

            if($status == "Active")
                $return_data["status"] = "active";
            elseif($status == "Expired")
                $return_data["status"] = "expired";
            elseif($status == "Transferred-elsewhere")
                $return_data["status"] = "transferred";


            return $return_data;

        }

        // Required function
        public function transfer_sync($params=[])
        {
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            /* Failed Transfer:
            // Sample error message:
            $this->error = "Failed Transfer";
            return false;
            */

            // Waiting Transfer:
            $details            = [
                'creation_date'     => "2024-05-25",
                'expiration_date'   => "",
                'status'            => "Pending",
            ];

            /* Completed Transfer:
            $details            = [
                'creation_date'     => "2024-05-25",
                'expiration_date'   => "2025-05-25",
                'status'            => "Completed",
            ];
            */

            $status             = $details["status"];


            $return_data    = [
                'creationtime'  => $details["creation_date"],
                'endtime'       => $details["expiration_date"],
                'status'        => $status == "Completed" ? "active" : "pending",
            ];


            return $return_data;

        }

        // Optional function
        public function get_info($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return [
                'creation_time'         => "2023-05-25", // Required
                'end_time'              => "2024-05-25", // Required
                'ns1'                   => "ns1.example.com", // Required
                'ns2'                   => "ns2.example.com", // Required
                'ns3'                   => "ns3.example.com", // Optional
                'ns4'                   => "ns4.example.com", // Optional
                'whois'                 => [ // Required
                    'registrant'        => [
                        'FirstName'         => 'John',
                        'LastName'          => 'Doe',
                        'Name'              => 'John Doe',
                        'Company'           => 'WISECP LLC',
                        'EMail'             => 'info@example.com',
                        'Country'           => 'US',
                        'City'              => 'Newark',
                        'State'             => 'Delaware',
                        'AddressLine1'      => '112 Capitol Trail Suite A747',
                        'AddressLine2'      => '',
                        'ZipCode'           => '19711',
                        'PhoneCountryCode'  => '1',
                        'Phone'             => '0123456789',
                        'FaxCountryCode'    => '',
                        'Fax'               => '',
                    ],
                    'administrative'    => [
                        'FirstName'         => 'John',
                        'LastName'          => 'Doe',
                        'Name'              => 'John Doe',
                        'Company'           => 'WISECP LLC',
                        'EMail'             => 'info@example.com',
                        'Country'           => 'US',
                        'City'              => 'Newark',
                        'State'             => 'Delaware',
                        'AddressLine1'      => '112 Capitol Trail Suite A747',
                        'AddressLine2'      => '',
                        'ZipCode'           => '19711',
                        'PhoneCountryCode'  => '1',
                        'Phone'             => '0123456789',
                        'FaxCountryCode'    => '',
                        'Fax'               => '',
                    ],
                    'technical'         => [
                        'FirstName'         => 'John',
                        'LastName'          => 'Doe',
                        'Name'              => 'John Doe',
                        'Company'           => 'WISECP LLC',
                        'EMail'             => 'info@example.com',
                        'Country'           => 'US',
                        'City'              => 'Newark',
                        'State'             => 'Delaware',
                        'AddressLine1'      => '112 Capitol Trail Suite A747',
                        'AddressLine2'      => '',
                        'ZipCode'           => '19711',
                        'PhoneCountryCode'  => '1',
                        'Phone'             => '0123456789',
                        'FaxCountryCode'    => '',
                        'Fax'               => '',
                    ],
                    'billing'           => [
                        'FirstName'         => 'John',
                        'LastName'          => 'Doe',
                        'Name'              => 'John Doe',
                        'Company'           => 'WISECP LLC',
                        'EMail'             => 'info@example.com',
                        'Country'           => 'US',
                        'City'              => 'Newark',
                        'State'             => 'Delaware',
                        'AddressLine1'      => '112 Capitol Trail Suite A747',
                        'AddressLine2'      => '',
                        'ZipCode'           => '19711',
                        'PhoneCountryCode'  => '1',
                        'Phone'             => '0123456789',
                        'FaxCountryCode'    => '',
                        'Fax'               => '',
                    ],
                ],
                'whois_privacy'         => [ // Optional: (If whois privacy is never used, don't define this index.)
                    'status'            => "enable", // (enable or disable)
                    'end_time'          => "2024-05-25",
                ],
                'transferlock'          => true, // Required: (true or false)
            ];

        }

        // Optional function
        public function domains(){
            Helper::Load(["User"]);

            // You should adapt the response from your API here.
            $data = [
                [
                    "creation_date"     => "2024-05-01",
                    "expiry_date"       => "2025-05-01",
                    "domain"            => "example1.com",
                ],
                [
                    "creation_date"     => "2023-08-15",
                    "expiry_date"       => "2024-08-15",
                    "domain"            => "example2.net",
                ],
                [
                    "creation_date"     => "2022-11-20",
                    "expiry_date"       => "2023-11-20",
                    "domain"            => "example3.org",
                ],
                [
                    "creation_date"     => "2024-02-10",
                    "expiry_date"       => "2025-02-10",
                    "domain"            => "example4.co",
                ],
                [
                    "creation_date"     => "2021-07-05",
                    "expiry_date"       => "2022-07-05",
                    "domain"            => "example5.io",
                ],
            ];

            $result     = [];

            if($data)
            {
                foreach($data AS $res)
                {
                    $cdate      = $res["creation_date"];
                    $edate      = $res["expiry_date"];
                    $domain     = $res["domain"];

                    if($domain)
                    {
                        $order_id    = 0;
                        $user_data   = [];

                        $is_imported = WDB::select("id,owner_id AS user_id")->from("users_products");
                        $is_imported->where("type",'=',"domain","&&");
                        $is_imported->where("name",'=',$domain);
                        $is_imported = $is_imported->build() ? $is_imported->getAssoc() : false;
                        if($is_imported)
                        {
                            $order_id   = $is_imported["id"];
                            $user_data  =  User::getData($is_imported["user_id"],"id,full_name,company_name","array");
                        }


                        $result[] = [
                            'domain'            => $domain,
                            'creation_date'     => $cdate, // Format: YYYY-MM-DD
                            'end_date'          => $edate, // Format: YYYY-MM-DD
                            'order_id'          => $order_id,
                            'user_data'        => $user_data,
                        ];
                    }
                }
            }

            return $result;
        }

        // If your API service provides price information for domain extensions, you can use this function.
        public function cost_prices($type='domain'){

            // The amount information must be in accordance with the ‘cost-currency’ defined in config.php.

            return [
                'com' => [
                    'register' => 9.90,
                    'transfer' => 9.90,
                    'renewal' => 9.90,
                ],
                'net' => [
                    'register' => 9.90,
                    'transfer' => 9.90,
                    'renewal' => 9.90,
                ],
                'org' => [
                    'register' => 9.90,
                    'transfer' => 9.90,
                    'renewal' => 9.90,
                ],
            ];
        }

        //  If your API service provides the domain extension list, you can use this function.
        public function tlds()
        {

            // Use the following example for the error message.
            /*
            $this->error = "Error message here";
            return false;
            */

            // Example:
            return [
                'com' => [
                    'min_years' => 1,
                    'max_years' => 10,
                    'whois_privacy' => true,
                    'epp_code'      => true,
                    'dns_manage'    => true,
                    'price' => [
                        'register'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'renewal'   => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'transfer'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                'net' => [
                    'min_years' => 1,
                    'max_years' => 10,
                    'whois_privacy' => true,
                    'epp_code'      => true,
                    'dns_manage'    => true,
                    'price' => [
                        'register'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'renewal'   => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'transfer'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                'org' => [
                    'min_years' => 1,
                    'max_years' => 10,
                    'whois_privacy' => true,
                    'epp_code'      => true,
                    'dns_manage'    => true,
                    'price' => [
                        'register'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'renewal'   => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'transfer'  => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                'xa' => [
                    'min_years' => 1,
                    'max_years' => 5,
                    'whois_privacy' => false,
                    'epp_code'      => true,
                    'dns_manage'    => true,
                    'price' => [
                        'register'  => [
                            'amount' => 7.90,
                            'currency' => 'USD',
                        ],
                        'renewal'   => [
                            'amount' => 9.90,
                            'currency' => 'USD',
                        ],
                        'transfer'  => [
                            'amount' => 6.90,
                            'currency' => 'USD',
                        ],
                    ],
                ],
                'xb' => [
                    'min_years' => 1,
                    'max_years' => 5,
                    'whois_privacy' => true,
                    'epp_code'      => false,
                    'dns_manage'    => true,
                    'price' => [
                        'register'  => [
                            'amount' => 5.90,
                            'currency' => 'USD',
                        ],
                        'renewal'   => [
                            'amount' => 4.90,
                            'currency' => 'USD',
                        ],
                        'transfer'  => [
                            'amount' => 5.90,
                            'currency' => 'USD',
                        ],
                    ],
                ],
            ];
        }

        /*
         *  DNS Record Functions (Optional)
        */

        public function getDnsRecords()
        {
            $domain     = idn_to_ascii($this->order["options"]["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $result = [];

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            $result[] = [
                'identity'      => 12345, // Sample Dns Record Identity ID e.g : 12345
                'type'          => "A", // Record Type e.g : A or MX
                'name'          => "@", // Record Host
                'value'         => "192.168.1.1", // Record Value
                'ttl'           => 3600, // Record TTL
                'priority'      => '', // Record Priority
            ];

            $result[] = [
                'identity'      => 12346,
                'type'          => "MX",
                'name'          => "@",
                'value'         => "mail.example.com",
                'ttl'           => 14400,
                'priority'      => 10,
            ];

            $result[] = [
                'identity'      => 12347,
                'type'          => "CNAME",
                'name'          => "www",
                'value'         => "example.com",
                'ttl'           => 300,
                'priority'      => '',
            ];

            $result[] = [
                'identity'      => 12348,
                'type'          => "TXT",
                'name'          => "@",
                'value'         => "v=spf1 include:example.com ~all",
                'ttl'           => 86400,
                'priority'      => '',
            ];

            $result[] = [
                'identity'      => 12349,
                'type'          => "AAAA",
                'name'          => "@",
                'value'         => "2001:0db8:85a3:0000:0000:8a2e:0370:7334",
                'ttl'           => 7200,
                'priority'      => '',
            ];


            return $result;

        }

        public function addDnsRecord($type,$name,$value,$ttl,$priority)
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'type'          => $type,
                'host'          => str_replace("@","",$name),
                'address'       => $value,
                'ttl'           => $ttl,
                'distance'      => $priority,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        public function updateDnsRecord($type='',$name='',$value='',$identity='',$ttl='',$priority='')
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'identity'      => $identity,
                'type'          => $type,
                'host'          => str_replace("@","",$name),
                'address'       => $value,
                'ttl'           => $ttl,
                'distance'      => $priority,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */


            return true;
        }

        public function deleteDnsRecord($type='',$name='',$value='',$identity='')
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'identity'      => $identity,
                'type'          => $type,
                'host'          => str_replace("@","",$name),
                'address'       => $value
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        public function getDnsSecRecords()
        {
            $domain     = $this->order["options"]["domain"];
            $result     = [];

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            $result[] = [
                'identity'      => 20001, // Sample DNSSEC Record Identity ID e.g : 20001
                'digest'        => '49FD46E6C4B45C55D4AC', // Digest value in hexadecimal
                'key_tag'       => 12345, // Key Tag, a 16-bit value used to identify a DNSKEY record
                'digest_type'   => 2, // Digest Type (e.g., 1 = SHA-1, 2 = SHA-256)
                'algorithm'     => 8, // Algorithm (e.g., 8 = RSA/SHA-256)
            ];

            $result[] = [
                'identity'      => 20002,
                'digest'        => 'AE098BD9E6789F65CDE2', // Another sample Digest value
                'key_tag'       => 54321,
                'digest_type'   => 1, // Using SHA-1 for this example
                'algorithm'     => 5, // Algorithm (e.g., 5 = RSA/SHA-1)
            ];

            $result[] = [
                'identity'      => 20003,
                'digest'        => '56ACDDEF013453C6A457', // Sample Digest value
                'key_tag'       => 67890,
                'digest_type'   => 2, // Using SHA-256 for this example
                'algorithm'     => 10, // Algorithm (e.g., 10 = RSA/SHA-512)
            ];

            $result[] = [
                'identity'      => 20004,
                'digest'        => '23AB45CD6790ED54B9F1', // Another sample Digest value
                'key_tag'       => 11223,
                'digest_type'   => 4, // Digest Type (e.g., 4 = SHA-384)
                'algorithm'     => 13, // Algorithm (e.g., 13 = ECDSA Curve P-256 with SHA-256)
            ];

            $result[] = [
                'identity'      => 20005,
                'digest'        => 'AABBCCDDEEFF11223344', // Sample Digest value
                'key_tag'       => 33445,
                'digest_type'   => 3, // Digest Type (e.g., 3 = GOST R 34.11-94)
                'algorithm'     => 12, // Algorithm (e.g., 12 = GOST R 34.10-2001)
            ];


            return $result;

        }

        public function addDnsSecRecord($digest,$key_tag,$digest_type,$algorithm)
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'digest'        => $digest,
                'keyTag'        => $key_tag,
                'digestType'    => $digest_type,
                'alg'           => $algorithm,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */


            return true;
        }

        public function deleteDnsSecRecord($digest,$key_tag,$digest_type,$algorithm,$identity='')
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'id'            => $identity,
                'digest'        => $digest,
                'keyTag'        => $key_tag,
                'digestType'    => $digest_type,
                'alg'           => $algorithm,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        /*
         *  Domain and Mail Forwarding Functions (Optional)
        */

        public function getForwardingDomain()
        {
            $domain     = $this->order["options"]["domain"];

            // Domain Forwarding is none
            #return ['status' => false];

            // Domain Forwarding 301 redirect
            return [
                'status'    => true,
                'method'    => 301,
                'protocol'  => "https", // htp or https
                'domain'    => "example2.com",
            ];

        }

        public function setForwardingDomain($protocol='',$method='',$domain='')
        {

            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'protocol'      => $protocol,
                'address'       => $domain,
                'method'        => $method,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        public function cancelForwardingDomain()
        {
            $apply      = $this->api->cancelDomainForward([
                'domain'        => $this->order["options"]["domain"],
            ]);

            if(!$apply){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function getEmailForwards()
        {
            $domain     = $this->order["options"]["domain"];
            $result     = [];

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            $result[] = [
                'identity'      => 1234,
                'prefix'        => "info",
                'target'        => "info@gmail.com",
            ];

            $result[] = [
                'identity'      => 1235,
                'prefix'        => "dev",
                'target'        => "dev@gmail.com",
            ];

            $result[] = [
                'identity'      => 1236,
                'prefix'        => "bill",
                'target'        => "bill@gmail.com",
            ];


            return $result;
        }

        public function addForwardingEmail($prefix='',$target='')
        {
            $sample_params = [
                'domain'       => $this->order["options"]["domain"],
                'email'        => $prefix,
                'forward'      => $target,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        public function updateForwardingEmail($prefix='',$target='',$target_new='',$identity='')
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'email'         => $prefix,
                'forward'       => $target_new,
                'identity'      => $identity,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

        public function deleteForwardingEmail($prefix='',$target='',$identity='')
        {
            $sample_params = [
                'domain'        => $this->order["options"]["domain"],
                'email'         => $prefix,
                'identity'      => $identity,
            ];

            // Send the $sample_params variable to the API.

            /*
            // Sample error message:
            $this->error = "Error message";
            return false;
            */

            return true;
        }

    }
<?php

    class ExampleRegistrarModule {
        public $api                = false;
        public $config             = [];
        public $lang               = [];
        public  $error              = NULL;
        public  $whidden            = [];
        public $order              = [];

        function __construct($args=[]){

            $this->config   = Modules::Config("Registrars",__CLASS__);
            $this->lang     = Modules::Lang("Registrars",__CLASS__);

            if(!class_exists("ExampleRegistrarModule_API")){
                // Calling API files
                include __DIR__.DS."api.php";
            }

            if(isset($this->config["settings"]["whidden-amount"])){
                $whidden_amount   = $this->config["settings"]["whidden-amount"];
                $whidden_currency = $this->config["settings"]["whidden-currency"];
                $this->whidden["amount"] = $whidden_amount;
                $this->whidden["currency"] = $whidden_currency;
            }

            // Set API Credentials 

            $username   = $this->config["settings"]["username"];
            $password   = $this->config["settings"]["password"];
            $password   = Crypt::decode($password,Config::get("crypt/system"));

            $sandbox    = (bool)$this->config["settings"]["test-mode"];
            $this->api  =  new ExampleRegistrarModule_API($sandbox);

            $this->api->set_credentials($username,$password);

        }

        public function set_order($order=[]){
            $this->order = $order;
            return $this;
        }

        private function setConfig($username,$password,$sandbox){
            $this->config["settings"]["username"]   = $username;
            $this->config["settings"]["password"]   = $password;
            $this->config["settings"]["test-mode"]  = $sandbox;
            $this->api = new ExampleRegistrarModule_API($sandbox);

            $this->api->set_credentials($username,$password);

        }

        public function testConnection($config=[]){
            $username   = $config["settings"]["username"];
            $password   = $config["settings"]["password"];
            $sandbox    = $config["settings"]["test-mode"];

            if(!$username || !$password){
                $this->error = $this->lang["error6"];
                return false;
            }

            $password  = Crypt::decode($password,Config::get("crypt/system"));

            $this->setConfig($username,$password,$sandbox);

            if(!$this->api->login()){
                $this->error = $this->api->error;
                return false;
            }
            
            return true;
        }


        public function questioning($sld=NULL,$tlds=[]){
            if($sld == '' || empty($tlds)){
                $this->error = $this->lang["error2"];
                return false;
            }
            $sld = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);
            if(!is_array($tlds)) $tlds = [$tlds];

            $servers            = Registrar::whois_server($tlds);

            $result = [];
            foreach ($tlds AS $t){
                if(isset($servers[$t]["host"]) && isset($servers[$t]["available_pattern"]))
                    $questioning = Registrar::questioning($sld,$t,$servers[$t]["host"],43,$servers[$t]["available_pattern"]);
                else
                    $questioning = false;

                $result[$t] = ['status' => $questioning['status']];

            }
            return $result;
        }

        public function register($domain='',$sld='',$tld='',$year=1,$dns=[],$whois=[],$wprivacy=false){
            $domain   = idn_to_ascii($domain,0,INTL_IDNA_VARIANT_UTS46);
            $sld      = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);

            // This result should return if the domain name was registered successfully or was previously registered.

            $returnData = [
                'status' => "SUCCESS",
                'config' => [
                    'entityID' => 1,
                ],
            ];

            if($wprivacy) $rdata["whois_privacy"] = ['status' => true,'message' => NULL];

            return $returnData;
        }

        public function transfer($domain='',$sld='',$tld='',$year=1,$dns=[],$whois=[],$wprivacy=false,$eppCode=''){
            $domain   = idn_to_ascii($domain,0,INTL_IDNA_VARIANT_UTS46);
            $sld      = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);

            

            // This result should return if the domain name was registered successfully or was previously registered.

            $returnData = [
                'status' => "SUCCESS",
                'config' => [
                    'entityID' => 1,
                ],
            ];

            if($wprivacy) $rdata["whois_privacy"] = ['status' => true,'message' => NULL];

            return $returnData;
        }

        public function renewal($params=[],$domain='',$sld='',$tld='',$year=1,$oduedate='',$nduedate=''){
            $domain   = idn_to_ascii($domain,0,INTL_IDNA_VARIANT_UTS46);
            $sld      = idn_to_ascii($sld,0,INTL_IDNA_VARIANT_UTS46);
            
            
            // Successful: true, Failed: false
            return true;
        }

        public function cost_prices($type='domain'){
            if(!$this->config["settings"]["adp"]) return false;

            $prices    = $this->api->cost_prices();
            if(!$prices){
                $this->error = $this->api->error;
                return false;
            }

            $result = [];

            if($type == "domain"){
                foreach($prices AS $name=>$val){
                    $result[$name] = [
                        'register' => $val["register"],
                        'transfer' => $val["transfer"],
                        'renewal'  => $val["renewal"],
                    ];
                }
            }
            
            return $result;
        }

        public function NsDetails($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            $returns = [];

            if(isset($details["ns1"])) $returns["ns1"] = $details["ns1"];
            if(isset($details["ns2"])) $returns["ns2"] = $details["ns2"];
            if(isset($details["ns3"])) $returns["ns3"] = $details["ns3"];
            if(isset($details["ns4"])) $returns["ns4"] = $details["ns4"];
            return $returns;
        }

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

        public function CNSList($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $get_list    = $this->api->get_child_nameservers($domain);
            if(!$get_list && $this->api->error){
                $this->error = $this->api->error;
                return false;
            }

            $data     = [];
            $i        = 0;

           if($get_list){
                foreach($get_list AS $row){
                    $i +=1;
                    $data[$i] = ['ns' => $row["nameserver"],'ip' => $row["ip_address"]];
                }
           }
            return $data;
        }

        public function addCNS($params=[],$ns='',$ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);
            $ns         = idn_to_ascii($ns,0,INTL_IDNA_VARIANT_UTS46);

            $addCNS = $this->api->add_child_nameserver($domain,$ns,$ip);
            if(!$addCNS){
                $this->error = $this->api->error;
                return false;
            }

            return ['ns' => $ns,'ip' => $ip];
        }
        
        public function ModifyCNS($params=[],$old=[],$new_ns='',$new_ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $old_ns      = idn_to_ascii($old["ns"],0,INTL_IDNA_VARIANT_UTS46);
            $new_ns      = idn_to_ascii($new_ns,0,INTL_IDNA_VARIANT_UTS46);

            $modify     = $this->api->modify_child_nameserver($domain,$old_ns,$new_ns,$new_ip);
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function DeleteCNS($params=[],$ns='',$ip=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);
            $ns         = idn_to_ascii($ns,0,INTL_IDNA_VARIANT_UTS46);

            $delete     = $this->api->delete_child_nameserver($domain,$ns,$ip);
            if(!$delete){
                $this->error = $this->api->error;
                return false;
            }
            
            return true;
        }


        public function ModifyWhois($params=[],$whois=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify = $this->api->modify_contact($domain,$whois);
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function getWhoisPrivacy($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            return $details["is_privacy"] == "on" ? "active" : "passive";
        }

        public function getTransferLock($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            return $details["transfer_lock"] == "on" ? true : false;
        }

        public function isInactive($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }
            return $details["status"] !== "active" ? true : false;
        }

        public function ModifyTransferLock($params=[],$status=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify     = $this->api->modify_transfer_lock($domain,$status == "enable" ? "locked" : "unlocked");
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }
            return true;
        }

        public function modifyPrivacyProtection($params=[],$staus=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify = $this->api->modify_whois_privacy($domain,$status == "enable" ? "true" : "false");
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function purchasePrivacyProtection($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $apply = $this->api->purchase_whois_privacy($domain);
            if(!$apply){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function suspend($params=[]){
            return true;
        }
        public function unsuspend($params=[]){
            return true;
        }
        public function terminate($params=[]){
            return true;
        }

        public function getAuthCode($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            $authCode   = $details["AuthCode"];

            return $authCode;
        }

        public function modifyAuthCode($params=[],$authCode=''){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $modify         = $this->api->modify_AuthCode($domain,$authCode);
            if(!$modify){
                $this->error = $this->api->error;
                return false;
            }

            return true;
        }

        public function sync($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            $start              = DateManager::format("Y-m-d",$details["creation_date"]);
            $end                = DateManager::format("Y-m-d",$details["expiration_date"]);
            $status             = $details["status"];

            $return_data    = [
                'creationtime'  => $start,
                'endtime'       => $end,
                'status'        => "unknown",
            ];

            if($status == "active"){
                $return_data["status"] = "active";
            }elseif($status == "expired")
                $return_data["status"] = "expired";

            return $return_data;

        }

        public function transfer_sync($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            $start              = DateManager::format("Y-m-d",$details["creation_date"]);
            $end                = DateManager::format("Y-m-d",$details["expiration_date"]);
            $status             = $details["status"];

            $return_data    = [
                'creationtime'  => $start,
                'endtime'       => $end,
                'status'        => "unknown",
            ];

            if($status == "active"){
                $return_data["status"] = "active";
            }elseif($status == "expired")
                $return_data["status"] = "expired";

            return $return_data;

        }

        public function get_info($params=[]){
            $domain     = idn_to_ascii($params["domain"],0,INTL_IDNA_VARIANT_UTS46);

            $details    = $this->api->get_details($domain);
            if(!$details){
                $this->error = $this->api->error;
                return false;
            }

            $result             = [];

            $cdate              = DateManager::format("Y-m-d",$details["creation_date"]);
            $duedate            = DateManager::format("Y-m-d",$details["expiration_date"]);

            $wprivacy           = $details["is_privacy"] != "none" ? ($OrderDetails["is_privacy"] == "on") : "none";
            if($wprivacy && $wprivacy != "none"){
                $wprivacy_endtime_i   = isset($details["privacy_endtime"]) ? $details["privacy_endtime"] : "none";
                if($wprivacy_endtime_i && $wprivacy_endtime_i != "none")
                    $wprivacy_endtime   = DateManager::format("Y-m-d",$details["privacy_endtime"]);
            }

            $ns1                = isset($details["ns1"]) ? $details["ns1"] : false;
            $ns2                = isset($details["ns2"]) ? $details["ns2"] : false;
            $ns3                = isset($details["ns3"]) ? $details["ns3"] : false;
            $ns4                = isset($details["ns4"]) ? $details["ns4"] : false;
            $whois_data         = isset($details["registrant_contact"]) ? $details["registrant_contact"] : [];

            if($whois_data){
                $whois                  = [
                    'FirstName'         =>  $whois_data["name"],
                    'LastName'          =>  $whois_data["surname"],
                    'Name'              =>  $whois_data["fullname"],
                    'Company'           =>  $whois_data["company"] == 'N/A' ? "" : $whois_data["company"],
                    'EMail'             =>  $whois_data["emailaddr"],
                    'AddressLine1'      =>  $whois_data["address1"],
                    'AddressLine2'      =>  isset($whois_data["address2"]) ? $whois_data["address2"] : "",
                    'City'              =>  $whois_data["city"],
                    'State'             =>  isset($whois_data["state"]) ? $whois_data["state"] : '',
                    'ZipCode'           =>  $whois_data["zip"],
                    'Country'           =>  $whois_data["country"],
                    'PhoneCountryCode' => $whois_data["telnocc"],
                    'Phone'            => $whois_data["telno"],
                    'FaxCountryCode'   => isset($whois_data["faxnocc"]) ? $whois_data["faxnocc"] : "",
                    'Fax'              => isset($whois_data["faxno"]) ? $whois_data["faxno"] : "",
                ];
            }

            $result["creation_time"]    = $cdate;
            $result["end_time"]         = $duedate;

            if(isset($wprivacy) && $wprivacy != "none"){
                $result["whois_privacy"] = ['status' => $wprivacy ? "enable" : "disable"];
                if(isset($wprivacy_endtime) && $wprivacy_endtime) $result["whois_privacy"]["end_time"] = $wprivacy_endtime;
            }

            if(isset($ns1) && $ns1) $result["ns1"] = $ns1;
            if(isset($ns2) && $ns2) $result["ns2"] = $ns2;
            if(isset($ns3) && $ns3) $result["ns3"] = $ns3;
            if(isset($ns4) && $ns4) $result["ns4"] = $ns4;
            if(isset($whois) && $whois) $result["whois"] = $whois;

            $result["transferlock"] = $details["transfer_lock"] == "on";

            if(isset($details["child_nameservers"])){
                $CNSList = $details["child_nameservers"];
                $cnsx  = [];
                $i       = 0;
                foreach($CNSList AS $k=>$v){
                    $i+=1;
                    $cnsx[$i] = ['ns' => $k,'ip' => $v];
                }
                $result["cns"] = $cnsx;
            }

            return $result;

        }
        
        public function domains(){
            Helper::Load(["User"]);

            $data       = $this->api->get_domains();
            if(!$data && $this->api->error){
                $this->error = $this->api->error;
                return false;
            }

            $result     = [];

            if($data && is_array($data)){
                foreach($data AS $res){
                    $cdate      = isset($res["creation_date"]) ? DateManager::format("Y-m-d",$res["creation_date"]) : '';
                    $edate      = isset($res["expration_date"]) ? DateManager::format("Y-m-d",$res["expration_date"]) : '';
                    $domain     = isset($res["domain"]) ? $res["domain"] : '';
                    if($domain){
                        $domain      = idn_to_utf8($domain,0,INTL_IDNA_VARIANT_UTS46);
                        $order_id    = 0;
                        $user_data   = [];
                        $is_imported = Models::$init->db->select("id,owner_id AS user_id")->from("users_products");
                        $is_imported->where("type",'=',"domain","&&");
                        $is_imported->where("name",'=',$domain);
                        $is_imported = $is_imported->build() ? $is_imported->getAssoc() : false;
                        if($is_imported){
                            $order_id   = $is_imported["id"];
                            $user_data  =  User::getData($is_imported["user_id"],"id,full_name,company_name","array");
                        }

                        $result[] = [
                            'domain'            => $domain,
                            'creation_date'     => $cdate,
                            'end_date'          => $edate,
                            'order_id'          => $order_id,
                            'user_data'        => $user_data,
                        ];
                    }
                }
            }

            return $result;
        }
        
        public function import_domain($data=[]){
            $config     = $this->config;

            $imports = [];

            Helper::Load(["Orders","Products","Money"]);

            foreach($data AS $domain=>$datum){
                $domain_parse   = Utility::domain_parser("http://".$domain);
                $sld            = $domain_parse["host"];
                $tld            = $domain_parse["tld"];
                $user_id        = (int) $datum["user_id"];
                if(!$user_id) continue;
                $info           = $this->get_info([
                    'domain'    => $domain,
                    'name'      => $sld,
                    'tld'       => $tld,
                ]);
                if(!$info) continue;

                $user_data          = User::getData($user_id,"id,lang","array");
                $ulang              = $user_data["lang"];
                $locallang          = Config::get("general/local");
                $productID          = Models::$init->db->select("id")->from("tldlist")->where("name","=",$tld);
                $productID          = $productID->build() ? $productID->getObject()->id : false;
                if(!$productID) continue;
                $productPrice       = Products::get_price("register","tld",$productID);
                $productPrice_amt   = $productPrice["amount"];
                $productPrice_cid   = $productPrice["cid"];
                $start_date         = $info["creation_time"];
                $end_date           = $info["end_time"];
                $year               = 1;

                $options            = [
                    "established"         => true,
                    "group_name"          => Bootstrap::$lang->get_cm("website/account_products/product-type-names/domain",false,$ulang),
                    "local_group_name"    => Bootstrap::$lang->get_cm("website/account_products/product-type-names/domain",false,$locallang),
                    "category_id"         => 0,
                    "domain"              => $domain,
                    "name"                => $sld,
                    "tld"                 => $tld,
                    "dns_manage"          => true,
                    "whois_manage"        => true,
                    "transferlock"        => $info["transferlock"],
                    "cns_list"            => isset($info["cns"]) ? $info["cns"] : [],
                    "whois"               => isset($info["whois"]) ? $info["whois"] : [],
                ];

                if(isset($info["whois_privacy"]) && $info["whois_privacy"]){
                    $options["whois_privacy"] = $info["whois_privacy"]["status"] == "enable";
                    $wprivacy_endtime   = DateManager::ata();
                    if(isset($info["whois_privacy"]["end_time"]) && $info["whois_privacy"]["end_time"]){
                        $wprivacy_endtime = $info["whois_privacy"]["end_time"];
                        $options["whois_privacy_endtime"] = $wprivacy_endtime;
                    }
                }

                if(isset($info["ns1"]) && $info["ns1"]) $options["ns1"] = $info["ns1"];
                if(isset($info["ns2"]) && $info["ns2"]) $options["ns2"] = $info["ns2"];
                if(isset($info["ns3"]) && $info["ns3"]) $options["ns3"] = $info["ns3"];
                if(isset($info["ns4"]) && $info["ns4"]) $options["ns4"] = $info["ns4"];



                $order_data             = [
                    "owner_id"          => (int) $user_id,
                    "type"              => "domain",
                    "product_id"        => (int) $productID,
                    "name"              => $domain,
                    "period"            => "year",
                    "period_time"       => (int) $year,
                    "amount"            => (float) $productPrice_amt,
                    "total_amount"      => (float) $productPrice_amt,
                    "amount_cid"        => (int) $productPrice_cid,
                    "status"            => "active",
                    "cdate"             => $start_date,
                    "duedate"           => $end_date,
                    "renewaldate"       => DateManager::Now(),
                    "module"            => $config["meta"]["name"],
                    "options"           => Utility::jencode($options),
                    "unread"            => 1,
                ];

                $insert                 = Orders::insert($order_data);
                if(!$insert) continue;

                if(isset($options["whois_privacy"])){
                    $amount = Money::exChange($this->whidden["amount"],$this->whidden["currency"],$productPrice_cid);
                    $start  = DateManager::Now();
                    $end    = isset($wprivacy_endtime) ? $wprivacy_endtime : DateManager::ata();
                    Orders::insert_addon([
                        'invoice_id' => 0,
                        'owner_id' => $insert,
                        "addon_key"     => "whois-privacy",
                        'addon_id' => 0,
                        'addon_name' => Bootstrap::$lang->get_cm("website/account_products/whois-privacy",false,$ulang),
                        'option_id'  => 0,
                        "option_name"   => Bootstrap::$lang->get("needs/iwwant",$ulang),
                        'period'       => 1,
                        'period_time'  => "year",
                        'status'       => "active",
                        'cdate'        => $start,
                        'renewaldate'  => $start,
                        'duedate'      => $end,
                        'amount'       => $amount,
                        'cid'          => $productPrice_cid,
                        'unread'       => 1,
                    ]);
                }
                $imports[] = $order_data["name"]." (#".$insert.")";
            }
            
            if($imports){
                $adata      = UserManager::LoginData("admin");
                User::addAction($adata["id"],"alteration","domain-imported",[
                    'module'   => $config["meta"]["name"],
                    'imported'  => implode(", ",$imports),
                ]);
            }

            return $imports;
        }

        public function apply_import_tlds(){

            $cost_cid           = $this->config["settings"]["cost-currency"]; // Currency ID

            $prices             = $this->cost_prices();
            if(!$prices) return false;

            Helper::Load(["Products","Money"]);

            $profit_rate        = Config::get("options/domain-profit-rate");

            foreach($prices AS $name=>$val){
                $api_cost_prices    = [
                    'register' => $val["register"],
                    'transfer' => $val["transfer"],
                    'renewal'  => $val["renewal"],
                ];

                $paperwork      = 0;
                $epp_code       = 1;
                $dns_manage     = 1;
                $whois_privacy  = 1;
                $module         = $this->config["meta"]["name"];

                $check          = Models::$init->db->select()->from("tldlist")->where("name","=",$name);

                if($check->build()){
                    $tld        = $check->getAssoc();
                    $pid        = $tld["id"];

                    $reg_price = Products::get_price("register","tld",$pid);
                    $ren_price = Products::get_price("renewal","tld",$pid);
                    $tra_price = Products::get_price("transfer","tld",$pid);

                    $tld_cid    = $reg_price["cid"];


                    $register_cost  = Money::deformatter($api_cost_prices["register"]);
                    $renewal_cost   = Money::deformatter($api_cost_prices["renewal"]);
                    $transfer_cost  = Money::deformatter($api_cost_prices["transfer"]);

                    // ExChanges
                    $register_cost  = Money::exChange($register_cost,$cost_cid,$tld_cid);
                    $renewal_cost   = Money::exChange($renewal_cost,$cost_cid,$tld_cid);
                    $transfer_cost  = Money::exChange($transfer_cost,$cost_cid,$tld_cid);


                    $reg_profit     = Money::get_discount_amount($register_cost,$profit_rate);
                    $ren_profit     = Money::get_discount_amount($renewal_cost,$profit_rate);
                    $tra_profit     = Money::get_discount_amount($transfer_cost,$profit_rate);

                    $register_sale  = $register_cost + $reg_profit;
                    $renewal_sale   = $renewal_cost + $ren_profit;
                    $transfer_sale  = $transfer_cost + $tra_profit;

                    Products::set("domain",$pid,[
                        'paperwork'         => $paperwork,
                        'epp_code'          => $epp_code,
                        'dns_manage'        => $dns_manage,
                        'whois_privacy'     => $whois_privacy,
                        'register_cost'     => $register_cost,
                        'renewal_cost'      => $renewal_cost,
                        'transfer_cost'     => $transfer_cost,
                        'module'            => $module,
                    ]);

                    Models::$init->db->update("prices",[
                        'amount' => $register_sale,
                        'cid'    => $tld_cid,
                    ])->where("id","=",$reg_price["id"])->save();


                    Models::$init->db->update("prices",[
                        'amount' => $renewal_sale,
                        'cid'    => $tld_cid,
                    ])->where("id","=",$ren_price["id"])->save();


                    Models::$init->db->update("prices",[
                        'amount' => $transfer_sale,
                        'cid'    => $tld_cid,
                    ])->where("id","=",$tra_price["id"])->save();

                }
                else{

                    $tld_cid    = $cost_cid;

                    $register_cost  = Money::deformatter($api_cost_prices["register"]);
                    $renewal_cost   = Money::deformatter($api_cost_prices["renewal"]);
                    $transfer_cost  = Money::deformatter($api_cost_prices["transfer"]);


                    $reg_profit     = Money::get_discount_amount($register_cost,$profit_rate);
                    $ren_profit     = Money::get_discount_amount($renewal_cost,$profit_rate);
                    $tra_profit     = Money::get_discount_amount($transfer_cost,$profit_rate);

                    $register_sale  = $register_cost + $reg_profit;
                    $renewal_sale   = $renewal_cost + $ren_profit;
                    $transfer_sale  = $transfer_cost + $tra_profit;

                    $insert                 = Models::$init->db->insert("tldlist",[
                        'status'            => "inactive",
                        'cdate'             => DateManager::Now(),
                        'name'              => $name,
                        'paperwork'         => $paperwork,
                        'epp_code'          => $epp_code,
                        'dns_manage'        => $dns_manage,
                        'whois_privacy'     => $whois_privacy,
                        'currency'          => $tld_cid,
                        'register_cost'     => $register_cost,
                        'renewal_cost'      => $renewal_cost,
                        'transfer_cost'     => $transfer_cost,
                        'module'            => $module,
                    ]);

                    if($insert){
                        $tld_id         = Models::$init->db->lastID();

                        Models::$init->db->insert("prices",[
                            'owner'     => "tld",
                            'owner_id'  => $tld_id,
                            'type'      => 'register',
                            'amount'    => $register_sale,
                            'cid'       => $tld_cid,
                        ]);


                        Models::$init->db->insert("prices",[
                            'owner'     => "tld",
                            'owner_id'  => $tld_id,
                            'type'      => 'renewal',
                            'amount'    => $renewal_sale,
                            'cid'       => $tld_cid,
                        ]);


                        Models::$init->db->insert("prices",[
                            'owner'     => "tld",
                            'owner_id' => $tld_id,
                            'type'      => 'transfer',
                            'amount'    => $transfer_sale,
                            'cid'       => $tld_cid,
                        ]);
                    }

                }
            }
            return true;
        }

    }
<?php
    class ExampleRegistrarModule_API {
        private $test_mode      = false;
        private $username       = NULL;
        private $password       = NULL;
        public  $error          = NULL;

        function __construct($test_mode=false){
            $this->test_mode    = $test_mode;
        }

        public function set_credentials($username='',$password=NULL){
            $this->username      = $username;
            $this->password      = $password;
        }

    }
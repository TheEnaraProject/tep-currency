<?php
    /* File : rest.class.php
    */
    class REST {
         
        //public $_allow = array();
        private $_content_type = "application/json";       // default content_type is JSON
        private $_content_encoding = "";		
        private $_request_headers = array();       
        private $_method = "";
        private $_query_string = "";
        private $_query_path = "";      
        private $_code = 200;
		private $_authorisation_key = "";

        public $_request = array();
		
        public function __construct(){
            ini_set("zlib.output_compression", "On");
            $this->initHeaders();
            $this->inputs();
        }
                  
        private function _response($data,$status){
            $this->_code = ($status)?$status:200;
            $this->set_headers();
            if($this->_content_type == "application/json"){
              echo json_encode($data);
              }elseif($this->_content_type == "application/msgpack"){
                echo msgpack_pack($this->to_array($data));   // Ensure that it is unpacked as an array at the other end
                }else{
                  echo serialize($data);
                  }
            exit;
        }
         
        private function get_status_message(){
            $status = array(
                        100 => 'Continue',  
                        101 => 'Switching Protocols',  
                        200 => 'OK',
                        201 => 'Created',  
                        202 => 'Accepted',  
                        203 => 'Non-Authoritative Information',  
                        204 => 'No Content',  
                        205 => 'Reset Content',  
                        206 => 'Partial Content',  
                        300 => 'Multiple Choices',  
                        301 => 'Moved Permanently',  
                        302 => 'Found',  
                        303 => 'See Other',  
                        304 => 'Not Modified',  
                        305 => 'Use Proxy',  
                        306 => '(Unused)',  
                        307 => 'Temporary Redirect',  
                        400 => 'Bad Request',  
                        401 => 'Unauthorized',  
                        402 => 'Payment Required',  
                        403 => 'Forbidden',  
                        404 => 'Not Found',  
                        405 => 'Method Not Allowed',  
                        406 => 'Not Acceptable',  
                        407 => 'Proxy Authentication Required',  
                        408 => 'Request Timeout',  
                        409 => 'Conflict',  
                        410 => 'Gone',  
                        411 => 'Length Required',  
                        412 => 'Precondition Failed',  
                        413 => 'Request Entity Too Large',  
                        414 => 'Request-URI Too Long',  
                        415 => 'Unsupported Media Type',  
                        416 => 'Requested Range Not Satisfiable',  
                        417 => 'Expectation Failed',  
                        500 => 'Internal Server Error',  
                        501 => 'Not Implemented',  
                        502 => 'Bad Gateway',  
                        503 => 'Service Unavailable',  
                        504 => 'Gateway Timeout',  
                        505 => 'HTTP Version Not Supported');
            return ($status[$this->_code])?$status[$this->_code]:$status[500];
        }

        private function msg_unpack($data, $content_type, $encoding_type){
            $encoding_type == "gzip" ? $data = gzdecode($data) : FALSE;
            if($content_type == "application/msgpack"){
              $arrayObject = new ArrayObject(msgpack_unpack($data));
              $array = $arrayObject->getArrayCopy();
              return $array;
              }
              else{              
                return json_decode($data, TRUE);
              }
        }
        
        private function initHeaders(){
        $this->_request_headers = apache_request_headers();
        $this->_request['request'] = $this->cleanInputs($_REQUEST['request']);
        $this->_query_path = explode('/', parse_url($this->_request['request'], PHP_URL_PATH));
        $this->_query_string = $this->cleanInputs($_SERVER['QUERY_STRING']);
        $this->_method = $_SERVER['REQUEST_METHOD'];
        isset($this->_request_headers['Content-Type']) ? $this->_content_type = $this->_request_headers['Content-Type'] : $this->_content_type;
        isset($this->_request_headers['Content-Encoding']) ? $this->_content_encoding = $this->_request_headers['Content-Encoding'] : $this->_content_encoding;
        isset($this->_request_headers['Authorization']) ? $this->_authorisation_key = $this->_request_headers['Authorization'] : $this->_authorisation_key;		
        $this->_version = $this->get_query_path(0);
        }
         
        private function cleanInputs($data){
            $clean_input = array();
            if(is_array($data)){
                foreach($data as $k => $v){
                    $clean_input[$k] = $this->cleanInputs($v);
                }
            }else{
                if(get_magic_quotes_gpc()){
                    $data = trim(stripslashes($data));
                }
                $data = strip_tags($data);
                $clean_input = trim($data);
            }
            return $clean_input;
        }       
         
        private function set_headers(){
            header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
            header("Content-Type:".$this->_content_type);
        }

        private function inputs(){
            switch($this->get_request_method()){
                
                case "POST":
                    $this->_request['args'] = $this->msg_unpack(file_get_contents("php://input", TRUE), $this->_content_type, $this->_content_encoding);
                    break;
                //case "DELETE":      // Not using DELETE in our API and it is just a GET anyway.
                case "GET":
                    $this->_request['args'] = $this->cleanInputs($_GET);
                    unset($this->_request['args']['request']); // remove apache GET rewrite request from GET
                    break;
                    //case "PUT":   // Not using PUT's at the moment as PUT is essentially just a POST with a PUT method
                    //$this->_request['args'] = $this->msg_unpack(file_get_contents("php://input", TRUE), $this->_content_type);
                    //break;
                default:
                    $this->response($this->get_request_method().' method not currently supported',406);
                    break;
            }
        }
               
// public methods

        public function to_array($data){
/*          $arrayObject = new ArrayObject($data);
          $array = $arrayObject->getArrayCopy();
          return $array;
*/ // Doesn't remove private/protected objects.
          return json_decode(json_encode($data), true);
        }

        public function response($data,$status){
          return $this->_response($data,$status);
         }
                  
        public function get_query_path($path_level){
            if(!isset($path_level)){return FALSE;}
              else{              
                return $this->_query_path[$path_level];
              }
        }
        
        public function get_query_string(){        
          return $this->_query_string;
        }

        public function get_request_method(){
            return $this->_method;
        }
        
        public function get_request(){
            return $this->_request;
        }
                         
        public function get_authorisation_key(){
            return $this->_authorisation_key;
        }
						 
}   
?>
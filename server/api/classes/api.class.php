<?php
   
    spl_autoload_register(function ($class_name) {
    require strtolower($class_name) . '.class.php';
	});


global $dbc;
      
class API extends REST {
     
   // public $data = "";   
    private $db = NULL;
 
    public function __construct(){
        parent::__construct();              // Init parent contructor
        $this->dbConnect();                 // Initiate Database connection
        $this->initRequest();
}
      
    private function initRequest(){
        //init functions here if needed
        //$this->requestData = $this->_request;
      }
     
    private function dbConnect(){
        $this->db = new Database();         // Create new DB object to use for API access
    }
    
    private function rstrstr($haystack,$needle){
        return substr($haystack, 0,strpos($haystack, $needle));
    }     
    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
    public function processApi(){
           //$this->__autoload('USER');
          //$testing = new USER(); 
         // $testing->getUserPassword('Admin');      
           
      //  $is_admin = ($user['permissions'] == 'admin') ? true : false;
        //echo "TST:".$this->get_query_path(0);        
        switch ($this->get_query_path(1)) {
          case "user": // Call our user level functions
            //echo "User functions \n";
            $this->runUserApp($this->get_query_path(2));
            break;
          case "server":  // Call our server functions
            echo "Server functions";
            $this->runServerApp();
            break;
          case "config":  // Call our config functions
           echo "Config functions";
           $this->runConfigApp();
           break;
        }
        
        //$test1 = strtok(parse_url($_REQUEST['request'], PHP_URL_PATH),"/");
        //$func = !empty($res[2]);
/*        if((int)method_exists($this,$level1path) > 0)
            $this->$level1path();
        else{
            $resp['requestData'] = $this->requestData;
            $resp['_SERVER'] = $_SERVER;
            $resp['_GET'] = $_GET;
            $resp['_POST'] = $_POST;
            $resp['level1path'] = $level1path;
            $resp['level2path'] = $level2path;
            $resp['level3path'] = $level3path;
            $resp['level4path'] = $level4path;
            $this->response(json_encode($resp),200);
            $this->response('Error code 404, Page not found',404);   // If the method not exist with in this class, response would be "Page not found".
            //var_dump($_REQUEST);
            }
            */
}
         
// API Routes Defined Here

    private function runUserApp($func){

        $user = new USER($this->db);  // Create a USER object to undertake the user functions
            
        switch ($func) {
          case "login": // User Login Function            
            if(isset($this->_request['args']['username']) && isset($this->_request['args']['password'])){
              $this->response($user->userLogin($this->_request['args']['username'], $this->_request['args']['password']), 200);
              }
              else
               { 
                  $error['_ERROR'] = "Username or Password were missing";
                  $this->response($error, 400);
               }
               
            break;
          case "adduser":  // Add a new user
		  print_r($this);
			if($user->validateToken($this->get_authorisation_key())) //validateUserToken
			{
				if(isset($this->_request['args']['username']) && isset($this->_request['args']['password'])){
					//$this->response($user->addUser($this->_request['args']['username'], $this->_request['args']['password']), 200);
			//check if username, password and name are set
            //check if the name or username exists already if so return error
            //Add the user and return TRUE if successful

			}
              else
               {
                  $error['_ERROR'] = "Username or Password were missing"; 
                  $this->response($error,400);
               }
            break;
          case "deleteuser":  // Delete a user
            //validateUserToken
            //check if username and / or name are set
            //check if the name or username exists and
            //detete the user and return TRUE if successful or FALSE if we fail
           break;
          case "updatetoken":  // get a new token
            //validateUserToken   //validate existing token - must be before time or have to re-login
            //check if the token exists and
            //create a new token for the user and a new expiry date/time and return TRUE if successful
           break;

        }
            
            
}      
private function test(){    
    // Cross validation if the request method is GET else it will return "Not Acceptable" status
    if($this->get_request_method() != "GET"){
        $this->response('',406);
    }
    $myDatabase= $this->db;// variable to access your database
    $param=$this->_request['var'];
    // If success everythig is good send header as "OK" return param
    $this->response($param, 200);    
}
    
}
 
?>
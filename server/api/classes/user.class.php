<?php
/*
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(50) NOT NULL,
  `permissions` tinyint(4) DEFAULT NULL,
  `token` char(56) DEFAULT NULL,
  `token_expire` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `username` (`username`,`token`)
) ENGINE=InnoDB
*/     
//require_once("api.class.php");

class USER {

  private $db;

  public function __construct($database){
        $this->db = $database;       
    }
    
  public function __destruct() {
       $this->db=null;
   }

  // Getters / Setters
  private function _setTokenExpiration($token, $tokenExpiration){
        $this->db->query("UPDATE `tbl_user` SET token_expire = :token_expire WHERE `token` = :token");
        $this->db->bind(':token_expire', $tokenExpiration);
        $this->db->bind(':token', $token);      
        return $this->db->execute();
  }

  private function _setUserToken($username, $token){
        $this->db->query("UPDATE `tbl_user` SET token = :token WHERE `username` = :username");
        $this->db->bind(':username', $username);
        $this->db->bind(':token', $token);
        return $this->db->execute();
  }

  private function _getUserToken($username){
      $this->db->query("SELECT token FROM `tbl_user` WHERE `username` = :username LIMIT 1");
      $this->db->bind(':username', $username);
      return $this->db->singleValue();
  }

  private function _getUserPermissions($token){
      $this->db->query("SELECT permissions FROM `tbl_user` WHERE `token` = :token LIMIT 1");
      $this->db->bind(':token', $token);
      return $this->db->singleValue();
  }
  
  private function _validateToken($token){
      $this->db->query("SELECT token_expire FROM `tbl_user` WHERE `token` = :token LIMIT 1");
      $this->db->bind(':token', $token);
      $tokenExpiration = strtotime($this->db->singleValue());
      if(time() <= $tokenExpiration){
        return TRUE;
        }
      return FALSE;
  }
  
  private function _getTokenExpiration($token){
      $this->db->query("SELECT token_expire FROM `tbl_user` WHERE `token` = :token LIMIT 1");
      $this->db->bind(':token', $token);
      $tokenExpiration = strtotime($this->db->singleValue());

      return $tokenExpiration;
  }

  private function _setUserPermissions($username, $permissions){
        $this->db->query("UPDATE `tbl_user` SET permissions = :permissions WHERE `username` = :username");
        $this->db->bind(':username', $username);
        $this->db->bind(':permissions', $permissions);
        return $this->db->execute();
  }

  private function _getUserFromToken($token){
      $this->db->query("SELECT username FROM `tbl_user` WHERE `token` = :token LIMIT 1");
      $this->db->bind(':token', $token);
      return $this->db->singleValue();
  }

  private function _getUserPassword($username){
      $this->db->query("SELECT password FROM `tbl_user` WHERE `username` = :username LIMIT 1");
      $this->db->bind(':username', $username);
      return $this->db->singleValue();
  }

  private function _getUserName($username){
      $this->db->query("SELECT name FROM `tbl_user` WHERE `username` = :username LIMIT 1");
      $this->db->bind(':username', $username);
      return $this->db->singleValue();
  }

  public function _addUser($username, $password, $name){
      $this->db->query("INSERT INTO `tbl_user` (`username`, `password`, `name`, `permissions`) VALUES (:username, :password, :name, :permissions)");
      $this->db->bind(':username', $username);
      $this->db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
      $this->db->bind(':name', $name);
      $this->db->bind(':permissions', 1); // Default to read-only permissions
      return $this->db->execute();
  }


// Public Functions

  public function getUserPassword($username){
    return $this->_getUserPassword($username);
    }

  public function getUserPermissions($token){
    return $this->_getUserPermissions($token);
    }
    
  public function getUserName($username){
    return $this->_getUserName($username);
    }
        
  public function validateToken($token){
    return $this->_validateToken($token);
   }

  public function setUserPermissions($username, $permissions){
    return $this->_setUserPermissions($username, $permissions);
   }
      
  public function addUser($username, $password, $name){
	  $this->getUserName() == FALSE ? return $this->_addUser($username, $password, $name) : return FALSE;
  }
          
  public function updateUserToken($username){
    $token = hash('sha224',openssl_random_pseudo_bytes(32)); //generate a random token 56 characters in length
    $tokenExpiration = date('Y-m-d H:i:s', strtotime('+2 hour')); // Set expiration for 2 hours time

    if ($this->_setUserToken($username, $token) && $this->_setTokenExpiration($token, $tokenExpiration)){
      return TRUE;
      }
        
  }

  public function isLoggedIn($username) {
      return $this->_getUserToken($username) != NULL ? TRUE : FALSE;      
  }

  public function userLogin($username, $password) {

      if (password_verify($password, $this->getUserPassword($username)) && !$this->validateToken($this->_getUserToken($username))) { 
            
      $arrRtn['name'] = $this->getUserName($username);      //Just return the user name for reference

      if ($this->updateUserToken($username)){
        $arrRtn['token'] = $this->_getUserToken($username);
        $arrRtn['token_expire'] = $this->_getTokenExpiration($arrRtn['token']); 
        }
      
        $arrRtn['valid'] = $this->validateToken($this->_getUserToken($username));
        return $arrRtn;
        }else{
          return $arrRtn['_ERROR'] = "Username / Password incorrect or user already logged in";
          }
      }
                                  
  public function userLogout($token) {
    if($this->validateToken($token)){
       return ($this->_setTokenExpiration($token, NULL) && $this->_setUserToken($this->_getUserFromToken($token), NULL)) ? TRUE : FALSE;
       }
   }

  public function forceUserLogout($username, $password) {
    if(password_verify($password, $this->getUserPassword($username))){
       return ($this->_setTokenExpiration($this->_getUserToken($username), NULL) && $this->_setUserToken($username, NULL)) ? TRUE : FALSE;
       }
   }
  
  
}

?>
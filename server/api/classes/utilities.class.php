<?php

spl_autoload_register(function ($class_name) {
    require strtolower($class_name) . '.class.php';
});


/**
 * General use class with static functions used by many classes and pages.
 */
class Utilities {
    const TRANSACTION_EPOCH = "1338576300";
    const  ARBITRARY_KEY = "01110100011010010110110101100101";
    const  SHA256TEST = "8c49a2b56ebd8fc49a17956dc529943eb0d73c00ee6eafa5d8b3ba1274eb3ea4";
    const  TIMEKOIN_VERSION = "4.0";
    const  NEXT_VERSION = "current_version51";
   
    
    public function __construct(){
        
    }
    
    public static function filter_sql($string)
    {
            // Filter symbols that might lead to an SQL injection attack
            $symbols = array("'", "%", "*", "`");
            $string = str_replace($symbols, "", $string);

        return $string;
    }
    
    public static function find_string($start_tag, $end_tag, $full_string, $end_match = FALSE)
    {
            $delimiter = '|';

            if($end_match == FALSE)
            {
                    $regex = $delimiter . preg_quote($start_tag, $delimiter) . '(.*?)'  . preg_quote($end_tag, $delimiter)  . $delimiter  . 's';
            }
            else
            {
                    $regex = $delimiter . preg_quote($start_tag, $delimiter) . '(.*)'  . preg_quote($end_tag, $delimiter)  . $delimiter  . 's';
            }

            preg_match_all($regex,$full_string,$matches);

            foreach($matches[1] as $found_string)
            {
            }

            return $found_string;
    }
    
    public static function getCharFreq($str,$chr=false)
    {
            $c = Array();
            if ($chr!==false) return substr_count($str, $chr);
            foreach(preg_split('//',$str,-1,1)as$v)($c[$v])?$c[$v]++ :$c[$v]=1;
            return $c;
    }
    
    public static function write_log($message, $type)
    {
        
        $database = new Database();
        
            // Write Log Entry
            $sql = "INSERT INTO activity_logs (timestamp ,log ,attribute) VALUES (:time, :message, :type)";            
            $database->query($sql);
            $database->bind(':time', time());
            $database->bind(':message', filter_sql(substr($message, 0, 256)) );
            $database->bind(':type', $type);
            $database->execute();
            return;
    }
    
    public static function filter_public_key($public_key)
    {
            if($public_key != ARBITRARY_KEY)
            {
                    // Filter any characters or values that do not belong in a public key
                    $public_key = preg_replace("|[^\\a-zA-Z0-9\s\s+-/=]|", "", $public_key);
                    return $public_key;
            }

            // Not a public key, return the original string
            return $public_key;
    }
    
    public static function tk_encrypt($key, $crypt_data)
    {
            if(function_exists('openssl_private_encrypt') == TRUE)
            {
                    openssl_private_encrypt($crypt_data, $encrypted_data, $key, OPENSSL_PKCS1_PADDING);
            }
            else
            {
                    require_once('RSA.php');
                    $rsa = new Crypt_RSA();
                    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
                    $rsa->loadKey($key);
                    $encrypted_data = $rsa->encrypt($crypt_data);
            }

            return $encrypted_data;
    }
    
    public static function set_decrypt_mode()
    {
            if(function_exists('openssl_public_decrypt') == TRUE)
            {
                    $GLOBALS['decrypt_mode'] = 1;
            }
            else
            {
                    $GLOBALS['decrypt_mode'] = 2;
            }
            return;
    }
    
    public static function tk_decrypt($key, $crypt_data, $skip_openssl_check = FALSE)
    {
            $decrypt;

            if($skip_openssl_check == TRUE || function_exists('openssl_public_decrypt') == TRUE)
            {
                    // Use OpenSSL if it is working
                    openssl_public_decrypt($crypt_data, $decrypt, $key, OPENSSL_PKCS1_PADDING);

                    if(empty($decrypt) == TRUE)
                    {
                            // OpenSSL can't decrypt this for some reason
                            // Use built in Code instead
                            require_once('RSA.php');
                            $rsa = new Crypt_RSA();
                            $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
                            $rsa->loadKey($key);
                            $decrypt = $rsa->decrypt($crypt_data);
                    }
            }
            else
            {
                    // Use built in Code
                    require_once('RSA.php');
                    $rsa = new Crypt_RSA();
                    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
                    $rsa->loadKey($key);
                    $decrypt = $rsa->decrypt($crypt_data);
            }

            return $decrypt;
    }
    
    public static function tk_time_convert($time)
    {
            if($time < 0)
            {
                    return "Now";
            }

            if($time < 60)
            {
                    if($time == 1)
                    {
                            $time .= " sec";
                    }
                    else
                    {
                            $time .= " secs";
                    }
            }
            else if($time >= 60 && $time < 3600)
            {
                    if($time >= 60 && $time < 120)
                    {
                            $time = intval($time / 60) . " min";
                    }
                    else
                    {
                            $time = intval($time / 60) . " mins";
                    }
            }
            else if($time >= 3600 && $time < 86400)
            {
                    if($time >= 3600 && $time < 7200)
                    {
                            $time = intval($time / 3600) . " hour";
                    }
                    else
                    {
                            $time = intval($time / 3600) . " hours";
                    }
            }
            else if($time >= 86400)
            {
                    if($time >= 86400 && $time < 172800)
                    {
                            $time = intval($time / 86400) . " day";
                    }
                    else
                    {
                            $time = intval($time / 86400) . " days";
                    }		
            }

            return $time;
    }
    
    public static function unix_timestamp_to_human($timestamp = "", $default_timezone, $format = 'D d M Y - H:i:s')
    {
            if(empty($default_timezone) == FALSE)
            {	
                    date_default_timezone_set($default_timezone);
            }

            if (empty($timestamp) || ! is_numeric($timestamp)) $timestamp = time();
            return ($timestamp) ? date($format, $timestamp) : date($format, $timestamp);
    }
}

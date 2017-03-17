<?php


class Peers {
    private $db;
    
    public function __construct(){
        $this->db = new Database();       
    }
    
    public function __construct1($database){
        $this->db = $database;       
    }
    
    public function __destruct() {
       $this->db=null;
   }
    
    function ip_banned($ip)
    {
            // Check for banned IP address
            $this->db->query("SELECT ip FROM `ip_banlist` WHERE `ip` = :ip LIMIT 1");
            $this->db->bind(':ip', $ip);
            $ip = $this->db->singleValue();

            if(empty($ip) == TRUE)
            {
                    return FALSE;
            }
            else
            {
                    // Sorry, your IP address has been banned :(
                    return TRUE;
            }
    }
    
    function log_ip($attribute, $multiple = 1, $super_peer_check = FALSE)
    {
            if($_SERVER['REMOTE_ADDR'] == "::1" || $_SERVER['REMOTE_ADDR'] == "127.0.0.1")
            {
                    // Ignore Local Machine Address
                    return;
            }

            if($super_peer_check == TRUE)
            {
                    // Is Super Peer Enabled?
                    $this->db->query("SELECT field_data FROM `main_loop_status` WHERE `field_name` = 'super_peer' LIMIT 1");
                    $super_peer_mode = $this->db->singleValue();                    

                    if($super_peer_mode > 0)
                    {
                            // Only count 1 in 4 IP for Super Peer Transaction Clerk to avoid
                            // accidental banning of peers accessing high volume data.
                            if(rand(1,4) != 4)
                            {
                                    return;
                            }
                    }
            }

            // Log IP Address Access
            while($multiple >= 1)
            {
                $this->db->query("INSERT INTO `ip_activity` (`timestamp` ,`ip`, `attribute`) VALUES (:time, :ip, :attribute)");
                $this->db->bind(':time', time());
                $this->db->bind(':ip', $_SERVER['REMOTE_ADDR']);
                $this->db->bind(':attribute', $attribute);
                $ip = $this->db->execute();
                
                    $multiple--;
            }
            return;
    }
    
    function scale_trigger($trigger = 100)
    {
            // Scale the amount of copies of the IP based on the trigger set.
            // So for example, a trigger of 1 means that one event can trigger flood protection.
            // A trigger of 2 means 2 events will trigger flood protection. So only half as many
            // IP copies are returned in this function.
            $this->db->query("SELECT field_data FROM `main_loop_status` WHERE `field_name` = 'server_request_max' LIMIT 1");
            $request_max = $this->db->singleValue(); 

            return intval($request_max / $trigger);
    }
    
    function perm_peer_mode()
    {
            $this->db->query("SELECT field_data FROM `main_loop_status` WHERE `field_name` = 'perm_peer_priority' LIMIT 1");
            $perm_peer_priority = intval($this->db->singleValue()); 

            if($perm_peer_priority == 1)
            {
                    return "SELECT * FROM `active_peer_list` WHERE `join_peer_list` = 0 ORDER BY RAND()";
            }
            else
            {
                    return "SELECT * FROM `active_peer_list` ORDER BY RAND()";
            }
    }
    
    function modify_peer_grade($ip_address, $domain, $subfolder, $port_number, $grade)
    {            
            $this->db->query("SELECT failed_sent_heartbeat FROM `active_peer_list` WHERE `IP_Address` = :ip_address AND `domain` = :domain AND `subfolder` = :subfolder AND `port_number` = :port_number LIMIT 1");
            $this->db->bind(':ip_address', $ip_address);
            $this->db->bind(':domain', $domain);
            $this->db->bind(':port_number', $port_number);
            $this->db->bind(':subfolder', $subfolder);
            $peer_failure = intval($this->db->singleValue()); 

            if($peer_failure < 50000) // Don't change anything over 50,000 as it is reserved for peers where failure grade is not used
            {
                    $peer_failure += $grade;
                    if($peer_failure >= 0)
                    {
                            $this->db->query("UPDATE `active_peer_list` SET `failed_sent_heartbeat` = :peer_failure WHERE `IP_Address` = :ip_address AND `domain` = :domain AND `subfolder` = :subfolder AND `port_number` = :port_number LIMIT 1");
                            $this->db->bind(':peer_failure', $peer_failure);
                            $this->db->bind(':ip_address', $ip_address);
                            $this->db->bind(':domain', $domain);
                            $this->db->bind(':port_number', $port_number);
                            $this->db->bind(':subfolder', $subfolder);
                            $this->db->execute();
                    }
            }
            return;
    }
    
    function poll_peer($ip_address, $domain, $subfolder, $port_number, $max_length, $poll_string, $custom_context)
    {
            if(empty($custom_context) == TRUE)
            {
                    // Standard socket close
                    $context = stream_context_create(array('http' => array('header'=>'Connection: close'))); // Force close socket after complete
            }
            else
            {
                    // Custom Context Data
                    $context = $custom_context;
            }

            if(empty($domain) == TRUE)
            {
                    if(filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == TRUE)
                    {
                            // IP Address is IPv6
                            // Fix up the format for proper polling
                            $ip_address = "[" . $ip_address . "]";
                    }

                    $site_address = $ip_address;
            }
            else
            {
                    $site_address = $domain;
            }

            if($port_number == 443)
            {
                    $ssl = "s";
            }
            else
            {
                    $ssl = NULL;
            }

            if(empty($subfolder) == FALSE)
            {
                    // Sub-folder included
                    $poll_data = filter_sql(file_get_contents("http$ssl://$site_address:$port_number/$subfolder/$poll_string", FALSE, $context, NULL, $max_length));
            }
            else
            {
                    // No sub-folder
                    $poll_data = filter_sql(file_get_contents("http$ssl://$site_address:$port_number/$poll_string", FALSE, $context, NULL, $max_length));
            }

            return $poll_data;
    }
    
    function peer_gen_amount($public_key)
    {
            // 1 week = 604,800 seconds
            $join_peer_list1 = "";
            $join_peer_list2 = "";
            $this->db->query("SELECT * FROM `generating_peer_list` WHERE `public_key` = :pub_key LIMIT 2");
            $this->db->bind(':pub_key', $public_key);
            $join_peer_list=$this->db->resultset();
            
            if($this->db->rowCount()>=1)
            {
                $join_peer_list1=$join_peer_list[0]["join_peer_list"];   
            }            
            if($this->db->rowCount()>=2)
            {
                $join_peer_list2=$join_peer_list[1]["join_peer_list"];
            }            
            
            
            $amount;

            if(empty($join_peer_list1) == TRUE || $join_peer_list1 < TRANSACTION_EPOCH)
            {
                    // Not found in the generating peer list
                    $amount = 0;
            }
            else
            {
                    // How many weeks has this public key been in the peer list
                    $peer_age = time() - $join_peer_list1;
                    $peer_age = intval($peer_age / 604800);
                    $amount = 0;

                    switch($peer_age)
                    {
                            case 0:
                                    $amount = 1;
                                    break;

                            case 1:
                                    $amount = 2;
                                    break;

                            case ($peer_age >= 2 && $peer_age <= 3):
                                    $amount = 3;
                                    break;

                            case ($peer_age >= 4 && $peer_age <= 7):
                                    $amount = 4;
                                    break;

                            case ($peer_age >= 8 && $peer_age <= 15):
                                    $amount = 5;
                                    break;

                            case ($peer_age >= 16 && $peer_age <= 31):
                                    $amount = 6;
                                    break;

                            case ($peer_age >= 32 && $peer_age <= 63):
                                    $amount = 7;
                                    break;

                            case ($peer_age >= 64 && $peer_age <= 127):
                                    $amount = 8;
                                    break;

                            case ($peer_age >= 128 && $peer_age <= 255):
                                    $amount = 9;
                                    break;

                            case ($peer_age >= 256):
                                    $amount = 10;
                                    break;

                            default:
                                    $amount = 1;
                                    break;				
                    }
            }

            if(empty($join_peer_list2) == TRUE || $join_peer_list2 < TRANSACTION_EPOCH)
            {
                    // Not found in the generating peer list
                    $amount+= 0;
            }
            else
            {
                    // How many weeks has this public key been in the peer list
                    $peer_age = time() - $join_peer_list2;
                    $peer_age = intval($peer_age / 604800);
                    $amount2 = 0;

                    switch($peer_age)
                    {
                            case 0:
                                    $amount2 = 1;
                                    break;

                            case 1:
                                    $amount2 = 2;
                                    break;

                            case ($peer_age >= 2 && $peer_age <= 3):
                                    $amount2 = 3;
                                    break;

                            case ($peer_age >= 4 && $peer_age <= 7):
                                    $amount2 = 4;
                                    break;

                            case ($peer_age >= 8 && $peer_age <= 15):
                                    $amount2 = 5;
                                    break;

                            case ($peer_age >= 16 && $peer_age <= 31):
                                    $amount2 = 6;
                                    break;

                            case ($peer_age >= 32 && $peer_age <= 63):
                                    $amount2 = 7;
                                    break;

                            case ($peer_age >= 64 && $peer_age <= 127):
                                    $amount2 = 8;
                                    break;

                            case ($peer_age >= 128 && $peer_age <= 255):
                                    $amount2 = 9;
                                    break;

                            case ($peer_age >= 256):
                                    $amount2 = 10;
                                    break;

                            default:
                                    $amount2 = 1;
                                    break;				
                    }
            }

            return $amount + $amount2;
    }
    

    function is_private_ip($ip, $ignore = FALSE)
    {
            if(empty($ip) == TRUE)
            {
                    return FALSE;
            }

            if($ignore == TRUE)
            {
                    $result = FALSE;
            }
            else
            {
                    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) == FALSE)
                    {
                            $result = TRUE;
                    }

                    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) == FALSE)
                    {
                            $result = TRUE;
                    }
            }

            return $result;
    }

    function is_domain_valid($domain)
    {
            $result = TRUE;

            if(empty($domain) == TRUE)
            {
                    $result = FALSE;		
            }

            if(filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) == TRUE)
            {
                    $result = FALSE;
            }

            if(filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == TRUE)
            {
                    $result = FALSE;
            }

            if(is_private_ip($domain) == FALSE)
            {
                    $result = FALSE;
            }

            if(strtolower($domain) == "localhost")
            {
                    $result = FALSE;
            }

            return $result;
    }
    
    function auto_update_IP_address()
    {
            // IPv4 Update
            $sys = new System();
            
            $generation_IP = $sys->get_option("generation_IP");
            $poll_IP = filter_sql(poll_peer(NULL, 'timekoin.net', NULL, 80, 46, "ipv4.php"));
            
            if(empty($generation_IP) == TRUE) // IP Field Empty
            {
                    if(empty($poll_IP) == FALSE && ipv6_test($poll_IP) == FALSE)
                    {                            
                            if($sys->set_option("generation_IP",$poll_IP) == TRUE)
                            {
                                    write_log("Generation IPv4 Updated to ($poll_IP)", "GP");
                            }
                    }
            }
            else
            {
                    // Check that existing IP still matches current IP and update if there is no match
                    if($generation_IP != $poll_IP)
                    {
                            if(empty($poll_IP) == FALSE && ipv6_test($poll_IP) == FALSE)
                            {                                    
                                    if($sys->set_option("generation_IP",$poll_IP) == TRUE)
                                    {
                                            write_log("Generation IPv4 Updated from ($generation_IP) to ($poll_IP)", "GP");
                                    }
                            }
                    }
            }

            // IPv6 Update	
            $generation_IP = $sys->get_option("generation_IP_v6");
            $poll_IP = filter_sql(poll_peer(NULL, 'ipv6.timekoin.net', NULL, 80, 46, "ipv6.php"));

            if(empty($generation_IP) == TRUE) // IP Field Empty
            {
                    if(empty($poll_IP) == FALSE && ipv6_test($poll_IP) == TRUE)
                    {                            
                            if($sys->set_option("generation_IP_v6",$poll_IP) == TRUE)
                            {
                                    write_log("Generation IPv6 Updated to ($poll_IP)", "GP");
                            }
                    }
            }
            else
            {
                    // Check that existing IP still matches current IP and update if there is no match
                    if($generation_IP != $poll_IP)
                    {
                            if(empty($poll_IP) == FALSE && ipv6_test($poll_IP) == TRUE)
                            {
                                    if($sys->set_option("generation_IP_v6",$poll_IP) == TRUE)
                                    {
                                            write_log("Generation IPv6 Updated from ($generation_IP) to ($poll_IP)", "GP");
                                    }
                            }
                    }
            }	
    }
    
    function ipv6_test($ip_address)
    {
            if(filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == TRUE)
            {
                    // IP Address is IPv6
                    return TRUE;
            }

            return FALSE;
    }
    //***********************************************************************************
    //***********************************************************************************
    function ipv6_compress($ip_address)
    {
            if(filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) == TRUE)
            {
                    // IP Address is IPv6
                    return inet_ntop(inet_pton($ip_address)); // Return Compressed Shorthand
            }

            return FALSE;
    }

    
}
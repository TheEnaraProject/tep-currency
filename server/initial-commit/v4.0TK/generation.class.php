<?php


class Generation {
    
    private $db;
    
    public function __construct(){
        $this->db = new Database();       
    }
    
    public function __construct1($database){
        $this->db = $database;       
    }
    
    public function __destruct() {
       
    }
    
    function generation_peer_hash()
    {
            $sql = "SELECT public_key, join_peer_list FROM `generating_peer_list` ORDER BY `join_peer_list` ASC";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            $generating_hash = 0;

            if($sql_num_results > 0)
            {
                    for ($i = 0; $i < $sql_num_results; $i++)
                    {
                            $sql_row = mysql_fetch_array($sql_result);
                            $generating_hash .= $sql_row["public_key"] . $sql_row["join_peer_list"];
                    }
            }

            return hash('md5', $generating_hash);
    }
    
    function scorePublicKey($public_key, $score_key = FALSE)
    {
            $current_generation_block = transaction_cycle(0, TRUE);	

            TKRandom::seed($current_generation_block);

            $public_key_score = 0;
            $tkrandom_num = 0;
            $character = 0;

            if($score_key == TRUE)
            {
                    $output_score_key;

                    // Output what is being used to score the keys
                    for ($i = 0; $i < 18; $i++)
                    {
                            $tkrandom_num = TKRandom::num(1, 35);
                            $output_score_key .= "[$tkrandom_num=" . base_convert($tkrandom_num, 10, 36) . "]";  // Base 10 to Base 36 conversion
                    }

                    return $output_score_key;
            }

            for ($i = 0; $i < 18; $i++)
            {
                    $tkrandom_num = TKRandom::num(1, 35);
                    $character = base_convert($tkrandom_num, 10, 36);  // Base 10 to Base 36 conversion
                    $public_key_score += getCharFreq($public_key, $character);
            }

            return $public_key_score;
    }
    
    function election_cycle($when = 0, $ip_type = 1, $gen_peers_total = 0)
    {
            if($ip_type == 1)
            {
                    // IPv4 Election Cycle Checking
                    // Check if a peer election should take place now or
                    // so many cycles ahead in the future
                    if($when == 0)
                    {
                            // Check right now
                            $current_generation_cycle = transaction_cycle(0);
                            $current_generation_block = transaction_cycle(0, TRUE);
                    }
                    else
                    {
                            // Sometime further in the future
                            $current_generation_cycle = transaction_cycle($when);
                            $current_generation_block = transaction_cycle($when, TRUE);
                    }

                    $str = strval($current_generation_cycle);
                    $last3_gen = intval($str[strlen($str)-3]);

                    TKRandom::seed($current_generation_block);
                    $tk_random_number = TKRandom::num(0, 9);

                    if($last3_gen + $tk_random_number > 16)
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            else if($ip_type == 2)
            {
                    // IPv6 Election Cycle Checking
                    // Check if a peer election should take place now or
                    // so many cycles ahead in the future
                    if($when == 0)
                    {
                            // Check right now
                            $current_generation_cycle = transaction_cycle(0);
                            $current_generation_block = transaction_cycle(0, TRUE);
                    }
                    else
                    {
                            // Sometime further in the future
                            $current_generation_cycle = transaction_cycle($when);
                            $current_generation_block = transaction_cycle($when, TRUE);
                    }

                    $str = strval($current_generation_cycle);
                    $last3_gen = intval($str[strlen($str)-3]);

                    // Transpose waveform 180 degrees from IPv4 Generation
                    if($last3_gen == 0)
                    {
                            $last3_gen = 5;
                    }
                    else if($last3_gen == 1)
                    {
                            $last3_gen = 6;
                    }
                    else if($last3_gen == 2)
                    {
                            $last3_gen = 7;
                    }
                    else if($last3_gen == 3)
                    {
                            $last3_gen = 8;
                    }
                    else if($last3_gen == 4)
                    {
                            $last3_gen = 9;
                    }
                    else
                    {
                            $last3_gen-= 5;
                    }
                    // Transpose waveform 180 degrees from IPv4 Generation
                    TKRandom::seed($current_generation_block);
                    $tk_random_number = TKRandom::num(0, 9);
                    $ipv6_gen_peer_adapt = TKRandom::num(0, $gen_peers_total);

                    // The more IPv6 Peers that Generate, the less often Peer Elections happen
                    if($last3_gen + $tk_random_number > 16)
                    {
                            if($ipv6_gen_peer_adapt < 25)
                            {
                                    return TRUE;
                            }
                            else
                            {
                                    return FALSE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }

            // No match to anything
            return FALSE;
    }
    
    function generation_cycle($when = 0)
    {
            // Check if currency generation should take place now or
            // so many cycles ahead in the future
            if($when == 0)
            {
                    // Check right now
                    $current_generation_cycle = transaction_cycle(0);
                    $current_generation_block = transaction_cycle(0, TRUE);
            }
            else
            {
                    // Sometime further in the future
                    $current_generation_cycle = transaction_cycle($when);
                    $current_generation_block = transaction_cycle($when, TRUE);
            }

            $str = strval($current_generation_cycle);
            $last3_gen = intval($str[strlen($str)-3]);

            TKRandom::seed($current_generation_block);
            $tk_random_number = TKRandom::num(0, 9);

            if($last3_gen + $tk_random_number < 6)
            {
                    return TRUE;
            }
            else
            {
                    return FALSE;
            }

            // No match to anything
            return FALSE;	
    }
    
    function gen_simple_poll_test($ip_address, $domain, $subfolder, $port_number)
    {
            $simple_poll_fail = FALSE; // Reset Variable

            TKRandom::seed(transaction_cycle(0, TRUE));

            // Grab random Transaction Foundation Hash
            $rand_block = TKRandom::num(0,foundation_cycle(0, TRUE) - 5); // Range from Start to Last 5 Foundation Hash
            $random_foundation_hash = mysql_result(mysql_query("SELECT hash FROM `transaction_foundation` WHERE `block` = $rand_block LIMIT 1"),0,0);

            // Grab random Transaction Hash
            $rand_block2 = TKRandom::num(transaction_cycle((0 - transaction_cycle(0, TRUE)), TRUE), transaction_cycle(-1000, TRUE)); // Range from Start to Last 1000 Transaction Hash
            $rand_block2 = transaction_cycle(0 - $rand_block2);
            $random_transaction_hash = mysql_result(mysql_query("SELECT hash FROM `transaction_history` WHERE `timestamp` = $rand_block2 LIMIT 1"),0,0);
            $rand_block2 = ($rand_block2 - TRANSACTION_EPOCH - 300) / 300;

            if(empty($random_foundation_hash) == FALSE) // Make sure we had one to compare first
            {
                    $poll_peer = poll_peer($ip_address, $domain, $subfolder, $port_number, 64, "foundation.php?action=block_hash&block_number=$rand_block");

                    // Is it valid?
                    if(empty($poll_peer) == TRUE)
                    {
                            // No response?
                            $simple_poll_fail = TRUE;
                    }
                    else
                    {
                            // Is it valid?
                            if($poll_peer == $random_foundation_hash)
                            {
                                    // Got a good response from an active Timekoin server
                                    $simple_poll_fail = FALSE;
                            }
                            else
                            {
                                    // Wrong Response?
                                    $simple_poll_fail = TRUE;
                            }
                    }
            }

            if(empty($random_transaction_hash) == FALSE) // Make sure we had one to compare first
            {
                    $poll_peer = poll_peer($ip_address, $domain, $subfolder, $port_number, 64, "transclerk.php?action=block_hash&block_number=$rand_block2");

                    // Is it valid?
                    if(empty($poll_peer) == TRUE)
                    {
                            //No response?
                            $simple_poll_fail = TRUE;
                    }
                    else
                    {
                            // Is it valid?
                            if($poll_peer == $random_transaction_hash)
                            {
                                    //Got a good response from an active Timekoin server
                                    $simple_poll_fail = FALSE;
                            }
                            else
                            {
                                    //Wrong Response?
                                    $simple_poll_fail = TRUE;
                            }
                    }
            }

            return $simple_poll_fail;
    }
    
    function find_v4_gen_key($my_public_key)
    {
            $sql = "SELECT IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == FALSE)
                    {
                            //IPv4 Address Associated with this Generating Public Key
                            return TRUE;
                    }
            }

            // No Matching Key with an IPv4 Address Found
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function find_v6_gen_key($my_public_key)
    {
            $sql = "SELECT IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == TRUE)
                    {
                            //IPv6 Address Associated with this Generating Public Key
                            return TRUE;
                    }
            }

            // No Matching Keys with an IPv6 Address Found
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function find_v4_gen_IP($my_public_key)
    {
            $sql = "SELECT IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == FALSE)
                    {
                            // Return IPv4 Address Associated with this Generating Public Key
                            return $sql_row["IP_Address"];
                    }
            }

            // No Matching Key with an IPv4 Address Found
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function find_v6_gen_IP($my_public_key)
    {
            $sql = "SELECT IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == TRUE)
                    {
                            // Return IPv6 Address Associated with this Generating Public Key
                            return $sql_row["IP_Address"];
                    }
            }

            // No Matching Key with an IPv6 Address Found
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function find_v4_gen_join($my_public_key)
    {
            $sql = "SELECT join_peer_list, IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == FALSE)
                    {
                            // Return IPv4 Address Associated with this Generating Public Key
                            return $sql_row["join_peer_list"];
                    }
            }

            // No Matching Key with an IPv4 Address Found
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function find_v6_gen_join($my_public_key)
    {
            $sql = "SELECT join_peer_list, IP_Address FROM `generating_peer_list` WHERE `public_key` = '$my_public_key'";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);

                    if(ipv6_test($sql_row["IP_Address"]) == TRUE)
                    {
                            // Return IPv6 Address Associated with this Generating Public Key
                            return $sql_row["join_peer_list"];
                    }
            }

            // No Matching Key with an IPv6 Address Found
            return;
    }
}

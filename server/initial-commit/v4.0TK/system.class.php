<?php


class System {
    
    private $_PublicKey = "";
    private $_PrivateKey = "";
    private $_Subfolder = "";
    private $_Port = "";
    private $_Domain = "";
    
    public function __construct(){
        
    }
    
    function my_public_key()
    {
        if ($this->_PublicKey == "")
            $this->_PublicKey = mysql_result(mysql_query("SELECT field_data FROM `my_keys` WHERE `field_name` = 'server_public_key' LIMIT 1"),0,0);
        
        return $this->_PublicKey;
    }
    
    function my_private_key()
    {
        if ($this->_PrivateKey == "")
            $this->_PrivateKey = mysql_result(mysql_query("SELECT field_data FROM `my_keys` WHERE `field_name` = 'server_private_key' LIMIT 1"),0,0);
        
        return $this->_PrivateKey;
        
    }
    
    function my_subfolder()
    {
        if ($this->_Subfolder == "")
            $this->_Subfolder = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'server_subfolder' LIMIT 1"),0,0);
        
        return $this->_Subfolder;
    }
    
    function my_port_number()
    {
        if ($this->_Port == "")
            $this->_Port = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'server_port_number' LIMIT 1"),0,0);
        
        return $this->_Port;       
    }
    
    function my_domain()
    {
        if ($this->_Domain == "")
            $this->_Domain = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'server_domain' LIMIT 1"),0,0);
        
        return $this->_Domain;        
    }
    
    function call_script($script, $priority = 1, $plugin = FALSE, $web_server_call = FALSE)
    {
            if($web_server_call == TRUE)
            {
                    // No Properly working PHP CLI Extensions for some odd reason, call from web server instead
                    $cli_port = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'cli_port' LIMIT 1"),0,0);

                    if(empty($cli_port) == TRUE)
                    {
                            // Use the same server port that is reported to other peers
                            if($plugin == TRUE)
                            {
                                    poll_peer(NULL, "localhost", my_subfolder() . "/plugins", my_port_number(), 1, $script);
                            }
                            else
                            {
                                    poll_peer(NULL, "localhost", my_subfolder(), my_port_number(), 1, $script);
                            }
                    }
                    else
                    {
                            // Use a different port number than what is reported to other peers.
                            // Useful for port forwarding where the External Internet port is different than
                            // the Internal web server port being forwarded through the router.
                            if($plugin == TRUE)
                            {
                                    poll_peer(NULL, "localhost", my_subfolder() . "/plugins", $cli_port, 1, $script);
                            }
                            else
                            {
                                    poll_peer(NULL, "localhost", my_subfolder(), $cli_port, 1, $script);
                            }			
                    }
            }
            else if($priority == 1)
            {
                    // Normal Priority
                    if(getenv("OS") == "Windows_NT")
                    {
                            pclose(popen("start /B php-win $script", "r"));// This will execute without waiting for it to finish
                    }
                    else
                    {
                            exec("php $script &> /dev/null &"); // This will execute without waiting for it to finish
                    }
            }
            else if($plugin == TRUE)
            {
                    // Normal Priority
                    if(getenv("OS") == "Windows_NT")
                    {
                            pclose(popen("start /B php-win plugins/$script", "r"));// This will execute without waiting for it to finish
                    }
                    else
                    {
                            exec("php plugins/$script &> /dev/null &"); // This will execute without waiting for it to finish
                    }
            }
            else
            {
                    // Below Normal Priority
                    if(getenv("OS") == "Windows_NT")
                    {
                            pclose(popen("start /BELOWNORMAL /B php-win $script", "r"));// This will execute without waiting for it to finish
                    }
                    else
                    {
                            exec("nice php $script &> /dev/null &"); // This will execute without waiting for it to finish
                    }
            }

            return;
    }
    
    function clone_script($script)
    {
            // No Properly working PHP CLI Extensions for some odd reason, call from web server instead
            $cli_port = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'cli_port' LIMIT 1"),0,0);

            if(empty($cli_port) == TRUE)
            {
                    // Use the same server port that is reported to other peers
                    poll_peer(NULL, "localhost", my_subfolder(), my_port_number(), 1, $script);
            }
            else
            {
                    // Use a different port number than what is reported to other peers.
                    // Useful for port forwarding where the External Internet port is different than
                    // the Internal web server port being forwarded through the router.
                    poll_peer(NULL, "localhost", my_subfolder(), $cli_port, 1, $script);
            }
    }
    
    function initialization_database()
    {
            // Clear IP Activity and Banlist for next start
            mysql_query("TRUNCATE TABLE `ip_activity`");
            mysql_query("TRUNCATE TABLE `ip_banlist`");

            // Clear Active & New Peers List
            mysql_query("DELETE FROM `active_peer_list` WHERE `active_peer_list`.`join_peer_list` != 0"); // Permanent Peers Ignored
            mysql_query("TRUNCATE TABLE `new_peers_list`");

            // Record when started
            mysql_query("UPDATE `options` SET `field_data` = '" . time() . "' WHERE `options`.`field_name` = 'timekoin_start_time' LIMIT 1");
    //**************************************
    // Upgrade Database from v3.x earlier versions

            // Auto IP Update Settings
            $new_record_check = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'auto_update_generation_IP' LIMIT 1"),0,0);
            if($new_record_check === FALSE)
            {
                    // Does not exist, create it
                    mysql_query("INSERT INTO `options` (`field_name` ,`field_data`) VALUES ('auto_update_generation_IP', '0')");
            }

            // CLI Mode Settings
            $new_record_check = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'cli_mode' LIMIT 1"),0,0);
            if($new_record_check === FALSE)
            {
                    // Does not exist, create it
                    mysql_query("INSERT INTO `options` (`field_name` ,`field_data`) VALUES ('cli_mode', '1')");
            }

            // CLI Mode Port Settings
            $new_record_check = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'cli_port' LIMIT 1"),0,0);
            if($new_record_check === FALSE)
            {
                    // Does not exist, create it
                    mysql_query("INSERT INTO `options` (`field_name` ,`field_data`) VALUES ('cli_port', '')");
            }

            // IPv4 + IPv6 Network Mode
            $new_record_check = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'network_mode' LIMIT 1"),0,0);
            if($new_record_check === FALSE)
            {
                    // Does not exist, create it
                    mysql_query("INSERT INTO `options` (`field_name` ,`field_data`) VALUES ('network_mode', '1')");
            }

            // IPv6 Generation IP Field
            $new_record_check = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'generation_IP_v6' LIMIT 1"),0,0);
            if($new_record_check === FALSE)
            {
                    // Does not exist, create it
                    mysql_query("INSERT INTO `options` (`field_name` ,`field_data`) VALUES ('generation_IP_v6', '')");
            }
    // Main Loop Status & Active Options Setup

            // Truncate to Free RAM
            mysql_query("TRUNCATE TABLE `main_loop_status`");
            $time = time();
    //**************************************
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('balance_last_heartbeat', '1')");	
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('foundation_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('generation_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('genpeer_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('main_heartbeat_active', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('main_last_heartbeat', '$time')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('peerlist_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('queueclerk_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('transclerk_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('treasurer_last_heartbeat', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('watchdog_heartbeat_active', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('watchdog_last_heartbeat', '$time')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('peer_transaction_start_blocks', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('peer_transaction_performance', '10')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('block_check_back', '1')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('block_check_start', '0')");	
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('firewall_blocked_peer', '0')");	
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('foundation_block_check', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('foundation_block_check_end', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('foundation_block_check_start', '0')");	
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('generation_peer_list_no_sync', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('no_peer_activity', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('time_sync_error', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('transaction_history_block_check', '0')");
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('update_available', '0')");
    //**************************************
    // Copy values from Database to RAM Database
            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'allow_ambient_peer_restart' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('allow_ambient_peer_restart', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'allow_LAN_peers' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('allow_LAN_peers', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'server_request_max' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('server_request_max', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'max_active_peers' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('max_active_peers', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'max_new_peers' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('max_new_peers', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'trans_history_check' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('trans_history_check', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'super_peer' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('super_peer', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'perm_peer_priority' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('perm_peer_priority', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'auto_update_generation_IP' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('auto_update_generation_IP', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'peer_failure_grade' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('peer_failure_grade', '$db_to_RAM')");

            $db_to_RAM = mysql_result(mysql_query("SELECT field_data FROM `options` WHERE `field_name` = 'network_mode' LIMIT 1"),0,0);
            mysql_query("INSERT INTO `main_loop_status` (`field_name` ,`field_data`)VALUES ('network_mode', '$db_to_RAM')");	
    //**************************************
            return 0;
    }
    //***********************************************************************************
    //***********************************************************************************
    function activate($component = "SYSTEM", $on_or_off = 1)
    {
            // Turn the entire or a single script on or off
            $build_file = '<?PHP ';

            // Check what the current constants are
            if($component != "TIMEKOINSYSTEM")	{ $build_file = $build_file . ' define("TIMEKOIN_DISABLED","' . TIMEKOIN_DISABLED . '"); '; }
            if($component != "FOUNDATION") { $build_file = $build_file . ' define("FOUNDATION_DISABLED","' . FOUNDATION_DISABLED . '"); '; }
            if($component != "GENERATION") { $build_file = $build_file . ' define("GENERATION_DISABLED","' . GENERATION_DISABLED . '"); '; }
            if($component != "GENPEER") { $build_file = $build_file . ' define("GENPEER_DISABLED","' . GENPEER_DISABLED . '"); '; }
            if($component != "PEERLIST") { $build_file = $build_file . ' define("PEERLIST_DISABLED","' . PEERLIST_DISABLED . '"); '; }
            if($component != "QUEUECLERK") { $build_file = $build_file . ' define("QUEUECLERK_DISABLED","' . QUEUECLERK_DISABLED . '"); '; }
            if($component != "TRANSCLERK") { $build_file = $build_file . ' define("TRANSCLERK_DISABLED","' . TRANSCLERK_DISABLED . '"); '; }
            if($component != "TREASURER") { $build_file = $build_file . ' define("TREASURER_DISABLED","' . TREASURER_DISABLED . '"); '; }
            if($component != "BALANCE") { $build_file = $build_file . ' define("BALANCE_DISABLED","' . BALANCE_DISABLED . '"); '; }
            if($component != "API") { $build_file = $build_file . ' define("API_DISABLED","' . API_DISABLED . '"); '; }			

            switch($component)
            {
                    case "TIMEKOINSYSTEM":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("TIMEKOIN_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("TIMEKOIN_DISABLED","0"); ';
                            }
                            break;

                    case "FOUNDATION":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("FOUNDATION_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("FOUNDATION_DISABLED","0"); ';
                            }
                            break;

                    case "GENERATION":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("GENERATION_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("GENERATION_DISABLED","0"); ';
                            }
                            break;

                    case "GENPEER":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("GENPEER_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("GENPEER_DISABLED","0"); ';
                            }
                            break;

                    case "PEERLIST":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("PEERLIST_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("PEERLIST_DISABLED","0"); ';
                            }
                            break;

                    case "QUEUECLERK":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("QUEUECLERK_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("QUEUECLERK_DISABLED","0"); ';
                            }
                            break;

                    case "TRANSCLERK":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("TRANSCLERK_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("TRANSCLERK_DISABLED","0"); ';
                            }
                            break;

                    case "TREASURER":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("TREASURER_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("TREASURER_DISABLED","0"); ';
                            }
                            break;

                    case "BALANCE":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("BALANCE_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("BALANCE_DISABLED","0"); ';
                            }
                            break;

                    case "API":
                            if($on_or_off == 0)
                            {
                                    $build_file = $build_file . ' define("API_DISABLED","1"); ';
                            }
                            else
                            {
                                    $build_file = $build_file . ' define("API_DISABLED","0"); ';
                            }
                            break;			
            }

            $build_file = $build_file . ' ?' . '>';

            // Save status.php file to the same directory the script was
            // called from.
            $fh = fopen('status.php', 'w');

            if($fh != FALSE)
            {
                    if(fwrite($fh, $build_file) > 0)
                    {
                            if(fclose($fh) == TRUE)
                            {
                                    return TRUE;
                            }
                    }
            }

            return FALSE;
    }
    
    function generate_new_keys()
    {
            require_once('RSA.php');

            $rsa = new Crypt_RSA();
            extract($rsa->createKey(1536));

            if(empty($privatekey) == FALSE && empty($publickey) == FALSE)
            {
                    $symbols = array("\r");
                    $new_publickey = str_replace($symbols, "", $publickey);
                    $new_privatekey = str_replace($symbols, "", $privatekey);

                    $sql = "UPDATE `my_keys` SET `field_data` = '$new_privatekey' WHERE `my_keys`.`field_name` = 'server_private_key' LIMIT 1";

                    if(mysql_query($sql) == TRUE)
                    {
                            // Private Key Update Success
                            $sql = "UPDATE `my_keys` SET `field_data` = '$new_publickey' WHERE `my_keys`.`field_name` = 'server_public_key' LIMIT 1";

                            if(mysql_query($sql) == TRUE)
                            {
                                    // Blank reverse crypto data field
                                    mysql_query("UPDATE `options` SET `field_data` = '' WHERE `options`.`field_name` = 'generation_key_crypt' LIMIT 1");

                                    // Public Key Update Success				
                                    return 1;
                            }
                    }
            }
            else
            {
                    // Key Pair Creation Error
                    return 0;
            }

            return 0;
    }
    
    function check_for_updates($code_feedback = FALSE)
    {
            // Poll timekoin.com for any program updates
            $context = stream_context_create(array('http' => array('header'=>'Connection: close'))); // Force close socket after complete
            ini_set('user_agent', 'Timekoin Server (GUI) v' . TIMEKOIN_VERSION);
            ini_set('default_socket_timeout', 10); // Timeout for request in seconds

            $update_check1 = 'Checking for Updates....<br><br>';

            $poll_version = file_get_contents("https://timekoin.com/tkupdates/" . NEXT_VERSION, FALSE, $context, NULL, 10);

            if($poll_version > TIMEKOIN_VERSION && empty($poll_version) == FALSE)
            {
                    if($code_feedback == TRUE) { return 1; } // Code feedback only that update is available

                    $update_check1 .= '<strong>New Version Available <font color="blue">' . $poll_version . '</font></strong><br><br>
                    <FORM ACTION="index.php?menu=options&upgrade=doupgrade" METHOD="post"><input type="submit" name="Submit3" value="Perform Software Update" /></FORM>';
            }
            else if($poll_version <= TIMEKOIN_VERSION && empty($poll_version) == FALSE)
            {
                    $update_check1 .= 'Current Version: <strong>' . TIMEKOIN_VERSION . '</strong><br><br><font color="blue">No Update Necessary.</font>';	
            }
            else
            {
                    $update_check1 .= '<strong><font color="red">ERROR: Could Not Contact Secure Server https://timekoin.com</font></strong>';
            }

            return $update_check1;
    }
    
    function install_update_script($script_name, $script_file)
    {
            $fh = fopen($script_name, 'w');

            if($fh != FALSE)
            {
                    if(fwrite($fh, $script_file) > 0)
                    {
                            if(fclose($fh) == TRUE)
                            {
                                    // Update Complete
                                    return '<font color="green"><strong>Update Complete...</strong></font><br><br>';
                            }
                            else
                            {
                                    return '<font color="red"><strong>ERROR: Update FAILED with a file Close Error.</strong></font><br><br>';
                            }
                    }
            }
            else
            {
                    return '<font color="red"><strong>ERROR: Update FAILED with unable to Open File Error.</strong></font><br><br>';
            }
    }
    
    function check_update_script($script_name, $script, $php_script_file, $poll_version, $context)
    {
            $update_status_return = NULL;

            $poll_sha = file_get_contents("https://timekoin.com/tkupdates/v$poll_version/$script.sha", FALSE, $context, NULL, 64);

            if(empty($poll_sha) == FALSE)
            {
                    $download_sha = hash('sha256', $php_script_file);

                    if($download_sha != $poll_sha)
                    {
                            // Error in SHA match, file corrupt
                            return FALSE;
                    }
                    else
                    {
                            $update_status_return .= 'Server SHA: <strong>' . $poll_sha . '</strong><br>Download SHA: <strong>' . $download_sha . '</strong><br>';
                            $update_status_return .= '<strong>' . $script_name . '</strong> SHA Match...<br>';
                            return $update_status_return;
                    }
            }

            return FALSE;
    }
    
    function get_update_script($php_script, $poll_version, $context)
    {
            return file_get_contents("https://timekoin.com/tkupdates/v$poll_version/$php_script.txt", FALSE, $context, NULL);
    }
    
    function run_script_update($script_name, $script_php, $poll_version, $context, $php_format = 1, $sub_folder = "")
    {
            $php_file = get_update_script($script_php, $poll_version, $context);

            if(empty($php_file) == TRUE)
            {
                    return ' - <strong>No Update Available</strong>...<br><br>';
            }
            else
            {
                    // File exist, is the download valid?
                    $sha_check = check_update_script($script_name, $script_php, $php_file, $poll_version, $context);

                    if($sha_check == FALSE)
                    {
                            return ' - <strong><font color="red">ERROR: Unable to Download File Properly</font></strong>...<br><br>';
                    }
                    else
                    {
                            $update_status .= $sha_check;

                            if($php_format == 1)
                            {
                                    // PHP Files are downloaded as text, then renamed to the .php extension
                                    $update_status .= install_update_script($script_php . '.php', $php_file);
                            }
                            else
                            {
                                    if(empty($sub_folder) == FALSE)
                                    {
                                            // This file is installed to a sub-folder
                                            $update_status .= install_update_script("$sub_folder/" . $script_php, $php_file);
                                    }
                                    else
                                    {
                                            $update_status .= install_update_script($script_php, $php_file);
                                    }
                            }

                            return $update_status;
                    }
            }
    }
    //***********************************************************************************
    function do_updates()
    {
            // Poll timekoin.com for any program updates
            $context = stream_context_create(array('http' => array('header'=>'Connection: close'))); // Force close socket after complete
            ini_set('user_agent', 'Timekoin Server (GUI) v' . TIMEKOIN_VERSION);
            ini_set('default_socket_timeout', 10); // Timeout for request in seconds

            $poll_version = file_get_contents("https://timekoin.com/tkupdates/" . NEXT_VERSION, FALSE, $context, NULL, 10);

            $update_status = 'Starting Update Process...<br><br>';

            if(empty($poll_version) == FALSE)
            {
                    //****************************************************
                    //Check for CSS updates
                    $update_status .= 'Checking for <strong>CSS Template</strong> Update...<br>';
                    $update_status .= run_script_update("CSS Template (admin.css)", "admin.css", $poll_version, $context, 0, "css");
                    //****************************************************
                    //****************************************************
                    $update_status .= 'Checking for <strong>RSA Code</strong> Update...<br>';
                    $update_status .= run_script_update("RSA Code (RSA.php)", "RSA", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Openssl Template</strong> Update...<br>';
                    $update_status .= run_script_update("Openssl Template (openssl.cnf)", "openssl.cnf", $poll_version, $context, 0);
                    //****************************************************
                    //****************************************************
                    $update_status .= 'Checking for <strong>API Access</strong> Update...<br>';
                    $update_status .= run_script_update("API Access (api.php)", "api", $poll_version, $context);
                    //****************************************************
                    //****************************************************
                    $update_status .= 'Checking for <strong>Balace Indexer</strong> Update...<br>';
                    $update_status .= run_script_update("Balance Indexer (balance.php)", "balance", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Transaction Foundation Manager</strong> Update...<br>';
                    $update_status .= run_script_update("Transaction Foundation Manager (foundation.php)", "foundation", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Currency Generation Manager</strong> Update...<br>';
                    $update_status .= run_script_update("Currency Generation Manager (generation.php)", "generation", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Generation Peer Manager</strong> Update...<br>';
                    $update_status .= run_script_update("Generation Peer Manager (genpeer.php)", "genpeer", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Timekoin Web Interface</strong> Update...<br>';
                    $update_status .= run_script_update("Timekoin Web Interface (index.php)", "index", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Main Program</strong> Update...<br>';
                    $update_status .= run_script_update("Main Program (main.php)", "main", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Peer List Manager</strong> Update...<br>';
                    $update_status .= run_script_update("Peer List Manager (peerlist.php)", "peerlist", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Transaction Queue Manager</strong> Update...<br>';
                    $update_status .= run_script_update("Transaction Queue Manager (queueclerk.php)", "queueclerk", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Timekoin Module Status</strong> Update...<br>';
                    $update_status .= run_script_update("Timekoin Module Status (status.php)", "status", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Web Interface Template</strong> Update...<br>';
                    $update_status .= run_script_update("Web Interface Template (templates.php)", "templates", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Transaction Clerk</strong> Update...<br>';
                    $update_status .= run_script_update("Transaction Clerk (transclerk.php)", "transclerk", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Treasurer Processor</strong> Update...<br>';
                    $update_status .= run_script_update("Treasurer Processor (treasurer.php)", "treasurer", $poll_version, $context);
                    //****************************************************
                    $update_status .= 'Checking for <strong>Process Watchdog</strong> Update...<br>';
                    $update_status .= run_script_update("Process Watchdog (watchdog.php)", "watchdog", $poll_version, $context);
                    //****************************************************
                    // We do the function storage last because it contains the version info.
                    // That way if some unknown error prevents updating the files above, this
                    // will allow the user to try again for an update without being stuck in
                    // a new version that is half-updated.
                    $update_status .= 'Checking for <strong>Function Storage</strong> Update...<br>';
                    $update_status .= run_script_update("Function Storage (function.php)", "function", $poll_version, $context);
                    //****************************************************

                    $finish_message = file_get_contents("https://timekoin.com/tkupdates/v$poll_version/ZZZfinish.txt", FALSE, $context, NULL);
                    $update_status .= '<br>' . $finish_message;
            }
            else
            {
                    $update_status .= '<font color="red"><strong>ERROR: Could Not Contact Secure Server https://timekoin.com</strong></font>';
            }

            return $update_status;
    }
    //***********************************************************************************
    //***********************************************************************************
    function plugin_check_for_updates($http_url, $ssl_enable = FALSE)
    {
            // Example Usage
            //
            // plugin_check_for_updates("mysite.blah/updates/plugin_update_01.txt", TRUE)
            //
            // This would return what was in the text file, such as a version number of the latest
            // plugin version for example.

            $context = stream_context_create(array('http' => array('header'=>'Connection: close'))); // Force close socket after complete
            ini_set('user_agent', 'Timekoin Server (Plugin) v' . TIMEKOIN_VERSION);
            ini_set('default_socket_timeout', 10); // Timeout for request in seconds

            if($ssl_enable == TRUE)
            {
                    return file_get_contents("https://$http_url", FALSE, $context, NULL);
            }
            else
            {
                    return file_get_contents("http://$http_url", FALSE, $context, NULL);
            }
    }
    //***********************************************************************************
    function plugin_download_update($http_url, $http_url_sha256, $ssl_enable = FALSE, $plugin_file)
    {
            // Example Usage
            //
            // plugin_download_update("mysite.blah/updates/plugin.txt", "mysite.com/updates/plugin.sha", TRUE, "myplugin.php")
            //
            // This would first download the file plugin.txt and then plugin.sha into memory.
            // Then the SHA256 of the file plugin.txt is compared to value of plugin.sha for a match.
            // If no SHA256 URL is used (NULL setting), then the hash check will be ignored.
            // Once the check passes (or ignored), the file name myplugin.php will be opened up for writing.
            // The downloaded file will be overwritten on top of the myplugin.php and then closed to complete the write.
            // This function should return a TRUE / (1) if successful and anything else will be an error number (0,2,3,4,5)

            $download_file;
            $download_file_SHA256;
            $sha256_check_pass = TRUE; // Default Pass if No SHA256 Used

            $context = stream_context_create(array('http' => array('header'=>'Connection: close'))); // Force close socket after complete
            ini_set('user_agent', 'Timekoin Server (Plugin) v' . TIMEKOIN_VERSION);
            ini_set('default_socket_timeout', 10); // Timeout for request in seconds

            if($ssl_enable == TRUE)
            {
                    $download_file = file_get_contents("https://$http_url", FALSE, $context, NULL);
                    $download_file_SHA256 = file_get_contents("https://$http_url_sha256", FALSE, $context, NULL);
            }
            else
            {
                    $download_file =  file_get_contents("http://$http_url", FALSE, $context, NULL);
                    $download_file_SHA256 = file_get_contents("http://$http_url_sha256", FALSE, $context, NULL);
            }

            if(empty($download_file) == FALSE && empty($http_url_sha256) == FALSE)
            {
                    // Check file against SHA256 Hash to make sure of no file corruption/tampering
                    if(hash('sha256', $download_file) != $download_file_SHA256)
                    {
                            // No SHA256 Match, Error Back
                            return 2;
                    }
            }

            if(empty($download_file) == FALSE) // Downloaded file exist in memory
            {
                    $fh = fopen($plugin_file, 'w'); // Open Plugin File for Writing

                    if($fh != FALSE)
                    {
                            if(fwrite($fh, $download_file) > 0) // Overwrite Downloaded File directly to Plugin File
                            {
                                    if(fclose($fh) == TRUE)
                                    {
                                            // Update Complete
                                            return TRUE;
                                    }
                                    else
                                    {
                                            // Update FAILED with a File Close Error
                                            return 3;
                                    }
                            }
                    }
                    else
                    {
                            // Update FAILED with Unable to Open File Error.
                            return 4;
                    }	
            }
            else
            {
                    // File Download Failed
                    return 5;
            }

            // Unknown Error
            return FALSE;
    }
    //***********************************************************************************
    function update_windows_port($new_port)
    {
            // Update the pms_config.ini file if it exist
            if(file_exists("../../pms_config.ini") == TRUE)
            {
                    //Previous port number
                    $old_port = mysql_result(mysql_query("SELECT * FROM `options` WHERE `field_name` = 'server_port_number' LIMIT 1"),0,"field_data");

                    if($old_port != $new_port)// Don't change unless different than before
                    {
                            $pms_config = file_get_contents('../../pms_config.ini');
                            $new_pms_config = str_replace("Port=$old_port", "Port=$new_port", $pms_config);

                            // Write new configuration file back to drive
                            $fh = fopen('../../pms_config.ini', 'w');

                            if($fh != FALSE)
                            {
                                    if(fwrite($fh, $new_pms_config) > 0)
                                    {
                                            if(fclose($fh) == TRUE)
                                            {
                                                    return TRUE;
                                            }
                                    }
                            }
                    }
            }
            return;
    }
    //***********************************************************************************
    //***********************************************************************************
    function generate_hashcode_permissions($pk_balance, $pk_gen_amt, $pk_recv, $send_tk, $pk_history, $pk_valid, $tk_trans_total, $pk_sent, $pk_gen_total, $tk_process_status, $tk_start_stop)
    {
            $permissions_number = 0;

            if($pk_balance == 1) { $permissions_number += 1; }
            if($pk_gen_amt == 1) { $permissions_number += 2; }
            if($pk_recv == 1) { $permissions_number += 4; }
            if($send_tk == 1) { $permissions_number += 8; }
            if($pk_history == 1) { $permissions_number += 16; }
            if($pk_valid == 1) { $permissions_number += 32; }
            if($tk_trans_total == 1) { $permissions_number += 64; }
            if($pk_sent == 1) { $permissions_number += 128; }
            if($pk_gen_total == 1) { $permissions_number += 256; }
            if($tk_process_status == 1) { $permissions_number += 512; }
            if($tk_start_stop == 1) { $permissions_number += 1024; }

            return $permissions_number;
    }
    //***********************************************************************************
    function check_hashcode_permissions($permissions_number, $pk_api_check, $checkbox = FALSE)
    {
            // tk_start_stop
            if($pk_api_check == "tk_start_stop")
            { 
                    if($permissions_number >= 1024) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 1024 >= 0) { $permissions_number -= 1024; } // Subtract Active Permission

            // tk_process_status
            if($pk_api_check == "tk_process_status")
            { 
                    if($permissions_number >= 512) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 512 >= 0) { $permissions_number -= 512; } // Subtract Active Permission

            // pk_gen_total
            if($pk_api_check == "pk_gen_total")
            { 
                    if($permissions_number >= 256) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 256 >= 0) { $permissions_number -= 256; } // Subtract Active Permission

            // pk_sent
            if($pk_api_check == "pk_sent")
            { 
                    if($permissions_number >= 128) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 128 >= 0) { $permissions_number -= 128; } // Subtract Active Permission

            // tk_trans_total
            if($pk_api_check == "tk_trans_total")
            { 
                    if($permissions_number >= 64) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 64 >= 0) { $permissions_number -= 64; } // Subtract Active Permission

            // pk_valid
            if($pk_api_check == "pk_valid")
            { 
                    if($permissions_number >= 32) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 32 >= 0) { $permissions_number -= 32; } // Subtract Active Permission

            // pk_history
            if($pk_api_check == "pk_history")
            { 
                    if($permissions_number >= 16) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 16 >= 0) { $permissions_number -= 16; } // Subtract Active Permission

            // send_tk
            if($pk_api_check == "send_tk")
            { 
                    if($permissions_number >= 8) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 8 >= 0) { $permissions_number -= 8; } // Subtract Active Permission

            // pk_recv
            if($pk_api_check == "pk_recv")
            { 
                    if($permissions_number >= 4) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 4 >= 0) { $permissions_number -= 4; } // Subtract Active Permission

            // pk_gen_amt
            if($pk_api_check == "pk_gen_amt")
            { 
                    if($permissions_number >= 2) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }
            if($permissions_number - 2 >= 0) { $permissions_number -= 2; } // Subtract Active Permission

            // pk_balance
            if($pk_api_check == "pk_balance") // Permission Granted
            { 
                    if($permissions_number >= 1) // Permission Granted
                    {
                            if($checkbox == TRUE)
                            {
                                    return "CHECKED";
                            }
                            else
                            {
                                    return TRUE;
                            }
                    }
                    else
                    {
                            return FALSE;
                    }
            }

            // Some other error
            return FALSE;
    }
}

<?php


class TransactionHistory {
    
    private $db;
    
    public function __construct(){
        $this->db = new Database();       
    }
    
    public function __construct1($database){
        $this->db = $database;       
    }
    
    public function __destruct() {
       
    }
    
    function transaction_history_hash()
    {
            $hash = mysql_result(mysql_query("SELECT COUNT(*) FROM `transaction_history`"),0);

            $previous_foundation_block = foundation_cycle(-1, TRUE);
            $current_foundation_cycle = foundation_cycle(0);
            $next_foundation_cycle = foundation_cycle(1);			

            $current_generation_block = transaction_cycle(0, TRUE);
            $current_foundation_block = foundation_cycle(0, TRUE);

            // Check to make sure enough lead time exist before another transaction foundation is built.
            // (50 blocks) or over 4 hours
            if($current_generation_block - ($current_foundation_block * 500) > 50)
            {
                    $current_history_foundation = mysql_result(mysql_query("SELECT * FROM `transaction_foundation` WHERE `block` = $previous_foundation_block LIMIT 1"),0,"hash");
                    $hash .= $current_history_foundation;
            }

            $sql = "SELECT hash FROM `transaction_history` WHERE `timestamp` >= $current_foundation_cycle AND `timestamp` < $next_foundation_cycle AND `attribute` = 'H' ORDER BY `timestamp` ASC";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_array($sql_result);
                    $hash .= $sql_row["hash"];
            }	

            return hash('md5', $hash);
    }
    
    function walkhistory($block_start = 0, $block_end = 0)
    {
            $current_generation_cycle = transaction_cycle(0);
            $current_generation_block = transaction_cycle(0, TRUE);	

            $wrong_timestamp = 0;
            $wrong_hash = 0;

            $first_wrong_block = 0;

            if($block_end == 0)
            {
                    $block_counter = $current_generation_block;
            }
            else
            {
                    $block_counter = $block_end + 1;
            }

            if($block_start == 0)
            {
                    $next_timestamp = TRANSACTION_EPOCH;
            }
            else
            {
                    $next_timestamp = TRANSACTION_EPOCH + ($block_start * 300);
            }

            for ($i = $block_start; $i < $block_counter; $i++)
            {
                    $time1 = transaction_cycle(0 - $current_generation_block + $i);
                    $time2 = transaction_cycle(0 - $current_generation_block + 1 + $i);	

                    $time3 = transaction_cycle(0 - $current_generation_block + 1 + $i);
                    $time4 = transaction_cycle(0 - $current_generation_block + 2 + $i);
                    $next_hash = mysql_result(mysql_query("SELECT hash FROM `transaction_history` WHERE `timestamp` >= $time3 AND `timestamp` < $time4 AND `attribute` = 'H' LIMIT 1"),0,0);

                    $sql = "SELECT timestamp, public_key_from, public_key_to, hash, attribute FROM `transaction_history` WHERE `timestamp` >= $time1 AND `timestamp` < $time2 ORDER BY `timestamp`, `hash` ASC";

                    $sql_result = mysql_query($sql);
                    $sql_num_results = mysql_num_rows($sql_result);
                    $my_hash = 0;

                    $timestamp = 0;

                    for ($h = 0; $h < $sql_num_results; $h++)
                    {
                            $sql_row = mysql_fetch_array($sql_result);

                            if($sql_row["attribute"] == "T" || $sql_row["attribute"] == "G")
                            {
                                    if(strlen($sql_row["public_key_from"]) > 300 && strlen($sql_row["public_key_to"]) > 300)
                                    {
                                            $my_hash .= $sql_row["hash"];
                                    }
                            }

                            if($sql_row["attribute"] == "H" || $sql_row["attribute"] == "B")
                            {
                                    $timestamp = $sql_row["timestamp"];

                                    $my_hash .= $sql_row["hash"];
                            }
                    }		

                    if($next_timestamp != $timestamp)
                    {
                            $wrong_timestamp++;
                            $first_wrong_block = $i;
                            break;
                    }

                    $next_timestamp = $next_timestamp + 300;

                    $my_hash = hash('sha256', $my_hash);

                    if($my_hash == $next_hash)
                    {
                            // Good match for hash
                    }
                    else
                    {
                            // Wrong match for hash
                            $wrong_hash++;
                            $first_wrong_block = $i;
                            break;
                    }
            }

            if($wrong_timestamp > 0 || $wrong_hash > 0)
            {
                    // Range of history walk contains errors, return the first block that the error
                    // started at
                    return $first_wrong_block;
            }
            else
            {
                    // No errors found
                    return 0;
            }
    }
    
    function visual_walkhistory($transaction_cycle_start = 0, $block_end = 0)
    {
            $output;

            $current_generation_block = transaction_cycle(0, TRUE);

            if($block_end <= $transaction_cycle_start)
            {
                    $block_end = $transaction_cycle_start + 1;
            }

            if($block_end > $current_generation_block)
            {
                    $block_end = $current_generation_block;
            }	

            $wrong_timestamp = 0;
            $wrong_block_numbers = NULL;
            $wrong_hash = 0;
            $wrong_hash_numbers = NULL;

            $next_timestamp = TRANSACTION_EPOCH + ($transaction_cycle_start * 300);

            for ($i = $transaction_cycle_start; $i < $block_end; $i++)
            {
                    $output .= '<tr><td class="style2">Transaction Cycle # ' . $i;
                    $time1 = transaction_cycle(0 - $current_generation_block + $i);
                    $time2 = transaction_cycle(0 - $current_generation_block + 1 + $i);	

                    $time3 = transaction_cycle(0 - $current_generation_block + 1 + $i);
                    $time4 = transaction_cycle(0 - $current_generation_block + 2 + $i);

                    $next_hash = mysql_result(mysql_query("SELECT hash FROM `transaction_history` WHERE `timestamp` >= $time3 AND `timestamp` < $time4 AND `attribute` = 'H' LIMIT 1"),0,0);

                    $sql = "SELECT timestamp, public_key_from, public_key_to, hash, attribute FROM `transaction_history` WHERE `timestamp` >= $time1 AND `timestamp` < $time2 ORDER BY `timestamp`, `hash` ASC";

                    $sql_result = mysql_query($sql);
                    $sql_num_results = mysql_num_rows($sql_result);
                    $my_hash = 0;
                    $timestamp = 0;

                    for ($h = 0; $h < $sql_num_results; $h++)
                    {
                            $sql_row = mysql_fetch_array($sql_result);

                            if($sql_row["attribute"] == "T" || $sql_row["attribute"] == "G")
                            {
                                    if(strlen($sql_row["public_key_from"]) > 300 && strlen($sql_row["public_key_to"]) > 300)
                                    {
                                            $my_hash .= $sql_row["hash"];
                                    }
                                    else
                                    {
                                            $output .= '<br><font color=blue>Public Key Length Wrong<br>Timestamp: [' . $sql_row["timestamp"] . ']<br>Hash: [' . $sql_row["hash"] . ']</font><br>';
                                    }
                            }

                            if($sql_row["attribute"] == "H" || $sql_row["attribute"] == "B")
                            {
                                    $timestamp = $sql_row["timestamp"];

                                    $my_hash .= $sql_row["hash"];
                            }
                    }		

                    if($next_timestamp != $timestamp)
                    {
                            $output .= '<br><font color=red><strong>Hash Timestamp Sequence Wrong... Should Be: ' . $next_timestamp . '</strong></font>';
                            $wrong_timestamp++;
                            $wrong_block_numbers .= " " . $i;
                    }

                    $next_timestamp = $next_timestamp + 300;

                    $my_hash = hash('sha256', $my_hash);

                    $output .= '<br>Timestamp in Database: ' . $timestamp;
                    $output .= '<br>Calculated Hash: ' . $my_hash;
                    $output .= '<br>&nbsp;Database Hash : ' . $next_hash;

                    if($my_hash == $next_hash)
                    {
                            $output .= '<br><font color=green>Hash Match...</font>';
                    }
                    else
                    {
                            $output .= '<br><font color=red><strong>Hash MISMATCH</strong></font></td></tr>';
                            $wrong_hash++;
                            $wrong_hash_numbers = $wrong_hash_numbers . " " . $i;			
                    }
            }

            if(empty($wrong_block_numbers) == TRUE)
            {
                    $wrong_block_numbers = '<font color="blue">None</font>';
            }

            if(empty($wrong_hash_numbers) == TRUE)
            {
                    $wrong_hash_numbers = '<font color="blue">None</font>';
            }

            $finish_output;

            $finish_output .= '<tr><td class="style2"><font color="blue"><strong>Total Wrong Sequence: ' . $wrong_timestamp . '</strong></font>';
            $finish_output .= '<br><font color="red"><strong>Transaction Cycles Wrong:</strong></font><strong> ' . $wrong_block_numbers . '</strong></td></tr>';
            $finish_output .= '<tr><td class="style2"><font color="blue"><strong>Total Wrong Hash: ' . $wrong_hash . '</strong></font>';
            $finish_output .= '<br><font color="red"><strong>Transaction Cycles Wrong:</strong></font><strong> ' . $wrong_hash_numbers . '</strong></td></tr>';

            return $finish_output . $output . $finish_output;
    }
    
    function visual_repair($transaction_cycle_start = 0, $cycle_limit = 500)
    {
            $current_transaction_cycle = transaction_cycle(0, TRUE);
            $output;

            if($cycle_limit == 0)
            {
                    $cycle_limit = transaction_cycle(0, TRUE);
            }

            if($transaction_cycle_start == 0)
            {
                    $transaction_cycle_start = 1;
            }

            $generation_arbitrary = ARBITRARY_KEY;

            // Wipe all blocks ahead
            $time_range1 = transaction_cycle(0 - $current_transaction_cycle + $transaction_cycle_start);
            $time_range2 = transaction_cycle(0 - $current_transaction_cycle + $transaction_cycle_start + $cycle_limit);

            $sql = "DELETE QUICK FROM `transaction_history` WHERE `transaction_history`.`timestamp` >= $time_range1 AND `transaction_history`.`timestamp` <= $time_range2 AND `attribute` = 'H'";

            if(mysql_query($sql) == TRUE)
            {
                    $output .= '<tr><td class="style2">Clearing Hash Timestamps Ahead of Transaction Cycle #' . $transaction_cycle_start . '</td></tr>';
            }
            else
            {
                    return '<tr><td class="style2">Database ERROR, stopping repair process...</td></tr>';
            }

            for ($t = $transaction_cycle_start; $t < $current_transaction_cycle; $t++)
            {
                    if($cycle_limit < 0) // Finished
                    {
                            break;
                    }

                    $output .= "<tr><td><strong>Repairing Transaction Cycle# $t</strong>";

                    $time1 = transaction_cycle(0 - $current_transaction_cycle - 1 + $t);
                    $time2 = transaction_cycle(0 - $current_transaction_cycle + $t);

                    $sql = "SELECT hash FROM `transaction_history` WHERE `timestamp` >= $time1 AND `timestamp` < $time2 ORDER BY `timestamp`, `hash` ASC";

                    $sql_result = mysql_query($sql);
                    $sql_num_results = mysql_num_rows($sql_result);
                    $hash = 0;

                    for ($i = 0; $i < $sql_num_results; $i++)
                    {
                            $sql_row = mysql_fetch_array($sql_result);
                            $hash .= $sql_row["hash"];
                    }

                    // Transaction hash
                    $hash = hash('sha256', $hash);

                    $sql = "INSERT INTO `transaction_history` (`timestamp` ,`public_key_from` ,`public_key_to` ,`crypt_data1` ,`crypt_data2` ,`crypt_data3` ,`hash` ,`attribute`)
                    VALUES ('$time2', '$generation_arbitrary', '$generation_arbitrary', '$generation_arbitrary', '$generation_arbitrary', '$generation_arbitrary', '$hash', 'H')";

                    if(mysql_query($sql) == FALSE)
                    {
                            // Something failed
                            $output .= '<br><strong><font color="red">Repair ERROR in Database</font></strong></td></tr>';
                    }
                    else
                    {
                            $output .= '<br><strong><font color="blue">Repair Complete...</font></strong></td></tr>';
                    }

                    $cycle_limit--;

            } // End for loop

            return $output;
    }
    
    function count_transaction_hash()
    {
            // Check server balance via custom memory index
            $count_transaction_hash = mysql_result(mysql_query("SELECT * FROM `balance_index` WHERE `public_key_hash` = 'count_transaction_hash' LIMIT 1"),0,"balance");
            $count_transaction_hash_last = mysql_result(mysql_query("SELECT * FROM `balance_index` WHERE `public_key_hash` = 'count_transaction_hash' LIMIT 1"),0,"block");

            if($count_transaction_hash === FALSE)
            {
                    // Does not exist, needs to be created
                    mysql_query("INSERT INTO `balance_index` (`block` ,`public_key_hash` ,`balance`) VALUES ('0', 'count_transaction_hash', '0')");

                    // Update record with the latest total
                    $total_trans_hash = mysql_result(mysql_query("SELECT COUNT(attribute) FROM `transaction_history` USE INDEX(attribute) WHERE `attribute` = 'H'"),0);
                    mysql_query("UPDATE `balance_index` SET `block` = '" . time() . "' , `balance` = '$total_trans_hash' WHERE `balance_index`.`public_key_hash` = 'count_transaction_hash' LIMIT 1");
            }
            else
            {
                    if(time() - $count_transaction_hash_last > 300) // 300s cache time
                    {
                            // Update new hash count and cache time
                            $total_trans_hash = mysql_result(mysql_query("SELECT COUNT(attribute) FROM `transaction_history` USE INDEX(attribute) WHERE `attribute` = 'H'"),0);
                            mysql_query("UPDATE `balance_index` SET `block` = '" . time() . "' , `balance` = '$total_trans_hash' WHERE `balance_index`.`public_key_hash` = 'count_transaction_hash' LIMIT 1");
                    }
                    else
                    {
                            $total_trans_hash = $count_transaction_hash;
                    }
            }

            return $total_trans_hash;
    }
    
    function reset_transaction_hash_count()
    {
            // Clear transaction count cache
            mysql_query("DELETE FROM `balance_index` WHERE `balance_index`.`public_key_hash` = 'count_transaction_hash' LIMIT 1");
            return;
    }
    
    function check_crypt_balance_range($public_key, $block_start = 0, $block_end = 0)
    {
            set_decrypt_mode(); // Figure out which decrypt method can be best used

            //Initialize objects for Internal RSA decrypt
            if($GLOBALS['decrypt_mode'] == 2)
            {
                    require_once('RSA.php');
                    $rsa = new Crypt_RSA();
                    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
            }

            if($block_start == 0 && $block_end == 0)// Find every TimeKoin ever sent to and from this public Key
            {
                    $sql = "SELECT public_key_from, public_key_to, crypt_data3, attribute FROM `transaction_history` WHERE `public_key_from` = '$public_key' OR `public_key_to` = '$public_key' ";
            }
            else
            {
                    // Find every TimeKoin sent to and from this public Key in a certain time range.
                    // Covert block to time.
                    $start_time_range = TRANSACTION_EPOCH + ($block_start * 300);
                    $end_time_range = TRANSACTION_EPOCH + ($block_end * 300);

                    $sql = "SELECT public_key_from, public_key_to, crypt_data3, attribute FROM `transaction_history` WHERE (`public_key_from` = '$public_key' AND `timestamp` >= '$start_time_range' AND `timestamp` < '$end_time_range')
                    OR (`public_key_to` = '$public_key' AND `timestamp` >= '$start_time_range' AND `timestamp` < '$end_time_range')";
            }

            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);
            $crypto_balance = 0;
            $transaction_info;

            for ($i = 0; $i < $sql_num_results; $i++)
            {
                    $sql_row = mysql_fetch_row($sql_result);

                    $public_key_from = $sql_row[0];
                    $public_key_to = $sql_row[1];
                    $crypt3 = $sql_row[2];
                    $attribute = $sql_row[3];

                    if($attribute == "G" && $public_key_from == $public_key_to) // Everything generated by this public key
                    {
                            // Currency Generation
                            // Decrypt transaction information
                            if($GLOBALS['decrypt_mode'] == 2)
                            {
                                    $rsa->loadKey($public_key_from);
                                    $transaction_info = $rsa->decrypt(base64_decode($crypt3));
                            }
                            else
                            {
                                    $transaction_info = tk_decrypt($public_key_from, base64_decode($crypt3), TRUE);
                            } 

                            $transaction_amount_sent = find_string("AMOUNT=", "---TIME", $transaction_info);
                            $crypto_balance += $transaction_amount_sent;
                    }

                    if($attribute == "T" && $public_key_to == $public_key) // Everything given to this public key
                    {
                            // Decrypt transaction information
                            if($GLOBALS['decrypt_mode'] == 2)
                            {
                                    $rsa->loadKey($public_key_from);
                                    $transaction_info = $rsa->decrypt(base64_decode($crypt3));
                            }
                            else
                            {
                                    $transaction_info = tk_decrypt($public_key_from, base64_decode($crypt3), TRUE);
                            }

                            $transaction_amount_sent = find_string("AMOUNT=", "---TIME", $transaction_info);
                            $crypto_balance += $transaction_amount_sent;
                    }

                    if($attribute == "T" && $public_key_from == $public_key) // Everything spent from this public key
                    {
                            // Decrypt transaction information
                            $transaction_info = tk_decrypt($public_key_from, base64_decode($crypt3));

                            if($GLOBALS['decrypt_mode'] == 2)
                            {
                                    $rsa->loadKey($public_key_from);
                                    $transaction_info = $rsa->decrypt(base64_decode($crypt3));
                            }
                            else
                            {
                                    $transaction_info = tk_decrypt($public_key_from, base64_decode($crypt3), TRUE);
                            }

                            $transaction_amount_sent = find_string("AMOUNT=", "---TIME", $transaction_info);
                            $crypto_balance -= $transaction_amount_sent;
                    }		
            }

            // Unset variable to free up RAM
            unset($sql_result);

            return $crypto_balance;
    }
    
    function check_crypt_balance($public_key)
    {
            if(empty($public_key) == TRUE)
            {
                    return 0;
            }

            // Do we already have an index to reference for faster access?
            $public_key_hash = hash('md5', $public_key);
            $current_transaction_block = transaction_cycle(0, TRUE);
            $current_foundation_block = foundation_cycle(0, TRUE);

            // Check to make sure enough lead time exist in advance to building
            // another balance index. (60 cycles) or 5 hours
            if($current_transaction_block - ($current_foundation_block * 500) > 60)
            {
                    // -1 Foundation Blocks (Standard)
                    $previous_foundation_block = foundation_cycle(-1, TRUE);
            }
            else
            {
                    // -2 Foundation Blocks - Buffers 5 hours after the newest foundation block
                    $previous_foundation_block = foundation_cycle(-2, TRUE);
            }

            $sql = "SELECT block, balance FROM `balance_index` WHERE `block` = $previous_foundation_block AND `public_key_hash` = '$public_key_hash' LIMIT 1";
            $sql_result = mysql_query($sql);
            $sql_row = mysql_fetch_array($sql_result);

            if(empty($sql_row["block"]) == TRUE)// No index exist yet, so after the balance check is complete, record the result for later use
            {
                    // Check if a Quantum Balance Index exist to shorten database access time
                    $pk_md5 = hash('md5', $public_key);
                    $sql2 = "SELECT max_foundation, balance FROM `quantum_balance_index` WHERE `public_key_hash` = '$pk_md5' LIMIT 1";
                    $sql_result2 = mysql_query($sql2);
                    $sql_row2 = mysql_fetch_array($sql_result2);

                    if(empty($sql_row2["max_foundation"]) == TRUE)// No Quantum Balance Index exist for this Public Key
                    {
                            // How many Transaction Foundations Should QBI Cover for Range?
                            // All Transaction Foundations up to the Last 500 
                            // So 761 would only be the first 500, 1256 would only be the first 1000, etc.
                            $qbi_max_foundation = (intval($current_foundation_block / 500)) * 500;

                            // Does this many Transaction Foundations even exist to index against?
                            $total_foundations = mysql_result(mysql_query("SELECT COUNT(*) FROM `transaction_foundation`"),0);

                            if($total_foundations > $qbi_max_foundation)
                            {
                                    // Create time range
                                    $qbi_end_time_range = $qbi_max_foundation * 500;
                                    $qbi_balance = check_crypt_balance_range($public_key, 0, $qbi_end_time_range);

                                    // Store QBI in database for more permanent future access
                                    mysql_query("INSERT INTO `quantum_balance_index` (`public_key_hash` ,`max_foundation` ,`balance`)
                                            VALUES ('$pk_md5', '$qbi_max_foundation', '$qbi_balance')");
                            }
                            else
                            {
                                    write_log("Incomplete Transaction History Unable to Create Quantum Balance Index", "BA");
                            }
                    }
                    else
                    {
                            // Quantum Balance Index exist, use the balance recorded and the remaining time range afterwards that this QBI represents
                            $qbi_max_foundation = $sql_row2["max_foundation"];
                            $qbi_balance = $sql_row2["balance"];
                    }		

                    // Use QBI to Decrease DB time to calculate Public Key Balance
                    // Create time range
                    $start_time_range = $qbi_max_foundation * 500;
                    $end_time_range = $previous_foundation_block * 500;
                    $index_balance1 = check_crypt_balance_range($public_key, $start_time_range, $end_time_range);

                    // Add in QBI Balance
                    $index_balance1 += $qbi_balance;

                    // Check balance between the last block and now
                    $start_time_range = $end_time_range;
                    $end_time_range = transaction_cycle(0, TRUE);
                    $index_balance2 = check_crypt_balance_range($public_key, $start_time_range, $end_time_range);

                    // Store index in database for future access
                    mysql_query("INSERT INTO `balance_index` (`block` ,`public_key_hash` ,`balance`)
                    VALUES ('$previous_foundation_block', '$public_key_hash', '$index_balance1')");
                    return ($index_balance1 + $index_balance2);
            }
            else // More Recent Index Available
            {
                    $crypto_balance = $sql_row["balance"];

                    // Check balance between the last block and now
                    $start_time_range = $previous_foundation_block * 500;
                    $end_time_range = transaction_cycle(0, TRUE);
                    $index_balance = check_crypt_balance_range($public_key, $start_time_range, $end_time_range);		
                    return ($crypto_balance + $index_balance);
            }
    }
    
    function db_cache_balance($my_public_key)
    {
            // Check server balance via custom memory index
            $my_server_balance = mysql_result(mysql_query("SELECT balance FROM `balance_index` WHERE `public_key_hash` = 'server_timekoin_balance' LIMIT 1"),0,0);
            $my_server_balance_last = mysql_result(mysql_query("SELECT block FROM `balance_index` WHERE `public_key_hash` = 'server_timekoin_balance' LIMIT 1"),0,0);

            if($my_server_balance === FALSE)
            {
                    // Does not exist, needs to be created
                    mysql_query("INSERT INTO `balance_index` (`block` ,`public_key_hash` ,`balance`) VALUES ('0', 'server_timekoin_balance', '0')");

                    // Update record with the latest balance
                    $display_balance = check_crypt_balance($my_public_key);

                    mysql_query("UPDATE `balance_index` SET `block` = '" . time() . "' , `balance` = '$display_balance' WHERE `balance_index`.`public_key_hash` = 'server_timekoin_balance' LIMIT 1");
            }
            else
            {
                    if($my_server_balance_last < transaction_cycle(0) && time() - transaction_cycle(0) > 25) // Generate 25 seconds after cycle
                    {
                            // Last generated balance is older than the current cycle, needs to be updated
                            // Update record with the latest balance
                            $display_balance = check_crypt_balance($my_public_key);

                            mysql_query("UPDATE `balance_index` SET `block` = '" . time() . "' , `balance` = '$display_balance' WHERE `balance_index`.`public_key_hash` = 'server_timekoin_balance' LIMIT 1");
                    }
                    else
                    {
                            $display_balance = $my_server_balance;
                    }
            }

            return $display_balance;
    }
}

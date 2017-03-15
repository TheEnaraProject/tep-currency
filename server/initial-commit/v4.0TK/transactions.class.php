<?php


class Transactions {
    
    public function __construct(){
        
    }
    
    function transaction_cycle($past_or_future = 0, $transacton_cycles_only = 0)
    {
            $transacton_cycles = (time() - TRANSACTION_EPOCH) / 300;

            // Return the last transaction cycle
            if($transacton_cycles_only == TRUE)
            {
                    return intval($transacton_cycles + $past_or_future);
            }
            else
            {
                    return TRANSACTION_EPOCH + (intval($transacton_cycles + $past_or_future) * 300);
            }
    }
    
    function send_timekoins($my_private_key, $my_public_key, $send_to_public_key, $amount, $message)
    {
            $arr1 = str_split($send_to_public_key, 181);

            $encryptedData1 = tk_encrypt($my_private_key, $arr1[0]);
            $encryptedData64_1 = base64_encode($encryptedData1);	

            $encryptedData2 = tk_encrypt($my_private_key, $arr1[1]);
            $encryptedData64_2 = base64_encode($encryptedData2);

            // Sanitization of message
            // Filter symbols that might lead to a transaction hack attack
            $symbols = array("|", "?", "="); // SQL + URL
            $message = str_replace($symbols, "", $message);

            // Trim any message to 64 characters max and filter any sql
            $message = filter_sql(substr($message, 0, 64));
            $transaction_data = "AMOUNT=$amount---TIME=" . time() . "---HASH=" . hash('sha256', $encryptedData64_1 . $encryptedData64_2) . "---MSG=$message";
            $encryptedData3 = tk_encrypt($my_private_key, $transaction_data);

            $encryptedData64_3 = base64_encode($encryptedData3);
            $triple_hash_check = hash('sha256', $encryptedData64_1 . $encryptedData64_2 . $encryptedData64_3);

            $sql = "INSERT INTO `my_transaction_queue` (`timestamp`,`public_key`,`crypt_data1`,`crypt_data2`,`crypt_data3`, `hash`, `attribute`) VALUES 
                    ('" . time() . "', '$my_public_key', '$encryptedData64_1', '$encryptedData64_2' , '$encryptedData64_3', '$triple_hash_check' , 'T')";

            if(mysql_query($sql) == TRUE)
            {
                    // Success code
                    return TRUE;
            }
            else
            {
                    return FALSE;
            }
    }
    
    
}

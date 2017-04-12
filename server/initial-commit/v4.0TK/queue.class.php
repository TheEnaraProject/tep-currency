<?php


class Queue {
    
    private $db;
    
    public function __construct(){
        $this->db = new Database();       
    }
    
    public function __construct1($database){
        $this->db = $database;       
    }
    
    public function __destruct() {
       
    }
    
    function queue_hash()
    {
            $sql = "SELECT * FROM `transaction_queue` ORDER BY `hash`, `timestamp` ASC";
            $sql_result = mysql_query($sql);
            $sql_num_results = mysql_num_rows($sql_result);

            $transaction_queue_hash = 0;

            if($sql_num_results > 0)
            {
                    for ($i = 0; $i < $sql_num_results; $i++)
                    {
                            $sql_row = mysql_fetch_array($sql_result);
                            $transaction_queue_hash .= $sql_row["timestamp"] . $sql_row["public_key"] . $sql_row["crypt_data1"] . 
                            $sql_row["crypt_data2"] . $sql_row["crypt_data3"] . $sql_row["hash"] . $sql_row["attribute"];
                    }

                    return hash('md5', $transaction_queue_hash);
            }

            return 0;
    }

}

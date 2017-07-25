<?php


class Foundations {
        
    public function __construct(){
        
    }
    
    function foundation_cycle($past_or_future = 0, $foundation_cycles_only = 0)
    {
            $foundation_cycles = (time() - TRANSACTION_EPOCH) / 150000;

            // Return the last transaction cycle
            if($foundation_cycles_only == TRUE)
            {
                    return intval($foundation_cycles + $past_or_future);
            }
            else
            {
                    return TRANSACTION_EPOCH + (intval($foundation_cycles + $past_or_future) * 150000);
            }
    }
}

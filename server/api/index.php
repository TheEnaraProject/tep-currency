<?php

function __autoload($class_name) {
    if(file_exists(__DIR__.'/classes/'.strtolower($class_name.'.class.php'))) {
        require_once(__DIR__.'/classes/'.strtolower($class_name.'.class.php'));    
    } else {
        throw new Exception("Unable to load ".__DIR__.'/classes/'.strtolower($class_name.'.class.php'));
    }
}
 
try {
    // Initiate Library
    $api = new API();
    $api->processApi(); 
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
       
?>
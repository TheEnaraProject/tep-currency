<?php


class UI {
    
    function standard_tab_settings($peerlist, $trans_queue, $send_receive, $history, $generation, $system, $backup, $tools)
    {
            $permissions_number = 0;

            if($peerlist == 1) { $permissions_number += 1; }
            if($trans_queue == 1) { $permissions_number += 2; }
            if($send_receive == 1) { $permissions_number += 4; }
            if($history == 1) { $permissions_number += 8; }
            if($generation == 1) { $permissions_number += 16; }
            if($system == 1) { $permissions_number += 32; }
            if($backup == 1) { $permissions_number += 64; }
            if($tools == 1) { $permissions_number += 128; }

            return $permissions_number;
    }
    //***********************************************************************************
    //***********************************************************************************
    function check_standard_tab_settings($permissions_number, $standard_tab)
    {
    // Tools Tab
            if($permissions_number - 256 >= 0) { $permissions_number -= 256; } // Subtract Active Permission
            if($standard_tab == 128)
            { 
                    if($permissions_number >= 128) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // Backup Tab
            if($permissions_number - 128 >= 0) { $permissions_number -= 128; } // Subtract Active Permission
            if($standard_tab == 64)
            { 
                    if($permissions_number >= 64) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // System Tab
            if($permissions_number - 64 >= 0) { $permissions_number -= 64; } // Subtract Active Permission
            if($standard_tab == 32)
            { 
                    if($permissions_number >= 32) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }	

    // Generation Tab
            if($permissions_number - 32 >= 0) { $permissions_number -= 32; } // Subtract Active Permission
            if($standard_tab == 16)
            { 
                    if($permissions_number >= 16) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // History Tab
            if($permissions_number - 16 >= 0) { $permissions_number -= 16; } // Subtract Active Permission
            if($standard_tab == 8)
            { 
                    if($permissions_number >= 8) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // Send / Receive Queue Tab
            if($permissions_number - 8 >= 0) { $permissions_number -= 8; } // Subtract Active Permission
            if($standard_tab == 4)
            { 
                    if($permissions_number >= 4) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // Transaction Queue Tab
            if($permissions_number - 4 >= 0) { $permissions_number -= 4; } // Subtract Active Permission
            if($standard_tab == 2)
            { 
                    if($permissions_number >= 2) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

    // Peerlist Tab
            if($permissions_number - 2 >= 0) { $permissions_number -= 2; } // Subtract Active Permission
            if($standard_tab == 1)
            { 
                    if($permissions_number >= 1) // Show Tab
                    {
                            return TRUE;
                    }
                    else
                    {
                            return FALSE;
                    }
            }

            // Some other error
            return FALSE;
    }
    //***********************************************************************************
    //***********************************************************************************
    function file_upload($http_file_name)
    {
            $user_file_upload = strtolower(basename($_FILES[$http_file_name]['name']));

            if(move_uploaded_file($_FILES[$http_file_name]['tmp_name'], "plugins/" . $user_file_upload) == TRUE)
            {
                    // Upload successful
                    return $user_file_upload;
            }
            else
            {
                    // Error during upload
                    return FALSE;
            }	
    }
    //***********************************************************************************
    //***********************************************************************************
    function read_plugin($filename)
    {
            $handle = fopen($filename, "r");
            $contents = stream_get_contents($handle);
            fclose($handle);
            return $contents;
    }
    //***********************************************************************************
    //***********************************************************************************

}

<?PHP
include 'status.php';
include 'utilities.class.php';

//error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR); // Disable most error reporting except for fatal errors
//ini_set('display_errors', FALSE);

error_reporting(E_ALL);
ini_set('display_errors', 1);





// Depracted - use Utilities members for this information
define("TRANSACTION_EPOCH","1338576300"); // Epoch timestamp: 1338576300
define("ARBITRARY_KEY","01110100011010010110110101100101"); // Space filler for non-encryption data
define("SHA256TEST","8c49a2b56ebd8fc49a17956dc529943eb0d73c00ee6eafa5d8b3ba1274eb3ea4"); // Known SHA256 Test Result
define("TIMEKOIN_VERSION","4.0"); // This Timekoin Software Version
define("NEXT_VERSION","current_version51.txt"); // What file to check for future versions

//***********************************************************************************
//Depracated - use peers->ip_banned($ip)
//***********************************************************************************
function ip_banned($ip)
{
	// Check for banned IP address
        $p = new Peers;
	return $p->ip_banned($ip);
}
//***********************************************************************************
////Depracated - use Utilities->filter_sql($string)
//***********************************************************************************
function filter_sql($string)
{
	// Filter symbols that might lead to an SQL injection attack
	return Utilities::filter_sql($string);
}
//***********************************************************************************
////Depracated - use Peers->log_ip($attribute, $multiple, $super_peer_check)
//***********************************************************************************
function log_ip($attribute, $multiple = 1, $super_peer_check = FALSE)
{
	$p = new Peers;
	return $p->log_ip($attribute, $multiple, $super_peer_check);
}
//***********************************************************************************
//Depracated - use Peers->scale_trigger($trigger)
//***********************************************************************************
function scale_trigger($trigger = 100)
{
	// Scale the amount of copies of the IP based on the trigger set.
	// So for example, a trigger of 1 means that one event can trigger flood protection.
	// A trigger of 2 means 2 events will trigger flood protection. So only half as many
	// IP copies are returned in this function.
    $p = new Peers;
	return $p->scale_trigger($trigger);
}
//***********************************************************************************
//Depracated - use Utilities::find_string($start_tag,$end_tag,$full_string, $end_match)
//***********************************************************************************
function find_string($start_tag, $end_tag, $full_string, $end_match = FALSE)
{
	return Utilities::find_string($start_tag,$end_tag,$full_string, $end_match);
}
//***********************************************************************************
//Depracated - use Utilities::write_log($message, $type)
//***********************************************************************************
function write_log($message, $type)
{
	// Write Log Entry
	return Utilities::write_log($message, $type);
}
//***********************************************************************************
//Depracated - use Generation->generation_peer_hash()
//***********************************************************************************
function generation_peer_hash()
{
	$g = new Generation;
        return $g->generation_peer_hash();
}
//***********************************************************************************
//Depracated - use Transactions->transaction_cycle($past_or_future, $transacton_cycles_only)
//***********************************************************************************
function transaction_cycle($past_or_future = 0, $transacton_cycles_only = 0)
{
	$t = new Transactions;
        return $t->transaction_cycle($past_or_future, $transacton_cycles_only);
}
//***********************************************************************************
//Depracated - use Foundations->foundation_cycle($past_or_future, $foundation_cycles_only)
//***********************************************************************************
function foundation_cycle($past_or_future = 0, $foundation_cycles_only = 0)
{
	$f = new Foundations;
        return $f->foundation_cycle($past_or_future, $foundation_cycles_only);
}
//***********************************************************************************
//Depracated - use Transactions->transaction_history_hash()
//***********************************************************************************
function transaction_history_hash()
{
	$h = new TransactionHistory();
        return $h->transaction_history_hash();
}
//***********************************************************************************
//Depracated - use Queue->queue-hash()
//***********************************************************************************
function queue_hash()
{
	$q = new Queue;
        return $q->queue_hash();
}
//***********************************************************************************

function filter_public_key($public_key)
{
	return Utilities::filter_public_key($public_key);
}
//***********************************************************************************

function perm_peer_mode()
{
	$p = new Peers;
        return $p->perm_peer_mode();
}
//***********************************************************************************

function my_public_key()
{
	$s = new System;
        return $s->my_public_key();
}
//***********************************************************************************

function my_private_key()
{
	$s = new System;
        return $s->my_private_key();
}
//***********************************************************************************

function my_subfolder()
{
	$s = new System;
        return $s->my_subfolder();
}
//***********************************************************************************

function my_port_number()
{
	$s = new System;
        return $s->my_port_number();
}
//***********************************************************************************

function my_domain()
{
	$s = new System;
        return $s->my_domain();
}
//***********************************************************************************

function modify_peer_grade($ip_address, $domain, $subfolder, $port_number, $grade)
{
	$p= new Peers;
        return $p->modify_peer_grade($ip_address,$domain,$subfolder, $port_number,$grade);
}
//***********************************************************************************

function poll_peer($ip_address, $domain, $subfolder, $port_number, $max_length, $poll_string, $custom_context)
{
	$p= new Peers;
        return $p->poll_peer($ip_address,$domain,$subfolder, $port_number,$max_length, $poll_string, $custom_context);
}
//***********************************************************************************

function call_script($script, $priority = 1, $plugin = FALSE, $web_server_call = FALSE)
{
	$s = new System;
        return $s->call_script($script, $priority, $plugin, $web_server_call);
}
//***********************************************************************************
function clone_script($script)
{
	$s = new System;
        return $s->clone_script($script);
}
//***********************************************************************************
function walkhistory($block_start = 0, $block_end = 0)
{
	$h = new TransactionHistory;
        return $h->walkhistory($block_start, $block_end);
}
//***********************************************************************************
//***********************************************************************************
function count_transaction_hash()
{
	$h = new TransactionHistory;
        return $h->count_transaction_hash();
}
//***********************************************************************************
//***********************************************************************************
function reset_transaction_hash_count()
{
	$h = new TransactionHistory;
        return $h->reset_transaction_hash_count();
}
//***********************************************************************************
//***********************************************************************************
function tk_encrypt($key, $crypt_data)
{
	return Utilities::tk_encrypt($key, $crypt_data);
}
//***********************************************************************************
//***********************************************************************************
function set_decrypt_mode()
{
	return Utilities::set_decrypt_mode();
}
//***********************************************************************************
//***********************************************************************************
function tk_decrypt($key, $crypt_data, $skip_openssl_check = FALSE)
{
	return Utilities::tk_decrypt($key, $crypt_data, $skip_openssl_check);
}
//***********************************************************************************
//***********************************************************************************
function check_crypt_balance_range($public_key, $block_start = 0, $block_end = 0)
{
	$h = new TransactionHistory;
        return $h->check_crypt_balance_range($public_key, $block_start, $block_end);
}
//***********************************************************************************
//***********************************************************************************
function check_crypt_balance($public_key)
{
	$h = new TransactionHistory;
        return $h->check_crypt_balance($public_key);
}
//***********************************************************************************
//***********************************************************************************
function peer_gen_amount($public_key)
{
	$p = new Peers;
        return $p->peer_gen_amount($public_key);
}
//***********************************************************************************
//***********************************************************************************
class TKRandom
{
	// random seed
	private static $RSeed = 0;
	// set seed
	public static function seed($s = 0)
  	{
		self::$RSeed = abs(intval($s)) % 9999999 + 1;
		self::num();
	}
	// generate random number
	public static function num($min = 0, $max = 2147483647)
  	{
		if (self::$RSeed == 0) self::seed(mt_rand());
		self::$RSeed = (self::$RSeed * 125) % 2796203;
		return self::$RSeed % ($max - $min + 1) + $min;
	}
}
//***********************************************************************************
//***********************************************************************************
function getCharFreq($str,$chr=false)
{
	return Utilities::getCharFreq($str, $chr);
}
//***********************************************************************************
//***********************************************************************************
function scorePublicKey($public_key, $score_key = FALSE)
{
	$g = new Generation;
        return $g->scorePublicKey($public_key, $score_key);
}
//***********************************************************************************
//***********************************************************************************
function tk_time_convert($time)
{
	return Utilities::tk_time_convert($time);
}
//***********************************************************************************
//***********************************************************************************
function election_cycle($when = 0, $ip_type = 1, $gen_peers_total = 0)
{
	$g = new Generation;
        return $g->election_cycle($when, $ip_type, $gen_peers_total);
}
//***********************************************************************************
//***********************************************************************************
function generation_cycle($when = 0)
{
	$g = new Generation;
        return $g->generation_cycle($when);
}
//***********************************************************************************
//***********************************************************************************
function db_cache_balance($my_public_key)
{
	$h = new TransactionHistory;
        return $h->db_cache_balance($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function send_timekoins($my_private_key, $my_public_key, $send_to_public_key, $amount, $message)
{
	$t = new Transactions;
        return $t->send_timekoins($my_private_key, $my_public_key, $send_to_public_key, $amount, $message);
}
//***********************************************************************************
//***********************************************************************************
function unix_timestamp_to_human($timestamp = "", $default_timezone, $format = 'D d M Y - H:i:s')
{
	return Utilities::unix_timestamp_to_human($timestamp, $default_timezone, $format);
}
//***********************************************************************************
function gen_simple_poll_test($ip_address, $domain, $subfolder, $port_number)
{
	$g = new Generation;
        return $g->gen_simple_poll_test($ip_address, $domain, $subfolder, $port_number);
}
//***********************************************************************************
function visual_walkhistory($transaction_cycle_start = 0, $block_end = 0)
{
	$h = new TransactionHistory;
        return $h->visual_walkhistory($transaction_cycle_start, $block_end);
}
//***********************************************************************************
//***********************************************************************************
function visual_repair($transaction_cycle_start = 0, $cycle_limit = 500)
{
	$h = new TransactionHistory;
        return $h->visual_repair($transaction_cycle_start, $cycle_limit);
}
//***********************************************************************************
//***********************************************************************************
function is_private_ip($ip, $ignore = FALSE)
{
	$p = new Peers;
        return $p->is_private_ip($ip, $ignore);
}
//***********************************************************************************
function is_domain_valid($domain)
{
	$p = new Peers;
            return $p->is_domain_valid($domain);
}
//***********************************************************************************
function auto_update_IP_address()
{
            $p = new Peers;
            return $p->auto_update_IP_address();
}
//***********************************************************************************
function initialization_database()
{
	$s = new System;
        return $s->initialization_database();
}
//***********************************************************************************
//***********************************************************************************
function activate($component = "SYSTEM", $on_or_off = 1)
{
	$s = new System;
        return $s->activate($component, $on_or_off);
}
//***********************************************************************************
//***********************************************************************************	
function generate_new_keys()
{
	$s = new System;
        return $s->generate_new_keys();
}
//***********************************************************************************	
//***********************************************************************************
function check_for_updates($code_feedback = FALSE)
{
	$s = new System;
        return $s->check_for_updates($code_feedback);
}
//***********************************************************************************
//***********************************************************************************
function install_update_script($script_name, $script_file)
{
	$s = new System;
        return $s->install_update_script($script_name, $script_file);
}
//***********************************************************************************
//***********************************************************************************
function check_update_script($script_name, $script, $php_script_file, $poll_version, $context)
{
	$s = new System;
        return $s->check_update_script($script_name, $script, $php_script_file, $poll_version, $context);
}
//***********************************************************************************
//***********************************************************************************
function get_update_script($php_script, $poll_version, $context)
{
	$s = new System;
        return $s->get_update_script($php_script, $poll_version, $context);
}
//***********************************************************************************
//***********************************************************************************
function run_script_update($script_name, $script_php, $poll_version, $context, $php_format = 1, $sub_folder = "")
{
	$s = new System;
        return $s->run_script_update($script_name, $script_php, $poll_version, $context, $php_format = 1, $sub_folder = "");
}
//***********************************************************************************
function do_updates()
{
	$s = new System;
        return $s->do_updates();
}
//***********************************************************************************
//***********************************************************************************
function plugin_check_for_updates($http_url, $ssl_enable = FALSE)
{
	$s = new System;
        return $s->plugin_check_for_updates($http_url, $ssl_enable = FALSE);
}
//***********************************************************************************
function plugin_download_update($http_url, $http_url_sha256, $ssl_enable = FALSE, $plugin_file)
{
	$s = new System;
        return $s->plugin_download_update($http_url, $http_url_sha256, $ssl_enable = FALSE, $plugin_file);
}
//***********************************************************************************
function update_windows_port($new_port)
{
	$s = new System;
        return $s->update_windows_port($new_port);
}
//***********************************************************************************
//***********************************************************************************
function generate_hashcode_permissions($pk_balance, $pk_gen_amt, $pk_recv, $send_tk, $pk_history, $pk_valid, $tk_trans_total, $pk_sent, $pk_gen_total, $tk_process_status, $tk_start_stop)
{
	$s = new System;
        return $s->generate_hashcode_permissions($pk_balance, $pk_gen_amt, $pk_recv, $send_tk, $pk_history, $pk_valid, $tk_trans_total, $pk_sent, $pk_gen_total, $tk_process_status, $tk_start_stop);
}
//***********************************************************************************
function check_hashcode_permissions($permissions_number, $pk_api_check, $checkbox = FALSE)
{
	$s = new System;
        return $s->update_windows_port($new_port);
}
//***********************************************************************************
//***********************************************************************************
function standard_tab_settings($peerlist, $trans_queue, $send_receive, $history, $generation, $system, $backup, $tools)
{
	$u = new UI();
    return $u->standard_tab_settings($peerlist, $trans_queue, $send_receive, $history, $generation, $system, $backup, $tools);
}
//***********************************************************************************
//***********************************************************************************
function check_standard_tab_settings($permissions_number, $standard_tab)
{
    $u = new UI();
    return $u->check_standard_tab_settings($permissions_number, $standard_tab);
}
//***********************************************************************************
//***********************************************************************************
function file_upload($http_file_name)
{
	$u = new UI();
    return $u->file_upload($http_file_name);
}
//***********************************************************************************
//***********************************************************************************
function read_plugin($filename)
{
	$u = new UI();
    return $u->read_plugin($filename);
}
//***********************************************************************************
//***********************************************************************************
function ipv6_test($ip_address)
{
	$p = new Peers;
        return $p->ipv6_test($ip_address);
}
//***********************************************************************************
//***********************************************************************************
function ipv6_compress($ip_address)
{
	$p = new Peers;
        return $p->ipv6_compress($ip_address);
}
//***********************************************************************************
//***********************************************************************************
function find_v4_gen_key($my_public_key)
{
	$g = new Generation;
        return $g->find_v4_gen_key($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function find_v6_gen_key($my_public_key)
{
		$g = new Generation;
        return $g->find_v6_gen_key($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function find_v4_gen_IP($my_public_key)
{
	$g = new Generation;
        return $g->find_v4_gen_IP($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function find_v6_gen_IP($my_public_key)
{
	$g = new Generation;
        return $g->find_v6_gen_IP($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function find_v4_gen_join($my_public_key)
{
		$g = new Generation;
        return $g->find_v4_gen_join($my_public_key);
}
//***********************************************************************************
//***********************************************************************************
function find_v6_gen_join($my_public_key)
{
	$g = new Generation;
        return $g->find_v6_gen_join($my_public_key);
}
//***********************************************************************************

?>

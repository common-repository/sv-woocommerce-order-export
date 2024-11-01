<?php
	/*
	Plugin Name: SV WooCommerce Order Export
	Plugin URI: https://straightvisions.com/
	Description: Exports WooCommerce Order Data
	Version: 1.1.2
	Author: Matthias Reuter
	Author URI: https://straightvisions.com
	Text Domain: sv_woocommerce_order_export
	License: GPL3
	License URI: https://www.gnu.org/licenses/gpl-3.0.html
	*/

// deactivate error output if not explicite activated
if(!defined('WP_DEBUG_DISPLAY')){
	define('WP_DEBUG_DISPLAY', false);
}

define('SV_WOOCOMMERCE_ORDER_EXPORT_DIR',WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)).'/');
define('SV_WOOCOMMERCE_ORDER_EXPORT_PLUGIN_URL',plugins_url( '' , __FILE__ ).'/');
define('SV_WOOCOMMERCE_ORDER_EXPORT_VERSION', 1011);

class sv_woocommerce_order_export{
	private	$module											= false;
	private $orders											= array();
	private $products										= array();
	private $stats											= NULL;
	private $global_subscriptions_settings					= false;
	private $user_subscriptions_settings					= false;
	private $stepping										= 200;

	public function __construct(){
		require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'/lib/common/stats.inc.php');
		$this->stats			= new sv_woocommerce_order_export_stats();
		
		require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'/lib/common/settings.inc.php');
		$this->settings			= new sv_woocommerce_order_export_settings();
		
		register_activation_hook(__FILE__,array($this,'install'));
		register_deactivation_hook(__FILE__,array($this,'uninstall'));
		add_action('admin_init', array($this,'admin_init'));
		add_action('admin_menu', array($this->settings, 'get_settings_menu'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_filter('plugin_action_links', array($this->settings,'plugin_action_links'), 10, 5);
	}
	// init
	public function admin_init(){
		if(intval(get_option('sv_woocommerce_order_export_version')) < SV_WOOCOMMERCE_ORDER_EXPORT_VERSION){
			// 1.0.3
			if(intval(get_option('sv_woocommerce_order_export_version')) < 1003){
				$this->add_capabilities(); // update caps
			}
			// 1.0.4
			if(intval(get_option('sv_woocommerce_order_export_version')) < 1004){
				$new_format = array('fields' => unserialize(get_option('sv_woocommerce_order_export_settings')));
				update_option('sv_woocommerce_order_export_settings', $new_format, false);
				
				update_option('sv_woocommerce_order_export_filter',unserialize(get_option('sv_woocommerce_order_export_filter')));
				update_option('sv_woocommerce_order_export_subscriptions_settings',unserialize(get_option('sv_woocommerce_order_export_subscriptions_settings')));
			}
				
			update_option('sv_woocommerce_order_export_version', SV_WOOCOMMERCE_ORDER_EXPORT_VERSION, true);
		}
		
		$this->settings->update_field_settings();
		$this->settings->scan_filter_available();
		$this->settings->get_global_filter();
		$this->export();
		$this->dashboard_widgets();
	}
	public function admin_scripts(){
		wp_enqueue_script('sv_woocommerce_order_export', SV_WOOCOMMERCE_ORDER_EXPORT_PLUGIN_URL.'lib/js/scripts.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'), SV_WOOCOMMERCE_ORDER_EXPORT_VERSION);
		wp_enqueue_style('sv_woocommerce_order_export', SV_WOOCOMMERCE_ORDER_EXPORT_PLUGIN_URL.'lib/css/style.css', false, SV_WOOCOMMERCE_ORDER_EXPORT_VERSION);
	}
	// install/uninstall
	public function install(){
		$this->add_capabilities();
	}
	public function uninstall(){
		$this->remove_capabilities();
	}
	private function add_capabilities(){
		$roles = array(
			'administrator'
		);

		$roles = apply_filters('sv_woocommerce_order_export_roles', $roles);

		foreach($roles as $role){
			$r = get_role($role);
			if($r){
				$r->add_cap('sv_woocommerce_order_export',true);
				$r->add_cap('sv_woocommerce_order_export_subscriptions',true);
			}
		}
	}
	private function remove_capabilities(){
		$roles = array(
			'administrator',
			'editor',
			'author',
			'contributor',
		);

		$roles = apply_filters('sv_woocommerce_order_export_roles', $roles);

		foreach($roles as $role){
			$r = get_role($role);
			if ($r){
				$r->remove_cap('sv_woocommerce_order_export');
			}
		}
	}
	// setter/getter
	public function set_order($id,$full=false){
		$this->orders[$id]['order']			= new WC_Order($id);
		if($full){
			$this->orders[$id]['items']		= $this->orders[$id]['order']->get_items();
		}else{
			$this->orders[$id]['items']		= false;
		}
	}
	public function get_order($id,$full=false){
		if(isset($this->orders[$id]['order']) && is_object($this->orders[$id]['order'])){
			return $this->orders[$id]['order'];
		}else{
			$this->set_order($id,$full);
			return $this->orders[$id]['order'];
		}
	}
	public function get_orders(){
		return $this->orders;
	}
	public function get_subscriptions(){
		return $this->get_all_subscriptions();
	}
	public function get_order_items($id){
		if(is_array($this->orders[$id]['order']->get_items())){
			$items							= $this->orders[$id]['order']->get_items();
			return $items;
		}else{
			return false;
		}
	}
	public function get_item_variation_id_by_item_id($item){
		return (intval($item['variation_id'] > 0) ? $item['variation_id'] : $item['product_id']);
	}
	public function set_product($id){
		$this->products[$id]				= new WC_Product($id);
	}
	public function get_product($id){
		if(isset($this->products[$id]) && is_object($this->products[$id])){
			return $this->products[$id];
		}else{
			$this->set_product($id);
			return $this->products[$id];
		}
	}
	public function export_field_visible($field_id, $field_settings){
		// forced visible?
		if(isset($_POST['subscriptions'])){ // subscriptions
			if($this->settings->is_subscriptions_userfield_forced_active($field_id)){
				return true;
			}elseif($this->settings->is_subscriptions_userfield_forced_hidden($field_id)){
				return false;
			}else{
				return (bool) $field_settings['active'];
			}
		}else{ // orders
			if($this->settings->is_userfield_forced_active($field_id)){
				return true;
			}elseif($this->settings->is_userfield_forced_hidden($field_id)){
				return false;
			}else{
				return (bool) $field_settings['active'];
			}
		}
	}
	// widgets
	public function dashboard_widgets(){
		if(current_user_can('sv_woocommerce_order_export')){
			add_meta_box('sv_woocommerce_order_export_get_stats_current_month', __('Orders Current Month', 'sv_woocommerce_order_export'), array($this,'widget_current_month'), 'dashboard', 'side', 'high');
			add_meta_box('sv_woocommerce_order_export_get_stats_last_month', __('Orders Last Month', 'sv_woocommerce_order_export'), array($this,'widget_last_month'), 'dashboard', 'normal', 'high');
			add_meta_box('sv_woocommerce_order_export_get_custom_export', __('Export Orders', 'sv_woocommerce_order_export'), array($this,'widget_get_custom_export'), 'dashboard', 'normal', 'high');
			// subscriptions
			if(function_exists('wcs_get_subscriptions')){
				add_meta_box('sv_woocommerce_order_export_get_subscription_export', __('Export Subscriptions', 'sv_woocommerce_order_export'), array($this,'widget_get_subscription_export'), 'dashboard', 'normal', 'high');
			}
		}else{
			remove_meta_box( 'woocommerce_dashboard_status', 'dashboard', 'normal');//since 3.8
		}
	}
	public function widget_current_month(){
		$this->get_widget_tpl($this->get_all_orders_current_month(apply_filters('sv_woocommerce_order_export_trigger_full',false)),'current_month',date('Ym'));
	}
	public function widget_last_month(){
		$this->get_widget_tpl($this->get_all_orders_last_month(apply_filters('sv_woocommerce_order_export_trigger_full',false)),'last_month',date('Ym', mktime(0, 0, 0, date('m')-1, 1, date('Y'))));
	}
	// templates
	private function get_widget_tpl($data,$date_range,$date=false){
		if(file_exists(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_monthly_export.php')){
			include(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_monthly_export.php');
		}else{
			include(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/get_monthly_export.php');
		}
	}
	public function widget_get_custom_export(){
		if(file_exists(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_custom_export.php')){
			include(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_custom_export.php');
		}else{
			include(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/get_custom_export.php');
		}
	}
	public function widget_get_subscription_export(){
		if(file_exists(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_subscriptions_export.php')){
			include(get_stylesheet_directory().'/sv_woocommerce_order_export/tpl/get_subscriptions_export.php');
		}else{
			include(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/get_subscriptions_export.php');
		}
	}
	// queries
	private function get_all_orders_current_month($full=false){
		global $wpdb;

		$transient						= 'current_month';
		$query							= 'SELECT * FROM '.$wpdb->prefix.'posts WHERE YEAR(post_date) = YEAR(NOW()) AND MONTH(post_date) = MONTH(NOW()) AND post_type="shop_order" ORDER BY ID ASC';

		// get all orders of the current month
		if(!$full){
			$offset						= $this->prepare_stats($transient);
			$results					= $wpdb->get_results($query.' LIMIT '.($offset ? intval($offset) : 0).', '.$this->stepping.'', ARRAY_A);
			
			if($results && count($results) > 0){ // new orders found
				$orders					= $this->get_order_objects($results,$full,$transient);
				$this->process_stats($transient,$offset);
				return $orders;
			}
		}else{
			return $this->get_order_objects($wpdb->get_results($query, ARRAY_A),$full,$transient);
		}
	}
	private function get_all_orders_last_month($full=false){
		global $wpdb;

		$transient						= 'last_month';
		$query							= 'SELECT * FROM '.$wpdb->prefix.'posts WHERE YEAR(post_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(post_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) AND post_type="shop_order" ORDER BY ID ASC';

		// get all orders of the last month
		if(!$full){
			$offset						= $this->prepare_stats($transient);
			$results					= $wpdb->get_results($query.' LIMIT '.($offset ? intval($offset) : 0).', '.$this->stepping.'', ARRAY_A);

			if($results && count($results) > 0){ // new orders found
				$orders					= $this->get_order_objects($results,$full,$transient);
				$this->process_stats($transient,$offset);
				return $orders;
			}
		}else{
			return $this->get_order_objects($wpdb->get_results($query, ARRAY_A),$full,$transient);
		}
	}
	private function prepare_stats($transient){
		$offset = get_transient('sv_woocommerce_order_export_query_'.$transient.'_offset');

		if(!$offset || $offset == ''){ // once transient is invalidated, empty stats cache
			$this->stats->reset_transient($transient);
		}
		
		return $offset;
	}
	private function process_stats($transient,$offset){
		echo '<p>'.__('calculating stats...', 'sv_woocommerce_order_export').'</p>';
		update_option('sv_woocommerce_order_export_stats',$this->stats->get_stats());
		set_transient('sv_woocommerce_order_export_query_'.$transient.'_offset', $offset+$this->stepping, HOUR_IN_SECONDS*24);
	}
	private function get_all_orders_custom_range($from,$to){
		global $wpdb;

		// security check
		$check_from = date_parse_from_format("Y-n-j",$from);
		$check_to = date_parse_from_format("Y-n-j",$to);
		if($check_from['year'] > 0 && $check_from['month'] > 0 && $check_from['day'] > 0 && $check_to['year'] > 0 && $check_to['month'] > 0 && $check_to['day'] > 0){
			// get all orders of given date range
			$query = 'SELECT * FROM '.$wpdb->prefix.'posts WHERE (post_date BETWEEN "'.intval($check_from['year']).'-'.intval($check_from['month']).'-'.intval($check_from['day']).'" AND "'.intval($check_to['year']).'-'.intval($check_to['month']).'-'.intval($check_to['day']).'") AND post_type="shop_order" ORDER BY ID ASC;';
			return $this->get_order_objects($wpdb->get_results($query, ARRAY_A),true);
		}
	}
	/**
	 * A general purpose function for grabbing an array of subscriptions in form of post_id => WC_Subscription
	 *
	 * The $args parameter is based on the parameter of the same name used by the core WordPress @see get_posts() function.
	 * It can be used to choose which subscriptions should be returned by the function, how many subscriptions should be returned
	 * and in what order those subscriptions should be returned.
	 *
	 * @param array $args A set of name value pairs to determine the return value.
	 *		'subscriptions_per_page' The number of subscriptions to return. Set to -1 for unlimited. Default 10.
	 *		'offset' An optional number of subscription to displace or pass over. Default 0.
	 *		'orderby' The field which the subscriptions should be ordered by. Can be 'start_date', 'trial_end_date', 'end_date', 'status' or 'order_id'. Defaults to 'start_date'.
	 *		'order' The order of the values returned. Can be 'ASC' or 'DESC'. Defaults to 'DESC'
	 *		'customer_id' The user ID of a customer on the site.
	 *		'product_id' The post ID of a WC_Product_Subscription, WC_Product_Variable_Subscription or WC_Product_Subscription_Variation object
	 *		'order_id' The post ID of a shop_order post/WC_Order object which was used to create the subscription
	 *		'subscription_status' Any valid subscription status. Can be 'any', 'active', 'cancelled', 'suspended', 'expired', 'pending' or 'trash'. Defaults to 'any'.
	 * @return array Subscription details in post_id => WC_Subscription form.
	 */
	private function get_all_subscriptions(){
		$args																				= array(
			'subscriptions_per_page'														=> -1,
			'paged'																			=> 0,
			'offset'																		=> 0,
			'orderby'																		=> 'start_date',
			'order'																			=> 'DESC',/*
			'customer_id'																	=> 0,
			'product_id'																	=> 0,
			'variation_id'																	=> 0,
			'order_id'																		=> 0,
			'meta_query_relation'															=> 'AND',*/
			'subscription_status'															=> $_POST['status'],
		);
		foreach(wcs_get_subscriptions($args) as $subscription){
			$items																						= reset($subscription->get_items());

			$subscriptions[$subscription->order->id]['subscription_id']									= $subscription->id;
			$subscriptions[$subscription->order->id]['subscription_parent_order']						= $subscription->order->id;
			$subscriptions[$subscription->order->id]['subscription_user_email']							= $subscription->order->billing_email;
			$subscriptions[$subscription->order->id]['subscription_name']								= $items['name'];
			$subscriptions[$subscription->order->id]['subscription_product_id']							= $items['product_id'];
			$subscriptions[$subscription->order->id]['subscription_variation_id']						= $items['variation_id'];
			$subscriptions[$subscription->order->id]['subscription_total']								= $items['line_total'];
			$subscriptions[$subscription->order->id]['subscription_tax']								= $items['line_tax'];
			$subscriptions[$subscription->order->id]['subscription_has_trial']							= $items['has_trial'];
			$subscriptions[$subscription->order->id]['subscription_status']								= $subscription->get_status();
			$subscriptions[$subscription->order->id]['subscription_failed_payment_count']				= $subscription->get_failed_payment_count();
			$subscriptions[$subscription->order->id]['subscription_completed_payment_count']			= $subscription->get_completed_payment_count();
			$subscriptions[$subscription->order->id]['subscription_needs_payment']						= $subscription->needs_payment();
			$subscriptions[$subscription->order->id]['subscription_start_date']							= $subscription->__get('start_date');
			$subscriptions[$subscription->order->id]['subscription_end_date']							= $subscription->__get('end_date');
			$subscriptions[$subscription->order->id]['subscription_trial_end_date']						= $subscription->__get('trial_end_date');
			$subscriptions[$subscription->order->id]['subscription_next_payment_date']					= $subscription->calculate_date('next_payment');
			$lastPaymentDate																			= $subscription->get_last_order('all');
			$subscriptions[$subscription->order->id]['subscription_last_payment_date']					= $lastPaymentDate->post->post_date_gmt;
			$subscriptions[$subscription->order->id]['subscription_is_download_permitted']				= $subscription->is_download_permitted();
			$subscriptions[$subscription->order->id]['subscription_sign_up_fee']						= $subscription->get_sign_up_fee();
		}
		
		return $subscriptions;
	}
	// gather additional data
	private function get_order_objects($orders,$full=false,$transient=false){
		// get order products
		foreach($orders as $order_data){
			set_time_limit(60);
			$order																						= $this->get_order($order_data['ID'],$full);
			$items																						= apply_filters('sv_woocommerce_order_export_get_order_objects_stats_items',$this->get_order_items($order_data['ID']),$order_data['ID']);
			
			if($items !== false){
				$total																					= apply_filters('sv_woocommerce_order_export_get_order_objects_stats_total',$order->get_total(),$order,$items,$this);
				$this->stats->set_stats($transient,'common',false,'total',$total,'add');
				$this->stats->set_stats($transient,'post_status',$order->get_status(),'total',$total,'add');
				$this->stats->set_stats($transient,'post_status',$order->get_status(),'orders',1,'add');
			}
		}
		$this->stats->set_stats($transient,'common',false,'orders',count($orders));
	}
	// export
	private function export_get_orders(){
		set_time_limit(600);
		ini_set('memory_limit','2048M');
		if(isset($_POST['date_range']) && empty($_POST['subscriptions'])){
			if($_POST['date_range'] == 'current_month'){
				$this->get_all_orders_current_month(true);
			}elseif($_POST['date_range'] == 'last_month'){
				$this->get_all_orders_last_month(true);
			}elseif($_POST['date_range'] == 'custom_export'){
				$this->get_all_orders_custom_range($_POST['datepicker_from'],$_POST['datepicker_to']);
			}else{
				die('unknown date range given');
			}
		}elseif(isset($_POST['subscriptions'])){
			if($_POST['subscriptions'] == 'all'){
				$this->get_subscriptions();
			}
		}else{
			die('no date range given');
		}
	}
	public function export(){
		if(isset($_POST['sv_woocommerce_order_export']) && wp_verify_nonce($_POST['sv_woocommerce_order_export'],'sv_woocommerce_order_export')){
			global $wpdb;
			
			$this->export_get_orders();

			// @todo: support different export modules
			if($_POST['type'] == 'excel'){
				require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/modules/excel.inc.php');
			}elseif($_POST['type'] == 'csv'){
				require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/modules/csv.inc.php');
			}elseif($_POST['type'] == 'xml'){
				require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/modules/xml.inc.php');
			}elseif($_POST['type'] == 'json'){
				require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/modules/json.inc.php');
			}
			$this->module		= $module;
			
			$this->module->build($this);
		}
	}
}

$GLOBALS['sv_woocommerce_order_export']	= new sv_woocommerce_order_export();
?>
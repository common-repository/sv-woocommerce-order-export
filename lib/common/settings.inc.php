<?php
	class sv_woocommerce_order_export_settings{
		private $global_settings								= false;
		private $global_filter									= false;
		private $user_settings									= false;
		private $global_filter_available						= array();
		
		private $default_export_fields							= array('fields' => array(
																	'order_id'							=> array('name' => 'order_id', 'active' => 1),
																	'invoice_id'						=> array('name' => 'invoice_id', 'active' => 0),
																	'order_date'						=> array('name' => 'order_date', 'active' => 1),
																	'customer_id'						=> array('name' => 'customer_id', 'active' => 0),
																	'billing_first_name'				=> array('name' => 'billing_first_name', 'active' => 1),
																	'billing_last_name'					=> array('name' => 'billing_last_name', 'active' => 1),
																	'billing_full_name'					=> array('name' => 'billing_full_name', 'active' => 0),
																	'billing_company'					=> array('name' => 'billing_company', 'active' => 1),
																	'billing_email'						=> array('name' => 'billing_email', 'active' => 1),
																	'billing_phone'						=> array('name' => 'billing_phone', 'active' => 1),
																	'billing_address_1'					=> array('name' => 'billing_address_1', 'active' => 1),
																	'billing_address_2'					=> array('name' => 'billing_address_2', 'active' => 1),
																	'billing_full_address'				=> array('name' => 'billing_full_address', 'active' => 0),
																	'billing_postcode'					=> array('name' => 'billing_postcode', 'active' => 1),
																	'billing_city'						=> array('name' => 'billing_city', 'active' => 1),
																	'billing_country'					=> array('name' => 'billing_country', 'active' => 1),
																	'shipping_first_name'				=> array('name' => 'shipping_first_name', 'active' => 1),
																	'shipping_last_name'				=> array('name' => 'shipping_last_name', 'active' => 1),
																	'shipping_full_name'				=> array('name' => 'shipping_full_name', 'active' => 0),
																	'shipping_company'					=> array('name' => 'shipping_company', 'active' => 1),
																	'shipping_email'					=> array('name' => 'shipping_email', 'active' => 1),
																	'shipping_address_1'				=> array('name' => 'shipping_address_1', 'active' => 1),
																	'shipping_address_2'				=> array('name' => 'shipping_address_2', 'active' => 1),
																	'shipping_full_address'				=> array('name' => 'shipping_full_address', 'active' => 0),
																	'shipping_postcode'					=> array('name' => 'shipping_postcode', 'active' => 1),
																	'shipping_city'						=> array('name' => 'shipping_city', 'active' => 1),
																	'shipping_country'					=> array('name' => 'shipping_country', 'active' => 1),
																	'shipping_method'					=> array('name' => 'shipping_method', 'active' => 1),
																	'shipping_costs'					=> array('name' => 'shipping_costs', 'active' => 1),
																	'order_comments'					=> array('name' => 'order_comments', 'active' => 1),
																	'total'								=> array('name' => 'total', 'active' => 1),
																	'total_tax'							=> array('name' => 'total_tax', 'active' => 1),
																	'items_ids'							=> array('name' => 'items_ids', 'active' => 1),
																	'items_name'						=> array('name' => 'items_name', 'active' => 1),
																	'items_author'						=> array('name' => 'items_author', 'active' => 1),
																	'items_author_name'					=> array('name' => 'items_author_name', 'active' => 1),
																	'items_meta'						=> array('name' => 'items_meta', 'active' => 1),
																	'items_quantity'					=> array('name' => 'items_quantity', 'active' => 1),
																	'items_totals'						=> array('name' => 'items_totals', 'active' => 1),
																	'items_totals_tax'					=> array('name' => 'items_totals_tax', 'active' => 1),
																	'items_totals_tax_percent'			=> array('name' => 'items_totals_tax_percent', 'active' => 1),
																	'items_sku'							=> array('name' => 'items_sku', 'active' => 1),
																	'items_link'						=> array('name' => 'items_link', 'active' => 1),
																	'items_total_sales'					=> array('name' => 'items_total_sales', 'active' => 0),
																	'order_status'						=> array('name' => 'order_status', 'active' => 1),
																	'payment_method'					=> array('name' => 'payment_method', 'active' => 1),
																	'download_permissions_granted'		=> array('name' => 'download_permissions_granted', 'active' => 0),
																));
		private $default_subscriptions_fields					= array('fields' => array(
																	'subscription_id'								=> array('name' => 'subscription_id', 'active' => 1),
																	'subscription_parent_order'						=> array('name' => 'subscription_parent_order', 'active' => 1),
																	'subscription_user_email'						=> array('name' => 'subscription_user_email', 'active' => 1),
																	'subscription_name'								=> array('name' => 'name', 'active' => 1),
																	'subscription_product_id'						=> array('name' => 'product_id', 'active' => 1),
																	'subscription_variation_id'						=> array('name' => 'variation_id', 'active' => 1),
																	'subscription_total'							=> array('name' => 'total', 'active' => 1),
																	'subscription_tax'								=> array('name' => 'tax', 'active' => 1),
																	'subscription_has_trial'						=> array('name' => 'has_trial', 'active' => 1),
																	'subscription_status'							=> array('name' => 'status', 'active' => 1),
																	'subscription_failed_payment_count'				=> array('name' => 'failed_payment_count', 'active' => 1),
																	'subscription_completed_payment_count'			=> array('name' => 'completed_payment_count', 'active' => 1),
																	'subscription_needs_payment'					=> array('name' => 'needs_payment', 'active' => 1),
																	'subscription_start_date'						=> array('name' => 'start_date', 'active' => 1),
																	'subscription_end_date'							=> array('name' => 'end_date', 'active' => 1),
																	'subscription_trial_end_date'					=> array('name' => 'trial_end_date', 'active' => 1),
																	'subscription_next_payment_date'				=> array('name' => 'next_payment_date', 'active' => 1),
																	'subscription_last_payment_date'				=> array('name' => 'last_payment_date', 'active' => 1),
																	'subscription_is_download_permitted'			=> array('name' => 'is_download_permitted', 'active' => 1),
																	'subscription_sign_up_fee'						=> array('name' => 'sign_up_fee', 'active' => 1),
																	'subscription_payment_method'					=> array('name' => 'subscription_payment_method', 'active' => 1),
																));
		
		public function get_default_export_fields(){
			return apply_filters('sv_woocommerce_order_export_get_default_export_fields',$this->default_export_fields);
		}
		public function get_default_subscriptions_export_fields(){
			return apply_filters('sv_woocommerce_order_export_get_default_subscriptions_export_fields',$this->default_subscriptions_fields);
		}
		public function get_settings_menu(){
			$domain = 'sv_woocommerce_order_export';
			$locale = apply_filters('plugin_locale', get_locale(), $domain);
			$custom_lang_dir = WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo';
			load_textdomain($domain, $custom_lang_dir);
			load_plugin_textdomain($domain, FALSE, dirname(plugin_basename(__FILE__)). '/lib/translate/');
			
			add_menu_page(
				__('User Settings', 'sv_woocommerce_order_export'),							// page title
				__('Order Export', 'sv_woocommerce_order_export'),							// menu title
				'sv_woocommerce_order_export',												// capability
				'sv_woocommerce_order_export_user_settings',								// menu slug
				array($this,'get_user_settings_tpl'),										// callable function
				SV_WOOCOMMERCE_ORDER_EXPORT_PLUGIN_URL.'/lib/img/logo_icon.png'				// icon url
			);
			add_submenu_page(
				'sv_woocommerce_order_export_user_settings',								// parent slug
				__('Global Settings', 'sv_woocommerce_order_export'),						// page title
				__('Global Settings', 'sv_woocommerce_order_export'),						// menu title
				'activate_plugins',															// capability
				'sv_woocommerce_order_export_global_settings',								// menu slug
				array($this,'get_global_settings_tpl')										// callable function
			);
			add_submenu_page(
				'sv_woocommerce_order_export_user_settings',								// parent slug
				__('Global Filters', 'sv_woocommerce_order_export'),						// page title
				__('Global Filters', 'sv_woocommerce_order_export'),						// menu title
				'activate_plugins',															// capability
				'sv_woocommerce_order_export_global_filters',								// menu slug
				array($this,'get_global_filters_tpl')										// callable function
			);
			if(class_exists('WC_Subscriptions')){
				add_submenu_page(
					'sv_woocommerce_order_export_user_settings',							// parent slug
					__('Subscriptions: Global Settings', 'sv_woocommerce_order_export'),	// page title
					__('Subscriptions: Global Settings', 'sv_woocommerce_order_export'),	// menu title
					'activate_plugins',														// capability
					'sv_woocommerce_order_export_subscriptions_global_settings',			// menu slug
					array($this,'get_subscriptions_global_settings_tpl')					// callable function
				);
				add_submenu_page(
					'sv_woocommerce_order_export_user_settings',							// parent slug
					__('Subscriptions: User Settings', 'sv_woocommerce_order_export'),		// page title
					__('Subscriptions: User Settings', 'sv_woocommerce_order_export'),		// menu title
					'sv_woocommerce_order_export_subscriptions',							// capability
					'sv_woocommerce_order_export_subscriptions_user_settings',				// menu slug
					array($this,'get_subscriptions_user_settings_tpl')						// callable function
				);
			}
		}
		public function plugin_action_links($actions, $plugin_file){
			static $plugin;
			if(!isset($plugin)){
				$plugin = plugin_basename(__FILE__);
			}
			if($plugin == $plugin_file){
				$settings = array('user_settings' => '<a href="admin.php?page=sv_woocommerce_order_export_user_settings">'.__('User Settings', 'sv_woocommerce_order_export').'</a> | <a href="admin.php?page=sv_woocommerce_order_export_global_settings">'.__('Global Settings', 'sv_woocommerce_order_export').'</a> | <a href="admin.php?page=sv_woocommerce_order_export_global_filters" target="_blank">'.__('Global Filters', 'sv_woocommerce_order_export').'</a>');
				$site_link = array('support' => '<a href="http://codecanyon.net/item/sv-woocommerce-order-export/15402617/support" target="_blank">'.__('Support', 'sv_woocommerce_order_export').'</a>');
				$actions = array_merge_recursive($settings, $actions);
				$actions = array_merge_recursive($site_link, $actions);
			}
			return $actions;
		}
		public function get_user_settings_tpl(){
			require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/user_settings.php');
		}
		public function get_global_settings_tpl(){
			require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/global_settings.php');
		}
		public function get_global_filters_tpl(){
			require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/global_filters.php');
		}
		public function get_subscriptions_global_settings_tpl(){
			require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/subscriptions_global_settings.php');
		}
		public function get_subscriptions_user_settings_tpl(){
			require_once(SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/tpl/subscriptions_user_settings.php');
		}
		public function update_field_settings(){
			if(isset($_POST['sv_woocommerce_order_export_setting_group'])){
				if($_POST['sv_woocommerce_order_export_setting_group'] == 'global_settings'){
					update_option('sv_woocommerce_order_export_settings', $_POST, false);
				}elseif($_POST['sv_woocommerce_order_export_setting_group'] == 'user_settings'){
					update_user_option(get_current_user_id(), 'sv_woocommerce_order_export_settings', $_POST);
				}elseif($_POST['sv_woocommerce_order_export_setting_group'] == 'global_filters'){
					update_option('sv_woocommerce_order_export_filter', $_POST['sv_woocommerce_order_export_filter'], false);
				}elseif($_POST['sv_woocommerce_order_export_setting_group'] == 'subscriptions_global_settings'){
					update_option('sv_woocommerce_order_export_subscriptions_settings', $_POST['sv_woocommerce_order_export_settings'], false);
				}elseif($_POST['sv_woocommerce_order_export_setting_group'] == 'subscriptions_user_settings'){
					update_user_option(get_current_user_id(), 'sv_woocommerce_order_export_subscriptions_settings', $_POST);
				}
			}
		}
		public function get_global_settings(){
			if($this->global_settings){
				return $this->global_settings;
			}elseif($settings = get_option('sv_woocommerce_order_export_settings')){
				if(is_array($settings['fields'])){
					$this->global_settings														= array_merge_recursive($this->get_default_export_fields(),$settings);
					return $this->global_settings;
				}else{
					return $this->get_default_export_fields();
				}
			}else{
				return $this->get_default_export_fields();
			}
		}
		public function get_global_filter(){
			if($this->global_filter){
				return $this->global_filter;
			}elseif($filter = get_option('sv_woocommerce_order_export_filter')){
				$this->global_filter = $filter;
				if($this->global_filter){
					foreach($this->global_filter as $filter_id => $filter_active){
						if(isset($this->global_filter_available[$filter_id]) && file_exists($this->global_filter_available[$filter_id]['path'])){
							require_once($this->global_filter_available[$filter_id]['path']);
							$this->filters_loaded[$filter_id]										= new $this->global_filter_available[$filter_id]['class']($this);
						}
					}
				}
				return $this->global_filter;
			}else{
				return $this->get_default_export_fields();
			}
		}
		public function get_user_settings(){
			if($this->user_settings){
				return $this->user_settings;
			}elseif(is_array(get_user_option('sv_woocommerce_order_export_settings')) && $this->user_settings = array_merge_recursive(get_user_option('sv_woocommerce_order_export_settings'),array_merge_recursive($this->get_default_export_fields(),get_user_option('sv_woocommerce_order_export_settings')))){
				// merge multiple fields to string
				foreach($this->user_settings['fields'] as $field_id => $field){
						$this->user_settings['fields'][$field_id]['name']						= (is_array($field['name']) ? $field['name'][(count($field['name'])-1)] : $field['name']);
						$this->user_settings['fields'][$field_id]['active']						= $field['active'][(count($field['active'])-1)];
				}
				return $this->user_settings;
			}else{
				return $this->get_default_export_fields();
			}
		}
		public function get_subscriptions_global_settings(){
			if($this->global_subscriptions_settings){
				return $this->global_subscriptions_settings;
			}elseif(get_option('sv_woocommerce_order_export_subscriptions_settings') && $this->global_subscriptions_settings = array_merge_recursive($this->get_default_subscriptions_export_fields(),(array)get_option('sv_woocommerce_order_export_subscriptions_settings'))){
				return $this->global_subscriptions_settings;
			}else{
				return $this->get_default_subscriptions_export_fields();
			}
		}
		public function get_subscriptions_user_settings(){
			if($this->user_subscriptions_settings){
				return $this->user_subscriptions_settings;
			}elseif(get_user_option('sv_woocommerce_order_export_subscriptions_settings') && $this->user_subscriptions_settings = array_merge_recursive(get_user_option('sv_woocommerce_order_export_subscriptions_settings'),array_merge_recursive($this->get_default_subscriptions_export_fields(),get_user_option('sv_woocommerce_order_export_subscriptions_settings')))){
				// merge multiple fields to string
				foreach($this->user_subscriptions_settings['fields'] as $field_id => $field){
						$this->user_subscriptions_settings['fields'][$field_id]['name']			= $field['name'][(count($field['name'])-1)];
						$this->user_subscriptions_settings['fields'][$field_id]['active']		= $field['active'][(count($field['active'])-1)];
				}
				return $this->user_subscriptions_settings;
			}else{
				return $this->get_default_subscriptions_export_fields();
			}
		}
		// filter
		public function scan_filter_available(){
			$default_headers = array(
				'name'									=> 'Filter Name',
				'uri'									=> 'Filter URI',
				'desc'									=> 'Description',
				'version'								=> 'Version',
				'author'								=> 'Author',
				'author_uri'							=> 'Author URI',
				'class'									=> 'Class Name'
			);
			
			// original filter
			$dir = SV_WOOCOMMERCE_ORDER_EXPORT_DIR.'lib/filter/';
			if($files = scandir($dir)){
				foreach($files as $file){
					if($file != '.' && $file != '..'){
						$data = get_file_data($dir.$file,$default_headers);
						$this->global_filter_available[$data['class']]			= $data;
						$this->global_filter_available[$data['class']]['path']	= $dir.$file;
					}
				}
			}
			
			// custom filter
			$dir = get_stylesheet_directory().'/sv_woocommerce_order_export/filter/';
			if(is_dir($dir) && $files = scandir($dir)){
				foreach($files as $file){
					if($file != '.' && $file != '..'){
						$data = get_file_data($dir.$file,$default_headers);
						$this->global_filter_available[$data['class']]	= $data;
						$this->global_filter_available[$data['class']]['path']	= $dir.$file;
					}
				}
			}
		}
		public function get_filter_available(){
			return $this->global_filter_available;
		}
		public function is_userfield_forced_hidden($field_id){
			$s = $this->get_global_settings();
			if(isset($s['fields'][$field_id]['status']) && $s['fields'][$field_id]['status'] == 'hide'){
				return true;
			}else{
				return false;
			}
		}
		public function is_userfield_forced_active($field_id){
			$s = $this->get_global_settings();
			if(isset($s['fields'][$field_id]['status']) && $s['fields'][$field_id]['status'] == 'show'){
				return true;
			}else{
				return false;
			}
		}
		public function is_subscriptions_userfield_forced_hidden($field_id){
			$s = $this->get_subscriptions_global_settings();
			if(isset($s['fields'][$field_id]['status']) && $s['fields'][$field_id]['status'] == 'hide'){
				return true;
			}else{
				return false;
			}
		}
		public function is_subscriptions_userfield_forced_active($field_id){
			$s = $this->get_subscriptions_global_settings();
			if(isset($s['fields'][$field_id]['status']) && $s['fields'][$field_id]['status'] == 'show'){
				return true;
			}else{
				return false;
			}
		}
	}
?>
<?php
/*
Filter Name: Variations New Line
Filter URI: http://straightvisions.com
Description: Each Product Variation will be printed in it's own line.
Version: 1.0.0
Author: Matthias Reuter
Author URI: http://straightvisions.com
Class Name: sv_woocommerce_order_export_filter_variation_new_line
*/

class sv_woocommerce_order_export_filter_variation_new_line{
	private $settings										= false;
	private $options										= false;
	
	public function __construct($settings){
		$this->settings										= $settings;
		
		// add new export fields
		add_filter('sv_woocommerce_order_export_get_default_export_fields',array($this,'add_default_export_fields'),1);
		
		// add contents
		add_filter('sv_woocommerce_order_export_filter::items_meta',array($this,'sv_woocommerce_order_export_filter_items_meta'),10,3);
	}
	public function add_default_export_fields($default){
		$this->options										= get_option('sv_woocommerce_order_export_settings');
		$additional_fields									= array();
		$customs											= explode(',',$this->options['settings']['force_additional_fields']);

		if(is_array($customs) && count($customs) > 0 && $customs[0] != ''){
			foreach($customs as $name){
				$additional_fields[$name]['active']			= 1;
				$additional_fields[$name]['name']			= $name;
			}
			return array_merge_recursive($default, array('fields' => $additional_fields));
		}else{
			return $default;
		}
	}
	public function sv_woocommerce_order_export_filter_items_meta($meta,$order,$filter){
		foreach($order['items'] as $item_id => $item){
			$fields											= array();
			if(isset($item['item_meta'])){
						$fields['order_id']					= $order['order']->get_order_number();
						$fields['items_ids']				= $item['product_id'];
				foreach(explode(',',$this->options['settings']['force_additional_fields']) as $name){
					if(isset($item['item_meta'][$name])){
						$fields[$name]						= $item['item_meta'][$name][0];
					}
				}

				$filter->module->childs->update_child($order['order']->get_order_number(),$item_id,$fields,'variations');
			}
		}
		return $meta;
	}
}

?>
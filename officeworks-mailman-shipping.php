<?php
 
/**
 * Plugin Name: WooCommerce Officeworks Mailman Shipping Method
 * Plugin URI: http://www.softwarehtec.com/
 * Description: Mailman is a parcel delivery service with Australia's lowest national flat rate prices, with no hidden fees or surcharges. We deliver from our store to any residential or business address in Australia (excluding PO Boxes, Postal Lockers and remote islands). With free signature on delivery, track and trace, optional parcel protection and the convenience to print your own labels from home or office, with Mailman you can send parcels for less
 * Version: 1.1.8
 * Author: softwarehtec.com
 * Author URI: http://www.softwarehtec.com
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /lang
 * Text Domain: softwarehtec-mailman
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return false;
}

function mailman_shipping_method() {
    if ( ! class_exists( 'Mailman_Shipping_Method' ) ) {
        class Mailman_Shipping_Method extends WC_Shipping_Method {
            var $pickup_postcode;
            public function __construct() {
                $this->id                 = 'mailman'; 
                $this->method_title       = __( 'Officeworks Mailman', 'softwarehtec-mailman' );  


                if(!is_callable('curl_init')){
                    $this->method_description = __( '<span style="color:red">To use Mailman Shipping Method, you have to enabled CURL</span>', 'softwarehtec' ); 
                }else{
                    $this->method_description = __( 'Mailman is a parcel delivery service with Australia\'s lowest national flat rate prices, with no hidden fees or surcharges. We deliver from our store to any residential or business address in Australia (excluding PO Boxes, Postal Lockers and remote islands). With free signature on delivery, track and trace, optional parcel protection and the convenience to print your own labels from home or office, with Mailman you can send parcels for less<br/><strong style="color:red">Currency Of Shipping Price Is In Australian Dollar</strong><br/><strong style="color:black">Support URL: <a href="http://www.softwarehtec.com/contact-us/" target="_blank">http://www.softwarehtec.com/contact-us/</a></strong><br/><strong style="color:black">Plugin URL: <a href="http://www.softwarehtec.com/project/woocommerce-officeworks-mailman-shipping-method/" target="_blank">http://www.softwarehtec.com/project/woocommerce-officeworks-mailman-shipping-method/</a></strong>', 'softwarehtec-mailman' ); 
                }

                $this->availability = 'including';
                $this->countries = array(
                'AU'
                );
                $this->init();
                $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Officeworks Mailman Shipping', 'softwarehtec-mailman' );
                $this->pickup_postcode = $this->settings['pickup_postcode'];
                $this->protection_1 = $this->settings['protection_1'];
                $this->protection_2 = $this->settings['protection_2'];
                $this->protection_3 = $this->settings['protection_3'];
                $this->protection_4 = $this->settings['protection_4'];
                $this->protection_5 = $this->settings['protection_5'];

            }

            function init() {
                $this->init_form_fields(); 
                $this->init_settings(); 
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }
            function init_form_fields() { 
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( 'Enable this shipping.', 'softwarehtec-mailman' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'softwarehtec-mailman' ),
                        'type' => 'text',
                        'description' => __( 'Title to be display on site', 'softwarehtec-mailman' ),
                        'default' => __( 'Officeworks Mailman', 'softwarehtec-mailman' )
                    ),
                    'pickup_postcode' => array(
                        'title' => __( 'Pickup Postcode', 'softwarehtec-mailman' ),
                        'type' => 'text',
                        'description' => __( 'Four-digit post code for the pickup address.', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    ),
                    'protection_1' => array(
                        'title' => __( 'Enable Mailman Parcel Protection Cover Up To $100', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( '', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    ),
                    'protection_2' => array(
                        'title' => __( 'Mailman Parcel Protection Cover Up To $300', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( '', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    ),
                    'protection_3' => array(
                        'title' => __( 'Mailman Parcel Protection Cover Up To $500', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( '', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    ),
                    'protection_4' => array(
                        'title' => __( 'Mailman Parcel Protection Cover Up To $1000', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( '', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    ),
                    'protection_5' => array(
                        'title' => __( 'Mailman Parcel Protection Cover Up To $1500', 'softwarehtec-mailman' ),
                        'type' => 'checkbox',
                        'description' => __( '', 'softwarehtec-mailman' ),
                        'default' => __( '', 'softwarehtec-mailman' )
                    )
                );
 
            }
            public function calculate_shipping( $package = Array() ) {

                $weight = 0;
                $volume = 0;
                $cost = 0;
                $country = $package["destination"]["country"];
                if($country != "AU"){
                    return ;
                }

                foreach ( $package['contents'] as $item_id => $values ) { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                    $tmp_length = wc_get_dimension($_product->get_length(), 'm');
                    $tmp_width = wc_get_dimension($_product->get_width(), 'm');
                    $tmp_height = wc_get_dimension($_product->get_height(), 'm');

                    $volume = $volume + ($tmp_length * $tmp_width * $tmp_height) * $values['quantity'];
                }

                $weight = wc_get_weight( $weight, 'kg' );
                if($weight == 0 || $weight  > 10){
                    return ;
                }

                if($volume  == 0){
                    return ;
                }

                if(!is_callable('curl_init')){
                    return ;
                }


                $d_postcode = urlencode($package["destination"]["postcode"]);

                if(empty($this->pickup_postcode)){
                    return ;
                }
                if(empty($d_postcode)){
                    return ;
                }





                $volume_weight = $volume * 250 ;

                if($volume_weight > $weight){
                    $weight = $volume_weight;
                }


                $amount = 0;

                if($weight > 0 && $weight <= 0.5){
                    $amount = 7.50;
                }else
                if($weight > 0.5 && $weight <= 1){
                    $amount = 11.00;
                }else
                if($weight > 1 && $weight <= 2){
                    $amount = 12.00;
                }else
                if($weight > 2 && $weight <= 3){
                    $amount = 13.00;
                }else
                if($weight > 3 && $weight <= 5){
                    $amount = 16.00;
                }else
                if($weight > 5 && $weight <= 10){
                    $amount = 22.00;
                }

                if($amount == 0){
                    return ;
                }


                $url = "https://api.officeworks.com.au/v1/mailman/postcode?searchTerm=".urlencode($d_postcode);
 
                $handle=curl_init($url);

                if(!empty($this->api_key ) && !empty($this->id)){
                    curl_setopt($handle, CURLOPT_USERPWD, $this->id.":".$this->api_key);
                    curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                }

                curl_setopt($handle, CURLOPT_VERBOSE, true);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));

                $content = curl_exec($handle);
                $result = json_decode( $content); // show target page

                $tmp_d_area = "";
                $d_area = "";

                if(count($result) > 0){
                    if($this->pickup_postcode == $d_postcode){
                        if(count($result) > 1){
                            $tmp_d_area = $result[1];
                        }
                    }else{
                        $tmp_d_area = $result[0];
                    }
                    $d_area = $tmp_d_area->zone;
                }



                if(empty($d_area)){
                    return ;
                }


                $url = "https://api.officeworks.com.au/v1/mailman/postcode?searchTerm=".urlencode($this->pickup_postcode);
 
                $handle=curl_init($url);


                curl_setopt($handle, CURLOPT_VERBOSE, true);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));

                $content = curl_exec($handle);
                $result = json_decode( $content); // show target page


                $o_area = "";

                if(count($result) > 0){
                    $o_area = $result[0]->zone;
                }



                if(empty($o_area)){
                    return ;
                }
 

                $url = "https://api.officeworks.com.au/v1/mailman/deliveryTime?fromZone=".urlencode($o_area)."&toZone=".urlencode($d_area);
                $handle=curl_init($url);
 

                curl_setopt($handle, CURLOPT_VERBOSE, true);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));

                $content = curl_exec($handle);
                $result = json_decode( $content); // show target page


                if(empty($result->minimumDays) || empty($result->maximumDays) ){
                    $delivery_time = "";
                }else{
                    $delivery_time = "(". $result->minimumDays."-".$result->maximumDays." BUSINESS DAYS".")";
                }


                $rate = array(
                'id' => $this->id,
                'label' => $this->title." [We do not deliver to PO Boxes, Postal Lockers and remote islands] ".$delivery_time,
                'cost' => $amount,
                'taxes' => false
                );
                $this->add_rate( $rate );
                if($this->protection_1 == "yes"){
                    $rate = array(
                    'id' => $this->id."_1",
                    'label' => $this->title." + Mailman Parcel Protection Cover Up To $100 [We do not deliver to PO Boxes, Postal Lockers and remote islands] (".$delivery_time.")",
                    'cost' => $amount+1.5,
                    'taxes' => false
                    );
                    $this->add_rate( $rate );

                }

                if($this->protection_2 == "yes"){
                    $rate = array(
                    'id' => $this->id."_2",
                    'label' => $this->title." + Mailman Parcel Protection Cover Up To $300 [We do not deliver to PO Boxes, Postal Lockers and remote islands] (".$delivery_time.")",
                    'cost' => $amount+3,
                    'taxes' => false
                    );
                    $this->add_rate( $rate );

                }


                if($this->protection_3 == "yes"){
                    $rate = array(
                    'id' => $this->id."_3",
                    'label' => $this->title." + Mailman Parcel Protection Cover Up To $500 [We do not deliver to PO Boxes, Postal Lockers and remote islands] (".$delivery_time.")",
                    'cost' => $amount+5,
                    'taxes' => false
                    );
                    $this->add_rate( $rate );

                }



                if($this->protection_4 == "yes"){
                    $rate = array(
                    'id' => $this->id."_4",
                    'label' => $this->title." + Mailman Parcel Protection Cover Up To $1000 [We do not deliver to PO Boxes, Postal Lockers and remote islands] (".$delivery_time.")",
                    'cost' => $amount+10,
                    'taxes' => false
                    );
                    $this->add_rate( $rate );

                }



                if($this->protection_5 == "yes"){
                    $rate = array(
                    'id' => $this->id."_5",
                    'label' => $this->title." + Mailman Parcel Protection Cover Up To $1500 [We do not deliver to PO Boxes, Postal Lockers and remote islands] (".$delivery_time.")",
                    'cost' => $amount+15,
                    'taxes' => false
                    );
                    $this->add_rate( $rate );

                }

            }
        }
    }
}

add_action( 'woocommerce_shipping_init', 'mailman_shipping_method' );
 
function add_mailman_shipping_method( $methods ) {
    $methods[] = 'Mailman_Shipping_Method';
    return $methods;
}
 
add_filter( 'woocommerce_shipping_methods', 'add_mailman_shipping_method' );
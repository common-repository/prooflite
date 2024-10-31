<?php if (! defined('ABSPATH')) { exit;}
/**
 * Copyright (c) 2018.
 * Plugin Name: Prooflite
 * Plugin URI:  https://wordpress.org/plugins/prooflite/
 * Description: Prooflite is a social proof tool which helps to increase website conversion by showing live visitors activity.
 * Version: 1.0
 * Author: Prooflite Team
 * Author URI: https://prooflite.com
 * Text Domain: Prooflite
 * Domain Path: /languages
 */


// Plugin Folder Path
if (!defined('WPPL_PATH')) {
    define('WPPL_PATH', plugin_dir_path(__FILE__));
}

// Plugin Folder URL
if (!defined('WPPL_URL')) {
    define('WPPL_URL', plugin_dir_url(__FILE__));
}

if (!class_exists('Prooflite')) {
    /**
     * The Main Class
     */
    class Prooflite
    {
        /**
         * Plugin Version
         *
         * @since 1.0.0
         * @var string
         */
        const VERSION = '1.0.0';


        protected $db;

        /**
         * Instance of class
         *
         * @access protected
         * @since 1.0.0
         *
         */
        protected static $instance;

        /**
         * @access private
         * @since 1.0.0
         */
        private $webhookUrl;

        /**
         * Constructor
         * @access private
         * @since 1.0.0
         */
        public function __construct()
        {
            $this->init();
        }

        /**
         * Initialize actions
         * @access public
         * @return null
         */
        public function init()
        {
            add_action('admin_menu', array($this, 'adminMenu'));
            add_action('wp_head', array($this, 'wpHead'), 10, 3);
            add_action('woocommerce_order_status_processing', array($this, 'orderPlacedHook'), 10, 3);
            $this->webhookUrl = get_option('pl_webhook_url');
        }

        public function wpHead()
        {
            echo get_option('pl-pixel-code');
        }


        /**
         * @access public
         * @param $orderId
         * @return null
         */
        public function orderPlacedHook ($orderId) {
            $order = wc_get_order($orderId);

            if ($this->webhookUrl && get_option('pl_woocommerce_status')) {

                $user = $this->getProduct($order, $this->getCustomer($order));

                wp_remote_post(
                    $this->webhookUrl, [
                        'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
                        'blocking'  => true,
                        'body'      => json_encode($user)
                    ]
                );
            }
        }

        /**
         * Return the instance of class
         * @access public
         * @return Object A Single Instance of the class
         */
        public static function getInstance()
        {
            if (null === self::$instance) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        public function addPixelCode() {
            include_once wp_normalize_path(WPPL_PATH . '/templates/pl_pixel_code.php');
        }

        public function woocommerce()
        {
            if (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                include_once wp_normalize_path(WPPL_PATH . '/templates/pl_woocommerce.php');
            } else {
                include_once wp_normalize_path(WPPL_PATH . '/templates/pl_warning.php');
            }
        }

        /**
         * GM Admin Menu
         * @access public
         */
        public function adminMenu()
        {
            add_menu_page(__('Prooflite'), 'Prooflite', 'manage_options', 'pl_pixel_code', array($this, 'addPixelCode'), 'dashicons-chart-pie', 72);
            add_submenu_page('pl_pixel_code', 'Install Pixel Code', 'Add Pixel Code', 'manage_options', 'pl_pixel_code', array($this, 'addPixelCode'));
            add_submenu_page('pl_pixel_code', 'Prooflite - WooCommerce Integration', 'WooCommerce', 'manage_options', 'pl_woocommerce', array($this, 'woocommerce'));

        }

        /**
         * @param $order
         * @return array
         */
        public function getCustomer($order)
        {
            if ($user_id = $order->get_user_id()) {
                $user = get_user_by('id', $user_id);    
                $data = [
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->user_email,
                ];
            } else {
                $data = [
                    'name' => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
                    'email' => $order->billing_email,
                ];
            }

            $location        = $this->getLocation($order);
            $data['format']  =  get_option('pl_message_format');
            $data['type']    =  'woocommerce';          

            return array_merge($data,$location);
        }
      
       private function getLocation($order) 
       {
           $location = [];
           if($order->get_shipping_city() && $order->get_shipping_state()) {
             $location['city']  = $order->get_shipping_city();
             $location['state'] = $order->get_shipping_state();
             $location['country'] = $order->get_shipping_country();
           }
         
         	 if($order->get_billing_city() && $order->get_billing_state() && empty($location)) {
             $location['city']  = $order->get_billing_city();
             $location['state'] = $order->get_billing_state();
             $location['country'] = $order->get_billing_country();
           }	
         
         	return $location;
         
         		
       }

        /**
         * @param $order
         * @param $user
         * @return mixed
         */
        public function getProduct($order, $user)
        {
            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                $product_id = $product->get_id();
                $user['title'] = $product->get_title();
                $user['image'] = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'full')[0];
                $user['link'] = get_permalink($product_id);
                break;
            }
            return $user;
        }

    }
}
add_action('plugins_loaded', array('Prooflite', 'getInstance'));

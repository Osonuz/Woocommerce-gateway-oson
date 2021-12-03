<?php
/**
 * Plugin Name: WooCommerce Oson Gateway
 * Description: Official OSON payment system plug-in for Woocommerce
 * Author: Oson
 * Author URI: https://oson.uz/
 * Version: 1.0.4
 * Text Domain: wc-gateway-oson
 * Domain Path: /i18n/languages/
 *
 * @package   WC-Gateway-Oson
 * @author    Oson
 * @category  Admin
 * @copyright Copyright (c) 2021
 *
 */

defined( 'ABSPATH' ) or exit;

require_once dirname(__FILE__). "/inc/ic_exchanger_class.php";

// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

define ('OSON_TABLE_MANAGER', 'oson_manager');

/**
 * Регистрируем гейт Oson
 * @since 1.0.0
 */
function wc_oson_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Oson_Gateway';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_oson_add_to_gateways' );


/**
 * Adds plugin page links
 *
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_oson_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=oson_gateway' ) . '">'
			. __( 'Configure', 'wc-gateway-oson' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_oson_gateway_plugin_links' );


/**
 * Oson Payment Gateway (расширяем WC_Payment_Gateway)
 *
 * @class 		WC_Oson_Gateway
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 */
add_action( 'plugins_loaded', 'wc_oson_gateway_init', 11 );
add_filter( 'request', array( /*__CLASS__*/'WC_Oson_Gateway' , 'get_request'));

function wc_oson_gateway_init() {

	class WC_Oson_Gateway extends WC_Payment_Gateway {

		public static $order_id;

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
			$intro			  = "Gateway Oson Payment";
			$this->id                 = 'oson_gateway';
            // $this->icon               = apply_filters('woocommerce_ic_icon', '');
            $this->icon               = apply_filters( 'woocommerce_gateway_icon', plugin_dir_url(__FILE__).'\oson.png' );
			$this->has_fields         = false;
			$this->method_title       = __( 'Oson', 'wc-gateway-oson' );
			$this->method_description = __( $intro, 'wc-gateway-oson' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			// $this->title        = $this->get_option( 'title' );
			// $this->description  = $this->get_option( 'description' );
			// $this->instructions = $this->get_option( 'instructions', $this->description );

			$this->plugin_name = plugin_basename(__FILE__);
			$this->plugin_url = trailingslashit(plugin_dir_url(__FILE__));


			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
			add_action( 'woocommerce_thankyou', array( $this, 'thankyou_page' ));

			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'wp_enqueue_scripts', array(&$this, 'site_load_styles'));

		}

		public function site_load_styles()
		{
			wp_register_style('wc_gwoson_css', $this->plugin_url . 'css/styles.css' );
			wp_enqueue_style('wc_gwoson_css');
		}

		/**
		 * Начальные настройки гейта
		 */
		public function init_form_fields() {

			$this->form_fields = array(

				'enabled' => array(
					'title'   => __( 'Вкл./Выкл.', 'wc-gateway-oson' ),
					'type'    => 'checkbox',
					'label'   => __( 'Включить Oson Payment', 'wc-gateway-oson' ),
					'default' => 'yes'
				),
				'title' => array(
					'title'       => __( 'Заголовок', 'wc-gateway-oson' ),
					'type'        => 'text',
					'description' => __( 'Наименование шлюза оплаты на странице checkout', 'wc-gateway-oson' ),
					'default'     => __( 'Oson Payment', 'wc-gateway-oson' ),
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => __( 'Описание', 'wc-gateway-oson' ),
					'type'        => 'textarea',
					'description' => __( 'Описание метода оплаты. Можете вписать все, что считаете нужным для описания сервиса оплаты', 'wc-gateway-oson' ),
					'default'     => 'Онлайн платежи по картам Visa, mastercard и др., через платежный сервис',
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Инструкции для клиента', 'wc-gateway-oson' ),
					'type'        => 'textarea',
					'description' => __( 'Инструкции для клиента при создании заказа. Уведомление пристутвует и на странице thankyou_page и в теле письма', 'wc-gateway-oson' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				'serverUrl'	=> array(
					'title'       => __( 'Адрес сервера Oson', 'wc-gateway-oson' ),
					'type'        => 'text',
					'default'     => 'https://api.oson.uz/api/',
				),
				'merchant_id' => array(
					'title'       => 'merchant id',
					'type'        => 'text'
				),
				'token' => array(
					'title'       => 'token',
					'type'        => 'password'
				),


			);
		}

		/**
		 * Функция приема данных (вебхук) с сервера оплаты
		 * @access public static
		 * @param array $query
		 */
		public static function get_request($query) {

			$request = urldecode($_SERVER['REQUEST_URI']);
			if ( stristr($request, '/webhook') ) {

				global $wpdb, $woocommerce;

				$data = file_get_contents ("php://input");
				$data = json_decode ($data, true);

				$params = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s",
					'woocommerce_oson_gateway_settings'));

				if (!$params) {
					error_log('Error configuring Oson options.');
					return $query;
				}

				preg_match_all('`"([^"]*)"`', $params, $params);

				$token 		 = WC_Oson_Gateway::get_next_arrval($params[1], 'token');
				$merchant_id = WC_Oson_Gateway::get_next_arrval($params[1], 'merchant_id');
				$instructions = WC_Oson_Gateway::get_next_arrval($params[1], 'instructions');

				$parameters = "{$data['transaction_id']}:{$data['bill_id']}:{$data['status']}";
				$signature  = hash('sha256', hash('sha256', "{$token}:{$merchant_id}").":{$parameters}");

				if ( $signature === $data['signature']) {

					$order_id = $wpdb->get_var($wpdb->prepare('SELECT order_id FROM '.$wpdb->prefix.OSON_TABLE_MANAGER.
							' WHERE transaction_id=%s', $data['transaction_id']));

					$order = wc_get_order($order_id);

					if ($order AND $data['status'] == 'PAID') {
						$order->payment_complete();
						$order->update_status('completed');
						$order->reduce_order_stock();
						$woocommerce->cart->empty_cart();

						if ( $instructions ) {
							echo wpautop( wptexturize( $instructions ) );
						}

						exit;
					}
				}

				do_action( 'wp_loaded');
				get_header();

				echo '<p>Ошибка! Некорректный запрос!</p>';
				error_log('Ошибка! Некорректная цифровая подпись в ответе сервера или ее отсутствие!');

				get_footer();

				exit();

			}

			return $query;
		}


		/**
		 * Вывод страницы, если заказ успешно завершен
		 * @access public
		 */
		public function thankyou_page() {

			global $woocommerce;
			$order_id = static::$order_id;
			$order = wc_get_order($order_id);
			if ($order) {
				if (!$order->has_status('processing')) {
					$order->update_status('pending');
					exit;
				} else {
					$order->payment_complete();
					$order->reduce_order_stock();

					$woocommerce->cart->empty_cart();

					if ( $this->instructions ) {
						echo wpautop( wptexturize( $this->instructions));
					}
				}

			}

		}

		/**
		 * Направить WC email.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {

			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}


		/**
		 * Вывод поля с информацией о платежке
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $plain_text
		 */
		public function payment_fields() {

			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description ) );
			}

			ob_start();
			?>
				<fieldset id="wc-<?= esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">

					<?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>

					<div class="cards-field-form">
						<svg width="231" height="55" viewBox="0 0 231 55" fill="none" xmlns="http://www.w3.org/2000/svg" style="height: 35px;">
							<g clip-path="url(#clip0)">
								<path d="M91.9826 8.45117C104.048 8.45117 111.82 16.6343 111.82 27.5905C111.82 38.5352 104.048 46.7184 91.9826 46.7184C79.9286 46.7184 72.1562 38.5352 72.1562 27.5905C72.1562 16.6343 79.9286 8.45117 91.9826 8.45117ZM91.9826 38.0485C97.7328 38.0485 101.946 33.9061 101.946 27.5905C101.946 21.4221 97.6537 17.2003 91.9826 17.2003C86.481 17.2003 82.109 21.2636 82.109 27.5905C82.109 33.6684 86.3228 38.0485 91.9826 38.0485Z" fill="#1694CA"></path>
								<path d="M116.667 19.5545C116.667 12.424 122.496 8.45117 130.031 8.45117C137.07 8.45117 141.6 10.9639 143.871 13.0691L140.391 19.713C138.199 18.0152 134.404 16.2269 130.517 16.2269C127.682 16.2269 125.976 17.7775 125.976 19.396C125.976 25.4739 145.655 22.1463 145.655 35.0493C145.655 42.259 139.825 46.7184 130.913 46.7184C124.202 46.7184 117.402 43.0739 115.695 40.482L120.237 34.3928C122.417 36.7469 127.919 38.9427 131.568 38.9427C134.234 38.9427 136.347 37.7995 136.347 35.536C136.347 29.6164 116.667 32.8535 116.667 19.5545Z" fill="#1694CA"></path>
								<path d="M169.445 8.37305C181.499 8.37305 189.272 16.5562 189.272 27.5124C189.272 38.4571 181.499 46.6402 169.445 46.6402C157.38 46.6402 149.607 38.4571 149.607 27.5124C149.607 16.5562 157.38 8.37305 169.445 8.37305ZM169.445 37.9704C175.195 37.9704 179.397 33.828 179.397 27.5124C179.397 21.3438 175.104 17.1221 169.445 17.1221C163.943 17.1221 159.572 21.1855 159.572 27.5124C159.572 33.5903 163.773 37.9704 169.445 37.9704Z" fill="#1694CA"></path>
								<path d="M195.338 10.1081H204.647V13.7638C206.839 11.4097 210.725 9.46289 215.097 9.46289C223.998 9.46289 230.065 13.7639 230.065 25.1953V46.6095H220.756V26.8139C220.756 20.9735 217.763 18.2233 212.588 18.2233C209.425 18.2233 206.839 19.5249 204.647 21.7092V46.6095H195.338V10.1081Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M47.4154 21.7685C44.2296 20.433 40.5693 21.9384 39.2363 25.1187C37.9033 28.3105 39.4058 31.9776 42.5803 33.3132C45.766 34.6488 49.4262 33.1434 50.7593 29.9629C52.0923 26.7712 50.5899 23.1041 47.4154 21.7685Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M30.7765 44.1794C30.2681 42.3685 31.3188 40.501 33.1263 39.9917C34.9225 39.4822 36.7978 40.5349 37.2949 42.3345C37.8032 44.1454 36.7526 46.0243 34.9563 46.5222C33.1488 47.0315 31.2848 45.9791 30.7765 44.1794Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M18.2922 0.628392C15.1064 -0.718563 11.4462 0.786859 10.1132 3.97863C8.78002 7.15896 10.2826 10.8261 13.4684 12.1616C16.6427 13.4973 20.303 12.0033 21.636 8.81155C22.9692 5.63108 21.478 1.96391 18.2922 0.628392Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M35.4517 8.59467C33.7233 7.87028 31.7349 8.68521 31.0119 10.4056C30.289 12.1374 31.1023 14.1294 32.8308 14.8538C34.5593 15.578 36.5475 14.7633 37.2705 13.0429C37.9936 11.311 37.1802 9.31907 35.4517 8.59467Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M17.9193 42.8797C14.7449 41.5441 11.0847 43.0494 9.75164 46.2412C8.4185 49.4217 9.90967 53.0888 13.0954 54.4244C16.2699 55.7599 19.9302 54.2659 21.2632 51.0741C22.5962 47.8823 21.105 44.2266 17.9193 42.8797Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M4.83643 24.407C3.108 23.6826 1.11963 24.4975 0.396737 26.2293C-0.326294 27.961 0.487101 29.9417 2.21553 30.6774C3.94395 31.4018 5.92091 30.5869 6.64394 28.8552C7.37825 27.1234 6.56486 25.1314 4.83643 24.407Z" fill="#1694CA"></path>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M34.7512 22.675C37.4399 29.2397 34.2994 36.7551 27.7357 39.4375C21.1722 42.1313 13.6824 38.9847 10.9936 32.4088C8.30493 25.8329 11.4569 18.3288 18.0091 15.635C24.5727 12.9527 32.0738 16.0991 34.7512 22.675ZM19.2178 18.5891C14.2811 20.6151 11.92 26.263 13.9422 31.209C15.9531 36.1438 21.5902 38.5206 26.5271 36.4948C31.4638 34.4687 33.8362 28.8209 31.814 23.8748C29.7919 18.9288 24.1547 16.5632 19.2178 18.5891Z" fill="#1694CA"></path>
							</g>
							<defs>
								<clipPath id="clip0">
									<rect width="230.185" height="55" fill="#1694CA"></rect>
								</clipPath>
							</defs>
						</svg>
					</div>

				  	<div class="clear"></div>

					<?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>

					<div class="clear"></div>
				</fieldset>

			<?php
			$answer = ob_get_contents();
			ob_end_clean();

			echo $answer;

		}

		/**
		 * Метод поиска в сериализованном массиве
		 * @access public static
		 */
		public static function get_next_arrval($array, $key) {
			$fbreak = 0;
			foreach ($array as $arr) {

				if ($fbreak) break;
				if ($arr == $key) {
					$fbreak ++;
				}
			}

			return $fbreak ? $arr : null;
		 }

		/**
		 * Метод процесса оплаты
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {

			global $wpdb;
			global $woocommerce;

			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

			static::$order_id = $order_id;
			$order = wc_get_order( $order_id );

			$params = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name=%s",
				'woocommerce_oson_gateway_settings'));

			if (!$params) {
				wc_add_notice(  'Error configuring payment parameters.', 'error' );
				return false;
			}

			preg_match_all('`"([^"]*)"`', $params, $params);

			$api = new ICExchanger( array(
				'serverUrl'   => 	$this->get_next_arrval($params[1], 'serverUrl'),
				'merchant_id' =>	$this->get_next_arrval($params[1], 'merchant_id'),
				'token'		  =>	$this->get_next_arrval($params[1], 'token'),
			));

			$response = $api->query("invoice/create", [
					'transaction_id'=> uniqid(),
					'user_account'  => $order->get_billing_email() ? $order->get_billing_email() : $order_id,
					'comment'		=> 'Payment order #'.$order_id,
					'currency'		=> "UZS", //get_woocommerce_currency(),
					'amount'		=> ceil($woocommerce->cart->cart_contents_total + $woocommerce->cart->tax_total + $order->get_total_shipping()),
					'phone'    		=> $order->get_billing_phone(),
					'lang'			=> substr(get_locale(), 0, 2),
					'lifetime'		=> 30,
					'return_url'
						  => $url.'/checkout/order-received/'.$order_id.'/?key='. $order->get_order_key().'&id='.$order_id,
				]
			);

			$message = "Извините. Что-то пошло не так, попробуйте немного позже повторить оплату или свяжитесь с нами";

			if ( isset($response->type) &&  $response->type === 'ERROR' || $api->errno > 0) {
				error_log("Error #" .$api->errno . ' '.json_encode($response));

				throw new Exception("{$message} <br><span style='font-size:10px;'>Connection error #{$api->errno} : {$api->errmsg}</span>");
			} else {

				if ($response->status === 'REGISTRED') {
					$order->update_status('pending');
					$wpdb->query( $wpdb->prepare(
						'INSERT INTO `'.$wpdb->prefix.OSON_TABLE_MANAGER.'` (`order_id`,`bill_id`, `transaction_id`) '.
							'values ('.$order->id.','.$response->bill_id.',\''.$response->transaction_id.'\')') );

					return array(
						'result' 	=> 'success',
						'redirect'  	=> $response->pay_url
					);

				} else {
					throw new Exception("{$message} <br><span style='font-size:10px;'>Error response #{$response->error_code} : {$response->message}</span>");
				}

			}

		}
  	}	//end of class
}


/*
* Класс управления плагином и таблицами
*/
if (!class_exists('OsonManager')) {

	class OsonManager {

		public function __construct ()
		{
			$this->plugin_name = plugin_basename(__FILE__);
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
			register_uninstall_hook( $this->plugin_name, array(&$this, 'uninstall') );

		}

		public function activate()
		{
			global $wpdb;

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);

			if ($link) {
				if ( version_compare(mysqli_get_server_info($link), '4.1.0', '>=') ) {
					if ( ! empty($wpdb->charset) )
						$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
					if ( ! empty($wpdb->collate) )
						$charset_collate .= " COLLATE $wpdb->collate";
				}
				mysqli_close($link);

			} else {
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			}

			$sql_table =
					'CREATE TABLE `'.$wpdb->prefix.OSON_TABLE_MANAGER.'` (
						`id` int(11) NOT NULL AUTO_INCREMENT KEY,
						`order_id` mediumint(9) NOT NULL ,
						`transaction_id` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
						`bill_id`  mediumint(9) NOT NULL ,
						`created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
					)' .$charset_collate.";";

			if ( $wpdb->get_var("show tables like '".$wpdb->prefix.OSON_TABLE_MANAGER."'") != $wpdb->prefix.OSON_TABLE_MANAGER ) {
				dbDelta($sql_table);
			}

		}

		public function deactivate()
		{
			return true;
		}

		/**
		 * Удаление плагина
		 */
		public static function uninstall()
		{
			global $wpdb;
			$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix.OSON_TABLE_MANAGER);
		}

	}
}

global $OsonManager;
$OsonManager = new OsonManager();

<?php
/**
 * Plugin Name: Calculate price by weight/dimension for WooCommerce
 * Description: Woocommerce products with sizes & prices
 * Version: 1.2.3
 * Author: nevma
 * Requires at least: 5.0
 * Author URI: https://nevma.gr
 * Text Domain: wecom-product-dimensions
 * Domain Path: /languages/
 * WC tested up to: 4.1
 */

namespace Wecom_Product_Dimensions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Wecom_Product_Dimensions\Main' ) ) {
	define( 'WCPRD_TEXTDOMAIN', 'wecom-product-dimensions' );
	define( 'WCPRD_PREFIX', 'wcprd' );
	define( 'WCPRD_URL', plugin_dir_url( __FILE__ ) );

	class Main {
		// Instance of this class.
		// protected static $instance = null;

		public function __construct() {
			if ( ! class_exists( 'WooCommerce' ) ) {
				return;
			}

			// Load translation files
			add_action( 'init', array( $this, 'add_translation_files' ) );

			// Admin page
			// add_action('admin_menu', array( $this, 'setup_menu' ));

			// Add settings link to plugins page
			// add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'add_settings_link' ) );

			// Register plugin settings fields
			// register_setting( WCPRD_PREFIX . '_settings', WCPRD_PREFIX . '_email_message', array('sanitize_callback' => array( 'Main', 'sanitize_code' ) ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ), 999999999999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_css' ), 999999999999 );
			add_filter( 'wecom_product_dimension_type', array( $this, 'product_dimension_types' ), 1, 1 );
			add_filter( 'wecom_product_dimension_labels', array( $this, 'product_dimension_labels' ), 1, 1 );
			add_filter( 'wcprd_min_size_filter', array( $this, 'min_size_default_value' ), 1, 1 );
			add_filter( 'wcprd_max_size_filter', array( $this, 'max_size_default_value' ), 1, 1 );
			add_filter( 'wcprd_stepper_filter', array( $this, 'stepper_default_value' ), 1, 1 );

			require_once __DIR__ . '/classes/class-backoffice.php';
			require_once __DIR__ . '/classes/class-frontend.php';
			require_once __DIR__ . '/classes/class-prices.php';
			require_once __DIR__ . '/classes/class-ajax.php';
		}

		public function min_size_default_value( $min_size ) {
			if ( $min_size == 0 ) {
				return 1;
			}
			return $min_size;
		}

		public function max_size_default_value( $max_size ) {
			if ( $max_size == 0 ) {
				return PHP_INT_MAX;
			}
			return $max_size;
		}

		public function stepper_default_value( $stepper_size ) {
			if ( $stepper_size == 0 ) {
				return 1;
			}
			return $stepper_size;
		}

		/**
		 * @param array<string, string> $labels
		 * @return array<string, string>
		 */
		public function product_dimension_labels( $labels ) {
			$labels = array(

				'fixed_length'   => __( 'Fixed Length', 'wecom-product-dimensions' ),
				'min_length'     => __( 'Min Length', 'wecom-product-dimensions' ),
				'max_length'     => __( 'Max Length', 'wecom-product-dimensions' ),

				'fixed_width'    => __( 'Fixed Width', 'wecom-product-dimensions' ),
				'min_width'      => __( 'Min Width', 'wecom-product-dimensions' ),
				'max_width'      => __( 'Max Width', 'wecom-product-dimensions' ),

				'diameter'       => __( 'Diameter', 'wecom-product-dimensions' ),
				'min_diameter'   => __( 'Min Diameter', 'wecom-product-dimensions' ),
				'max_diameter'   => __( 'Max Diameter', 'wecom-product-dimensions' ),

				'min_weight'     => __( 'Min Weight', 'wecom-product-dimensions' ),
				'max_weight'     => __( 'Max Weight', 'wecom-product-dimensions' ),

				'stepper'        => __( 'Stepper', 'wecom-product-dimensions' ),
				'stepper_width'  => __( 'Width Stepper', 'wecom-product-dimensions' ),
				'stepper_length' => __( 'Length Stepper', 'wecom-product-dimensions' ),
				'stepper_weight' => __( 'Weight Stepper', 'wecom-product-dimensions' ),
			);

			return $labels;
		}

		/**
		 * @param array<string, array<string, mixed>> $types
		 * @return array<string, array<string, mixed>>
		 */
		public function product_dimension_types( $types ): array {
			$types = array(
				'select'           => array(
					'title'      => __( 'Select...', 'wecom-product-dimensions' ),
					'dimensions' => array(),
					'headings'   => array(),
				),
				'linear_dimension' => array(
					'title'      => __( 'Per Meter', 'wecom-product-dimensions' ),
					'dimensions' => array(
						'min_length',
						'max_length',
						'stepper_length',
					),
					'headings'   => array(
						'length' => __( 'Length', 'wecom-product-dimensions' ),
					),
				),
				'weight'           => array(
					'title'      => __( 'By Weight', 'wecom-product-dimensions' ),
					'dimensions' => array(
						'min_weight',
						'max_weight',
						'stepper_weight',
					),
					'headings'   => array(
						'weight' => __( 'Weight', 'wecom-product-dimensions' ),
					),
				),
			);

			return $types;
		}

		public function enqueue_css(): void {
			wp_enqueue_style(
				'wecom_dimensions_stylesheet',
				plugin_dir_url( __FILE__ ) . 'assets/css/style.css'
			);
			if ( is_product() ) {
				wp_dequeue_script( 'wecom_ajax_add_to_cart' );
			}
		}

		public static function sanitize_code( string $input ): string {
			$sanitized = wp_kses_post( $input );
			if ( isset( $sanitized ) ) {
				return $sanitized;
			}

			return '';
		}

		public function add_translation_files(): void {
			load_plugin_textdomain( WCPRD_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		public function setup_menu(): void {
			add_management_page(
				__( 'Plugin Settings Title here...', 'wecom-product-dimensions' ),
				__( 'Plugin Settings Title here...', 'wecom-product-dimensions' ),
				'manage_options',
				WCPRD_PREFIX . '_settings_page',
				array( $this, 'admin_panel_page' )
			);
		}

		public function admin_panel_page(): void {
			include_once __DIR__ . '/wecom-product-dimensions.admin.php';
		}

		/**
		 * @param array<string> $links
		 * @return array<string> $links
		 */
		public function add_settings_link( $links ): array {
			$links[] = '<a href="' . admin_url( 'tools.php?page=' . WCPRD_PREFIX . '_settings_page' ) . '">' . __( 'Settings' ) . '</a>';
			return $links;
		}

		// Instance of this class.
		/**
		 * @var Main
		 */
		protected static $instance = null;

		// Return an instance of this class.
		public static function get_instance(): Main {
			// If the single instance hasn't been set, set it now.
			if ( self::$instance == null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}

	add_action( 'plugins_loaded', array( '\Wecom_Product_Dimensions\Main', 'get_instance' ), 0 );
}

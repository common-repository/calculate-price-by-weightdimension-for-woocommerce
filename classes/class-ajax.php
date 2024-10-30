<?php

namespace Wecom_Product_Dimensions;

if ( ! class_exists( 'Ajax' ) ) {
	class Ajax {

		public function __construct() {
			add_action( 'wp_ajax_wecom_dimensions_get_price', array( $this, 'get_price' ) );
			add_action( 'wp_ajax_nopriv_wecom_dimensions_get_price', array( $this, 'get_price' ) );
		}

		public function get_price() {
			check_ajax_referer( 'wecom-dimensions', 'security' );

			$product_id  = filter_input( INPUT_POST, 'prodId' );
			$type        = filter_input( INPUT_POST, 'sizeType' );
			$wecom_sizes = isset( $_POST['wecomSizes'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['wecomSizes'] ) ) : array();
			$sizes       = array_map(
				function ( $size ) {
					return intval( $size );
				},
				$wecom_sizes
			);
			$price       = \Wecom_Product_Dimensions\Prices::get_price( $product_id, $type, $sizes );
			wp_send_json_success( array( 'price' => $price ) );
		}

		// Instance of this class.
		protected static $instance = null;


		// Return an instance of this class.
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}

	Ajax::get_instance();

}

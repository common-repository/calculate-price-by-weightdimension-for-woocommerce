<?php

namespace Wecom_Product_Dimensions;

if ( ! class_exists( '\Wecom_Product_Dimensions\Prices' ) ) {
	class Prices {


		public function __construct() {
			add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_cart_prices' ) );
			add_action( 'woocommerce_cart_item_price', array( $this, 'set_mini_cart_prices' ), 10, 3 );
		}

		public function set_mini_cart_prices( $price_html, $cart_item, $cart_item_key ) {
			$product_id = isset( $cart_item['variation_id'] ) && ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product    = wc_get_product( $product_id );
			$size_type  = $product->get_meta( 'wecom_type' );
			$size_type  = apply_filters( 'wcprd_product_size_type', $size_type, $product );
			if ( $size_type == 'select' ) {
				return $price_html;
			}
			$length = isset( $cart_item['wecom_length'] ) ? intval( $cart_item['wecom_length'] ) : null;
			$width  = isset( $cart_item['wecom_width'] ) ? intval( $cart_item['wecom_width'] ) : null;
			$weight = isset( $cart_item['wecom_weight'] ) ? intval( $cart_item['wecom_weight'] ) : null;
			$sizes  = array(
				'wecom_dimension_length' => $length,
				'wecom_dimension_width'  => $width,
				'wecom_dimension_weight' => $weight,
			);

			$price = self::get_price( $product_id, $size_type, $sizes );

			if ( ! empty( $price ) ) {
				$args = array( 'price' => $price );
				if ( WC()->cart->display_prices_including_tax() ) {
					$product_price = wc_get_price_including_tax( $cart_item['data'], $args );
				} else {
					$product_price = wc_get_price_excluding_tax( $cart_item['data'], $args );
				}
				return wc_price( $product_price );
			}
			return $price_html;
		}

		// Set prices to cart
		public function set_cart_prices( $cart_object ) {
			foreach ( $cart_object->cart_contents as $cart_item ) {

				$product_id = isset( $cart_item['variation_id'] ) && ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
				$product    = wc_get_product( $product_id );
				$size_type  = $product->get_meta( 'wecom_type' );
				$size_type  = apply_filters( 'wcprd_product_size_type', $size_type, $product );
				if ( $size_type == 'select' ) {
					continue;
				}
				$length = isset( $cart_item['wecom_length'] ) ? intval( $cart_item['wecom_length'] ) : null;
				$width  = isset( $cart_item['wecom_width'] ) ? intval( $cart_item['wecom_width'] ) : null;
				$weight = isset( $cart_item['wecom_weight'] ) ? intval( $cart_item['wecom_weight'] ) : null;
				$sizes  = array(
					'wecom_dimension_length' => $length,
					'wecom_dimension_width'  => $width,
					'wecom_dimension_weight' => $weight,
				);

				$price = self::get_price( $product_id, $size_type, $sizes );

				if ( ! empty( $price ) ) {
					$cart_item['data']->set_price( $price );
				}
			}
		}

		// Calculate price
		public static function get_price( $product_id, $size_type, $sizes ) {
			$product = wc_get_product( $product_id );
			if ( empty( $product ) || empty( $size_type ) ) {
				return false;
			}
			if ( 'linear_dimension' === $size_type ) {

				$length = $sizes['wecom_dimension_length'];
				if ( empty( $length ) ) {
					return false;
				}
				$permeter_price = $product->get_price();
				$permeter       = $length / 100;

				$total       = $permeter * $permeter_price;
				$round_total = round( $total, 2 );

				return $round_total;

			} elseif ( 'weight' === $size_type ) {
				$weight = $sizes['wecom_dimension_weight'];
				if ( empty( $weight ) ) {
					return false;
				}
				$weight_unit_price = $product->get_price();
				$total             = $weight * $weight_unit_price;
				return round( $total, 2 );
			}
			return false;
		}

		// Instance of this class.
		protected static $instance = null;


		// Return an instance of this class.
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( self::$instance == null ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

	}

	Prices::get_instance();

}

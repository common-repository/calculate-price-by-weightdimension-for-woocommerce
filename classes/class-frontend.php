<?php

namespace Wecom_Product_Dimensions;

if ( ! class_exists( '\Wecom_Product_Dimensions\Frontend' ) ) {

	class Frontend {


		public function __construct() {
			// Add size meta data in cart.
			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 3 );

			// Add size meta data in order item.
			add_filter( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_data' ), 10, 4 );

			add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_show_meta' ), 99, 3 );
			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'size_inputs' ), 10 );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );

		}

		public function enqueue_scripts() {
			if ( is_product() ) {
				$prod_id = get_the_ID();
				$product = wc_get_product( $prod_id );
				wp_register_script( 'wecom_dimensions_frontend_script', WCPRD_URL . 'assets/js/frontend.js', array( 'jquery' ), false, true );
				$options = array(
					'ajaxurl'        => admin_url( 'admin-ajax.php' ),
					'prodId'         => get_the_ID(),
					'variationIds'   => array(),
					'currencySymbol' => get_woocommerce_currency_symbol(),
					'ajaxnonce'      => wp_create_nonce( 'wecom-dimensions' ),
				);
				if ( get_class( $product ) == 'WC_Product_Variable' ) {
					$options['variationIds'] = $product->get_children();
				}
				wp_localize_script( 'wecom_dimensions_frontend_script', 'wcprd', $options );
				wp_enqueue_script( 'wecom_dimensions_frontend_script' );
			}
		}

		public function size_inputs() {
			global $product;
			$ret_html = '';
			if ( $product->is_type( 'simple' ) ) {
				$wecom_type = $product->get_meta( 'wecom_type' );
				$wecom_type = apply_filters( 'wcprd_product_size_type', $wecom_type, $product );
				if ( empty( $wecom_type ) || 'select' === $wecom_type ) {
					return;
				}
				$ret_html = $this->get_simple_prod_input( $wecom_type, $product );
			}

			echo wp_kses(
				$ret_html,
				array(
					'input'  => array(
						'class' => array(),
						'name'  => array(),
						'type'  => array(),
						'value' => array(),
						'id'    => array(),
						'min'   => array(),
						'max'   => array(),
						'step'  => array(),
					),
					'span'   => array(
						'class' => array(),
						'id'    => array(),
					),
					'div'    => array(
						'class'   => array(),
						'id'      => array(),
						'data-id' => array(),
					),
					'p'      => array(
						'class' => array(),
						'id'    => array(),
					),
					'a'      => array(
						'class' => array(),
						'href'  => array(),
						'id'    => array(),
					),
					'button' => array(
						'class' => array(),
						'type'  => array(),
						'id'    => array(),
					),
					'label'  => array(
						'class' => array(),
						'for'   => array(),
						'id'    => array(),
					),
				)
			);
		}

		public function get_simple_prod_input( $wecom_type, $product ) {
			$type           = apply_filters( 'wecom_product_dimension_type', array() );
			$input_headings = $type[ $wecom_type ]['headings'];
			$input_fields   = $type[ $wecom_type ]['dimensions'];
			$ret_html       = '<div class="wecom-dimensions-front wecom-dimensions-front--simple">';
			$ret_html      .= wp_nonce_field( 'wcprd-add-to-cart', 'wcprdcartnonce', false, false );
			$ret_html      .= '<div class="wecom-dimensions-front__inner" data-id="' . esc_attr( $product->get_id() ) . '">';
			$ret_html      .= '<span>( ' . esc_html__( 'Price', 'wecom-product-dimensions' ) . ' ' . esc_html( $this->dimension_abbr( $wecom_type ) ) . ' : ' . esc_html( $product->get_price() . get_woocommerce_currency_symbol() ) . ' )</span>';
			$ret_html      .= '<span>' . esc_html__( 'Make your choice', 'wecom-product-dimensions' ) . '</span>';
			$ret_html      .= sprintf( '<input class="wecom_dimension_type" name="wecom_dimension_type" type="text" value="%s" />', esc_attr( $wecom_type ) );
			$ret_html      .= '<div class="wecom-dimension-inputs-outer">';
			foreach ( $input_headings as $input_slug => $input_label ) {
				$min      = $product->get_meta( 'wecom_min_' . $input_slug );
				$max      = $product->get_meta( 'wecom_max_' . $input_slug );
				$step     = $product->get_meta( 'wecom_stepper_' . $input_slug );
				$is_fixed = in_array( 'fixed_' . $input_slug, $input_fields ) ? true : false;
				if ( $is_fixed ) {
					$fixed = $product->get_meta( 'wecom_fixed_' . $input_slug );
					$min   = $fixed;
					$max   = $fixed;
				}

				// If there is no min size, set min size as 1
				$min = apply_filters( 'wcprd_min_size_filter', $min, $input_slug, $product );
				// If there is no max size, set max size as PHP_INT_MAX
				$max = apply_filters( 'wcprd_max_size_filter', $max, $input_slug, $product );
				// If there is no stepper, set as 1
				$step = apply_filters( 'wcprd_stepper_filter', $step, $input_slug, $product );

				$dimension_unit = $input_slug == 'weight' ? get_option( 'woocommerce_weight_unit' ) : get_option( 'woocommerce_dimension_unit' );

				// Get min, max, step
				$ret_html .= sprintf(
					'<span class="wecom-dimension-input-wrapper"><label for="wecom_dimension_%2$s">%1$s (%6$s):</label><span><input class="wecom-dimension-input" id="wecom_dimension_%2$s" name="wecom_dimension_%2$s" type="number" value="%3$d" min="%3$s" max="%4$d" step="%5$d" /></span></span>',
					esc_attr( $input_label ),
					esc_attr( $input_slug ),
					esc_attr( $min ),
					esc_attr( $max ),
					esc_attr( $step ),
					esc_attr( $dimension_unit ),
				);
			}
			$ret_html .= '</div>';
			$ret_html .= '<span>' . esc_html__( 'Calculated price', 'wecom-product-dimensions' ) . ': <span class="wecom-dimensions-front__calcPrice"></span></span>';
			$ret_html .= '</div>';
			$ret_html .= '</div>';
			return $ret_html;
		}

		public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
			if ( ! isset( $_POST['wcprdcartnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['wcprdcartnonce'] ) ), 'wcprd-add-to-cart' ) ) {
				return $cart_item_data;
			}

			$type         = filter_input( INPUT_POST, 'wecom_dimension_type' );
			$wecom_length = filter_input( INPUT_POST, 'wecom_dimension_length' );
			$wecom_width  = filter_input( INPUT_POST, 'wecom_dimension_width' );
			$wecom_weight = filter_input( INPUT_POST, 'wecom_dimension_weight' );

			if ( ! empty( $wecom_length ) ) {
				$cart_item_data['wecom_length'] = $wecom_length;
			}

			if ( ! empty( $wecom_width ) ) {
				$cart_item_data['wecom_width'] = $wecom_width;
			}

			if ( ! empty( $wecom_weight ) ) {
				$cart_item_data['wecom_weight'] = $wecom_weight;
			}

			if ( ! empty( $type ) ) {
				$cart_item_data['wecom_dimension_type'] = $type;
			}

			return $cart_item_data;

		}

		public function add_order_item_data( $item, $cart_item_key, $values, $order ) {
			if ( ! empty( $values['wecom_length'] ) ) {
				$item->add_meta_data( __( 'Length', 'wecom-product-dimensions' ), $values['wecom_length'] );
			}

			if ( ! empty( $values['wecom_width'] ) ) {
				$item->add_meta_data( __( 'Width', 'wecom-product-dimensions' ), $values['wecom_width'] );
			}

			if ( ! empty( $values['wecom_weight'] ) ) {
				$item->add_meta_data( __( 'Weight', 'wecom-product-dimensions' ), $values['wecom_weight'] );
			}
		}

		public function cart_item_show_meta( $item_name, $cart_item, $cart_item_key ) {
			$product    = $cart_item['data'];
			$wecom_type = get_post_meta( $product->get_id(), 'wecom_type', true );

			if ( empty( $wecom_type ) ) {
				return $item_name;
			}

			if ( ! empty( $cart_item['wecom_width'] ) && empty( $cart_item['wecom_length'] ) ) {
				$item_name .= '<br><small class="product-sku">' . esc_html__( 'Length', 'wecom-product-dimensions' ) . ': ' . esc_html( $cart_item['wecom_width'] ) . '</small>';
			}

			if ( ! empty( $cart_item['wecom_length'] ) && empty( $cart_item['wecom_width'] ) ) {
				$item_name .= '<br><small class="product-sku">' . esc_html__( 'Width', 'wecom-product-dimensions' ) . ': ' . esc_html( $cart_item['wecom_length'] ) . '</small>';
			}

			if ( ! empty( $cart_item['wecom_length'] ) && ! empty( $cart_item['wecom_width'] ) ) {
				$item_name .= '<br><small class="product-sku">' . esc_html__( 'Dimensions', 'wecom-product-dimensions' ) . ': ' . esc_html( $cart_item['wecom_width'] . ' x ' . $cart_item['wecom_length'] ) . 'cm</small>';
			}

			if ( ! empty( $cart_item['wecom_weight'] ) ) {
				$item_name .= '<br><small class="product-sku">' . esc_html__( 'Weight', 'wecom-product-dimensions' ) . ': ' . esc_html( $cart_item['wecom_weight'] ) . '</small>';
			}

			return $item_name;
		}

		private function dimension_abbr( $type ) {
			$abbreviations = array(
				'linear_dimension' => 'm',
				'weight'           => get_option( 'woocommerce_weight_unit' ),
			);
			if ( ! isset( $abbreviations[ $type ] ) ) {
				return '';
			}
			return $abbreviations[ $type ];
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

	Frontend::get_instance();

}

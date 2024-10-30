<?php

namespace Wecom_Product_Dimensions;

if ( ! class_exists( '\Wecom_Product_Dimensions\Backoffice' ) ) {

	class Backoffice {


		public function __construct() {
			add_action( 'woocommerce_product_options_pricing', array( $this, 'simple_product_display_fields' ) );

			add_action( 'save_post', array( $this, 'save_meta_dimensions' ), 10, 2 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20, 1 );
		}

		public function enqueue_scripts( $hook_suffix ) {
			global $post;
			if ( $hook_suffix == 'post.php' && $post->post_type == 'product' ) {
				wp_register_script( 'wecom_dimensions_backoffice_script', WCPRD_URL . 'assets/js/backoffice.js', array( 'jquery' ), false, true );
				wp_enqueue_script( 'wecom_dimensions_backoffice_script' );
			}
		}

		public function simple_product_display_fields() {
			wp_nonce_field( 'wecom_save_dimensions_action', '_wecom_nonce_field', false );

			$id = get_the_ID();

			$type        = apply_filters( 'wecom_product_dimension_type', array() );
			$dimension   = apply_filters( 'wecom_product_dimension_labels', array() );
			$size_unit   = get_option( 'woocommerce_dimension_unit' );
			$weight_unit = get_option( 'woocommerce_weight_unit' );
			$wecom_type  = get_post_meta( $id, 'wecom_type', true );

			$select = array();
			foreach ( $type as $k => $v ) {
				$select[ $k ] = $v['title'];
			};

			?> <hr>

			<?php

			woocommerce_wp_select(
				array(
					'id'            => 'wecom_type',
					'label'         => 'Measurement:',
					'wrapper_class' => 'form-field ' . $id,
					'value'         => $wecom_type,
					'options'       => $select,
				),
			);

			woocommerce_wp_text_input(
				array(
					'id'    => 'wcprdbackofficenonce',
					'name'  => 'wcprdbackofficenonce',
					'label' => '',
					'value' => wp_create_nonce( 'wcprd-backoffice-save-fields' ),
					'type'  => 'hidden',
				)
			);

			foreach ( $dimension as $k => $v ) {

				$class = '';

				foreach ( $type as $k_type => $v_value ) {
					foreach ( $v_value['dimensions'] as $dim ) {
						if ( $dim === $k ) {
							$class .= ' ' . $k_type;
						}
					}
				}

				$value = get_post_meta( $id, 'wecom_' . $k, true );
				$unit  = strpos( $k, 'weight' ) !== false ? $weight_unit : $size_unit;
				woocommerce_wp_text_input(
					array(
						'id'                => 'wecom_' . $k,
						'label'             => $v . ' ' . $unit,
						'wrapper_class'     => 'form-field wecom_dimensions ' . $class . ' ' . $id,
						'value'             => $value,
						'type'              => 'number',
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '1',
						),
					)
				);
			}
			?>
			<hr>
			<?php
		}

		public function save_meta_dimensions( $post_id, $post ) {
			if ( ! isset( $_POST['_wecom_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wecom_nonce_field'] ) ), 'wecom_save_dimensions_action' ) ) {
				return $post_id;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			if ( 'product' !== $post->post_type ) {
				return $post_id;
			}

			$product = wc_get_product( $post_id );

			if ( isset( $_POST['wecom_type'] ) ) {

				$product->update_meta_data( 'wecom_type', sanitize_text_field( wp_unslash( $_POST['wecom_type'] ) ) );
			}

			$dimension = apply_filters( 'wecom_product_dimension_labels', array() );

			$dimensions = array_keys( $dimension );

			foreach ( $dimensions as $key ) {
				if ( isset( $_POST[ 'wecom_' . $key ] ) ) {
					$product->update_meta_data( 'wecom_' . $key, sanitize_text_field( wp_unslash( $_POST[ 'wecom_' . $key ] ) ) );
				}
			}

			$product->save();

			return $post_id;

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

	Backoffice::get_instance();

}

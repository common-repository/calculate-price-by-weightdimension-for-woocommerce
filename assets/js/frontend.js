jQuery( document ).ready(
	function ($) {
		// wcprd.ajaxurl
		// Add +- buttons
		$( '.wecom-dimension-input' ).each(
			function (index) {
				// Disable inputs where min value == max value
				// Clone & keep as hidden input in order to send it in form submissions (disabled inputs are ignored by default)
				if ( $( this ).attr( 'min' ) == $( this ).attr( 'max' ) ) {
					$clonedElem = $( this ).clone();
					$clonedElem.attr( 'type', 'hidden' );
					$clonedElem.insertAfter( this );
					$( this ).attr( 'disabled', 'disabled' );
					return;
				}
				$plus  = $( '<button class="button-plus">+</button>' );
				$minus = $( '<button class="button-minus">-</button>' );

				$minus.insertBefore( this );
				$plus.insertAfter( this );
			}
		);
		// Add functions to +- buttons
		$( '.wecom-dimension-input-wrapper .button-plus, .wecom-dimension-input-wrapper .button-minus' ).click(
			function (e) {
				e.preventDefault();
				$input       = $( this ).closest( '.wecom-dimension-input-wrapper' ).find( '.wecom-dimension-input' );
				max          = parseInt( $input.attr( 'max' ) );
				min          = parseInt( $input.attr( 'min' ) );
				step         = parseInt( $input.attr( 'step' ) );
				currentValue = parseInt( $input.val() );
				nextValue    = currentValue;
				if ( $( this ).hasClass( 'button-plus' ) ) {
					nextValue = currentValue + step;
				} else if ($( this ).hasClass( 'button-minus' )) {
					nextValue = currentValue - step;
				}
				if (nextValue >= min && nextValue <= max ) {
					$input.attr( 'value', nextValue );
					$input.val( nextValue );
				} else if (nextValue < min) {
					$input.attr( 'value', min );
					$input.val( min );
				} else if (nextValue > max) {
					$input.attr( 'value', max );
					$input.val( max );
				}
				$input.trigger( 'change' );
			}
		);
		productIds = [];
		// Add on change validation to inputs
		if ( wcprd.variationIds.length == 0 ) {
			// Simple product
			productIds = [wcprd.prodId];
			
		}
		productIds.forEach(
			function (productId) {
				$( '.wecom-dimensions-front__inner[data-id="' + productId + '"] .wecom-dimension-input' ).on(
					'change',
					function (e) {
						value = parseInt( $( this ).val() );
						max   = parseInt( $( this ).attr( 'max' ) );
						min   = parseInt( $( this ).attr( 'min' ) );
						step  = parseInt( $( this ).attr( 'step' ) );
						if ( value < min ) {
							$( this ).val( min );
						} else if ( value > max ) {
							$( this ).val( max );
						} else if ((value - min) % step != 0) {
							// If selected value not in step range selection: set floor step
							stepsToAdd = Math.floor( (value - min) / step );
							$( this ).val( min + stepsToAdd * step );
						}
						$( this ).closest( '.wecom-dimensions-front__inner' ).find( '.wecom-dimensions-front__calcPrice' ).trigger( 'updatePrice', productId );
					}
				);
			}
		);

		$( '.wecom-dimensions-front__calcPrice' ).on(
			'updatePrice',
			function (e, productId) {
				size_type = $( '.wecom-dimensions-front__inner[data-id="' + productId + '"] input.wecom_dimension_type' ).val();
				sizes     = {};
				$( '.wecom-dimensions-front__inner[data-id="' + productId + '"] .wecom-dimension-input' ).each(
					function () {
						dimension          = $( this ).attr( 'name' ).replace( /\[[0-9]+\]/, '' );
						value              = $( this ).val();
						sizes[ dimension ] = value;
					}
				);
				getPrice( productId, size_type, sizes );
			}
		);

		// On input change getPrice with ajax & update it on page
		function getPrice (product_id, sizeType, prodSizes) {
			$.ajax(
				{
					type: "post",
					dataType: "json",
					url: wcprd.ajaxurl,
					data: {
						action: "wecom_dimensions_get_price",
						prodId: product_id,
						wecomSizes: prodSizes,
						sizeType: sizeType,
						security: wcprd.ajaxnonce,
					},
					success: function (resp) {
						if (resp.success && resp.data.price != false) {
							price = resp.data.price;
							$( '.wecom-dimensions-front__inner[data-id="' + product_id + '"] .wecom-dimensions-front__calcPrice' ).text( price + wcprd.currencySymbol );
						} else {
							alert( 'Get price failed' );
						}
					},
				}
			);
		}

	}
);

jQuery( document ).ready(
	function ($) {
		// Simple product
		selectedType = $( '.options_group #wecom_type' ).val()
		$( '.options_group #wecom_type' ).closest( '.options_group' ).find( '.wecom_dimensions.' + selectedType ).show( 400 );
		$( '.options_group #wecom_type' ).change(
			function (e) {
				selectedType = $( this ).val();
				$( this ).closest( '.options_group' ).find( '.wecom_dimensions' ).hide( 400 );
				$( this ).closest( '.options_group' ).find( '.wecom_dimensions.' + selectedType ).show( 400 );
			}
		);

	}
);

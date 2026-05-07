/* global gcowAnalyticsData, jQuery */
( function ( $ ) {
	$( document ).ready( function () {
		if ( typeof gcowAnalyticsData === 'undefined' ) {
			return;
		}

		$( '.gcow-bump-container' ).each( function () {
			var bumpId = $( this ).data( 'bump-id' );
			if ( ! bumpId ) {
				return;
			}
			$.ajax( {
				url: gcowAnalyticsData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'gcow_track_impression',
					nonce: gcowAnalyticsData.nonce,
					bump_id: bumpId,
				},
			} );
		} );
	} );
} )( jQuery );

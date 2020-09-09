jQuery( document ).ready( function( $ ) {
	// Create dialog box
	$( '#ds-dialog-box' ).dialog( {
		title: '',
		dialogClass: 'wp-dialog',
		autoOpen: false,
		draggable: false,
		width: 'auto',
		modal: true,
		resizable: false,
		closeOnEscape: false,
		position: {
			my: "center",
			at: "center",
			of: window
		},
		open: function( event, ui ) {},
		create: function() {},
	} );
	
	// Show disable notice.
	$( document ).on( 'click', '.ds-invalid', function() {
		var type = DS.is_post;
		if ( $( this ).parents( 'tr' ).hasClass( 'type-page' ) ) {
			type = DS.is_page;
		}
		var message = '';
		if ( $( this ).hasClass( 'ds-invalid-view' ) ) {
			message = DS.view_message;
		} else if( $( this ).hasClass( 'ds-invalid-trash' ) ) {
			message = DS.trash_message;
		} else if( $( this ).hasClass( 'ds-invalid-untrash' ) ) {
			message = DS.untrash_message;
		} else if( $( this ).hasClass( 'ds-invalid-delete' ) ) {
			message = DS.delete_message;
		} else if( $( this ).hasClass( 'ds-invalid-preview' ) ) {
			message = DS.preview_message;
		} else {
			message = DS.edit_message;
		}
		$( '#ds-dialog-box' ).find( 'h2' ).html( message + ' ' + type );
		$( '#ds-dialog-box' ).dialog( 'open' );
	} );
	// Remove edit link from title bar.
	$( document ).on( 'click', '.column-title .row-title', function() {
		var titleParent = $( this ).parents( 'tr' );
		var isDisabled = titleParent.find( '.row-actions' ).find( '.ds-invalid' ).length;
		var type = DS.is_post;
		if ( titleParent.hasClass( 'type-page' ) ) {
			type = DS.is_page;
		}
		if ( isDisabled ) {
			var message = '';
			if ( $( this ).hasClass( 'ds-invalid-view' ) ) {
				message = DS.view_message;
			} else if( $( this ).hasClass( 'ds-invalid-trash' ) ) {
				message = DS.trash_message;
			} else if( $( this ).hasClass( 'ds-invalid-untrash' ) ) {
				message = DS.untrash_message;
			} else if( $( this ).hasClass( 'ds-invalid-delete' ) ) {
				message = DS.delete_message;
			} else if( $( this ).hasClass( 'ds-invalid-preview' ) ) {
				message = DS.preview_message;
			} else {
				message = DS.edit_message;
			}
			$( '#ds-dialog-box' ).find( 'h2' ).html( message + ' ' + type );
			$( '#ds-dialog-box' ).dialog( 'open' );
			return false;
		}
	} );
} );
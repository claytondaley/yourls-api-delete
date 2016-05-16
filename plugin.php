<?php
/*
Plugin Name: API Action - Delete
Plugin URI: http://www.claytondaley.com/
Description: Adds a "delete" action to the API. This action requires authentication even if the site is public. This action accepts either a shorturl or keyword passed using the "shorturl" parameter.
Version: 1.0
Author: Clayton Daley (derived from API-Action by Ozh)
Author URI: http://www.claytondaley.com/
*/

// Define custom action "delete"
yourls_add_filter( 'api_action_delete', 'clayton_api_action_delete' );

// Actually delete
function clayton_api_action_delete() {
	// We don't want unauthenticated users deleting links
	// If YOURLS is in public mode, force authentication anyway
	if (!yourls_is_private()) {
		yourls_do_action( 'require_auth' );
		require_once( YOURLS_INC.'/auth.php' );
	}

	// Need 'shorturl' parameter
	if( !isset( $_REQUEST['shorturl'] ) ) {
		return array(
			'statusCode' => 400,
			'simple'     => "Need a 'shorturl' parameter",
			'message'    => 'error: missing param',
		);	
	}
	
	$shorturl = $_REQUEST['shorturl'];

	// Check if valid shorturl
	if( !yourls_is_shorturl( $shorturl ) ) {
		return array(
			'statusCode' => 404,
			'simple'    => 'Error: short URL not found',
			'message'    => 'error: not found',
		);	
	}
	
	// Is $shorturl a URL (http://sho.rt/abc) or a keyword (abc) ?
	if( yourls_get_protocol( $shorturl ) ) {
		$keyword = yourls_get_relative_url( $shorturl );
	} else {
		$keyword = $shorturl;
	}
	
	// Delete shorturl
	if( yourls_delete_link_by_keyword( $keyword ) ) {
		return array(
			'statusCode' => 200,
			'simple'     => "Shorturl $shorturl deleted",
			'message'    => 'success: deleted',
		);	
	} else {
		return array(
			'statusCode' => 500,
			'simple'     => 'Error: could not delete shorturl, not sure why :-/',
			'message'    => 'error: unknown error',
		);	
	}
}

<?php
/*
Plugin Name: Paid Memberships Pro - Member Homepages Add On
Plugin URI: http://www.paidmembershipspro.com/pmpro-member-homepages/
Description: Redirect members to a unique homepage/landing page based on their level.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/

/*
	Function to redirect member on login to their membership level's homepage
*/
function pmpromh_login_redirect( $redirect_to, $request, $user ) {
	// check level
	if ( ! empty( $user ) && ! empty( $user->ID ) && function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		$level = pmpro_getMembershipLevelForUser( $user->ID );
		if ( ! empty( $level ) ) {
			$member_homepage_id = pmpromh_getHomepageForLevel( $level->id );
		}
		if ( ! empty( $member_homepage_id ) ) {
			$redirect_to = get_permalink( $member_homepage_id );
		}
	}

	return $redirect_to;
}
add_filter( 'login_redirect', 'pmpromh_login_redirect', 10, 3 );

/*
	Function to redirect member to their membership level's homepage when
	trying to access your site's front page (static page or posts page).
*/

function pmpromh_template_redirect_homepage() {
	global $current_user;
	// is there a user to check?
	if ( ! empty( $current_user->ID ) && is_front_page() ) {
		$member_homepage_id = pmpromh_getHomepageForLevel();
		if ( ! empty( $member_homepage_id ) && ! is_page( $member_homepage_id ) ) {
			wp_redirect( get_permalink( $member_homepage_id ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'pmpromh_template_redirect_homepage' );

/*
	Function to get a homepage for level
*/
function pmpromh_getHomepageForLevel( $level_id = null ) {
	if ( empty( $level_id ) && function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
		global $current_user;
		$level = pmpro_getMembershipLevelForUser( $current_user->ID );
		if ( ! empty( $level ) ) {
			$level_id = $level->id;
		}
	}

	// look up by level
	if ( ! empty( $level_id ) ) {
		$member_homepage_id = get_option( 'pmpro_member_homepage_' . $level_id );
	} else {
		$member_homepage_id = false;
	}

	return $member_homepage_id;
}

/*
	Settings
*/
function pmpromh_pmpro_membership_level_after_other_settings() {
?>
<table>
<tbody class="form-table">
	<tr>
		<td>
			<tr>
				<th scope="row" valign="top"><label for="member_homepage"><?php _e( 'Member Homepage', 'pmpromh' ); ?>:</label></th>
				<td>
					<?php
						$level_id = intval( $_REQUEST['edit'] );
						$member_homepage_id = pmpromh_getHomepageForLevel( $level_id );
					?>
					<?php
					wp_dropdown_pages(
						array(
							'name' => 'member_homepage_id',
							'show_option_none' => '-- ' . __( 'Choose One', 'pmpro' ) . ' --',
							'selected' => $member_homepage_id,
						)
					);
					?>
									
				</td>
			</tr>
		</td>
	</tr> 
</tbody>
</table>
<?php
}
add_action( 'pmpro_membership_level_after_other_settings', 'pmpromh_pmpro_membership_level_after_other_settings' );

/*
	Save the member homepage.
*/
function pmpromh_pmpro_save_membership_level( $level_id ) {
	if ( isset( $_REQUEST['member_homepage_id'] ) ) {
		update_option( 'pmpro_member_homepage_' . $level_id, $_REQUEST['member_homepage_id'] );
	}
}
add_action( 'pmpro_save_membership_level', 'pmpromh_pmpro_save_membership_level' );

/*
	Function to add links to the plugin row meta
*/
function pmpromh_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'pmpro-member-homepages.php' ) !== false ) {
		$new_links = array(
			'<a href="' . esc_url( 'http://www.paidmembershipspro.com/add-ons/plus-add-ons/member-homepages/' ) . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url( 'http://paidmembershipspro.com/support/' ) . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'pmpromh_plugin_row_meta', 10, 2 );

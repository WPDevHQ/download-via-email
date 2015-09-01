<?php
/**
 * Options Page.
 *
 * Page to set all the basic settings for the plugin.
 *
 * @package  email-downloads
 */

function email_downloads_add_admin_menu() {
	add_menu_page(
		__( 'Email Downloads', 'email-downloads' ),
		__( 'Email Downloads', 'email-downloads' ),
		'manage_options',
		'email_downloads',
		'email_downloads_options_page_callback',
		'dashicons-email-alt'
	);
}
add_action( 'admin_menu', 'email_downloads_add_admin_menu' );

function email_downloads_settings_init() { 

	register_setting( 'ed', 'email_downloads_settings' );

	add_settings_section(
		'email_downloads_ed_section',						//id
		__( 'Basic Settings', 'email-downloads' ),			//title
		'email_downloads_settings_section_callback',		//callback
		'ed'												//page
	);

	add_settings_field( 
		'ed_sender_email', 
		__( 'Sender Email', 'email-downloads' ), 
		'email_downloads_sender_email_render', 
		'ed', 
		'email_downloads_ed_section' 
	);

	add_settings_field( 
		'ed_sender_name', 
		__( 'Name of the Sender', 'email-downloads' ), 
		'email_downloads_sender_name_render', 
		'ed', 
		'email_downloads_ed_section' 
	);

}
add_action( 'admin_init', 'email_downloads_settings_init' );

function email_downloads_sender_email_render() {
	$options = get_option( 'email_downloads_settings' ); ?>

	<input type="email" class="regular-text" name="email_downloads_settings[ed_sender_email]" value="<?php echo $options['ed_sender_email'] ? $options['ed_sender_email'] : get_option( 'admin_email' ); ?>"> <em class="howto"><span class="dashicons dashicons-info"></span> <?php _e( "<strong>default:</strong> administrator's email address. Make sure to put an on-domain email address like <code>something@yourdomain.com</code>, otherwise the email may not be sent.", "email-downloads" ); ?></em>

	<?php
}

function email_downloads_sender_name_render() {
	$options = get_option( 'email_downloads_settings' );
	$admin_email = get_option( 'admin_email' );
	$admin_user = get_user_by( 'email', $admin_email );
	?>

	<input type="text" class="regular-text" name="email_downloads_settings[ed_sender_name]" value="<?php echo $options['ed_sender_name'] ? $options['ed_sender_name'] : $admin_user->display_name; ?>"> <em class="howto"><span class="dashicons dashicons-info"></span> <?php _e( "<strong>default:</strong> administrator's display name", "email-downloads" ); ?></em>

	<?php
}

function email_downloads_settings_section_callback() {
	_e( 'Settings that will introduce you on every email', 'email-downloads' );
}

function email_downloads_options_page_callback() { ?>
	<div class="wrap">
		<form action='options.php' method='post'>		
			<h2><span class="dashicons dashicons-email-alt"></span> <?php _e( 'Email Downloads', 'email-downloads' ); ?></h2>		
			<?php
			settings_fields( 'ed' );
			do_settings_sections( 'ed' );
			submit_button();
			?>
		</form>


	<?php
	/**
	 * Pagination in action
	 * @link http://tareq.wedevs.com/2011/07/simple-pagination-system-in-your-wordpress-plugins/
	 * @author  Tareq Hasan
	 */
	$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

	$posts_per_page = get_option( 'posts_per_page' );
	$offset = ( $pagenum - 1 ) * $posts_per_page;

	$get_emails = nano_email_lists( $posts_per_page, $offset );

	if( $get_emails ) :
		$_counter = 0; ?>
			<hr>

			<h2><?php _e( 'Stored Email Addresses', 'email-downloads' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Serial', 'email-downloads' ); ?></th>
						<th><?php _e( 'Email', 'email-downloads' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach ($get_emails as $emails) :
					$_counter++;
					?>				
					<tr>
						<td><?php echo $_counter; ?></td>
						<td><?php echo $emails; ?></td>
					</tr>
				<?php
				endforeach;
				?>
				</tbody>
			</table>
			<?php
			global $wpdb;
			$option_table = $wpdb->prefix .'options';

			$total = $wpdb->get_var( "SELECT COUNT('option_name') FROM {$option_table} WHERE option_name LIKE 'edmail_%' GROUP BY option_id" );
			$num_of_pages = ceil( $total / $posts_per_page );

			$page_links = paginate_links( array(
			    'base'		=> add_query_arg( 'pagenum', '%#%' ),
			    'format'	=> '',
			    'prev_text'	=> __( '&laquo;', 'aag' ),
			    'next_text'	=> __( '&raquo;', 'aag' ),
			    'total'		=> $num_of_pages,
			    'current'	=> $pagenum
			) );
			 
			if ( $page_links ) {
			    echo '<div class="tablenav"><div class="tablenav-pages" style="margin: 1em 0">'. $page_links .'</div></div>';
			}
			?>
			<br>
			<?php
			/*
			// Make CSV from the db storage
			function download_csv_results( $results, $name = NULL ) {
			    if( ! $name)
			    	$name = md5(uniqid() . microtime(TRUE) . mt_rand()). '.csv';

			    header('Content-Type: text/csv');
			    header('Content-Disposition: attachment; filename='. $name);
			    header('Pragma: no-cache');
			    header("Expires: 0");

			    $outstream = fopen("php://output", "w");

			    foreach( $results as $result ) {
			        fputcsv( $outstream, $result );
			    }

			    fclose($outstream);
			}

			if( isset($_POST['csv_submit']) ) {
				$email = $emails->email;
				download_csv_results( $email, 'something.csv' );
			}
			?>
			<form action="" method="post">
				<button class="button" type="submit" name="csv_submit">Download as CSV</button>
			</form>
			<?php */ ?>
	<?php endif; ?>

	</div> <!-- .wrap -->
	<?php
}
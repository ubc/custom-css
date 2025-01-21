<?php

	add_meta_box( 'revisionsdiv', __( 'CSS Revisions', 'safecss' ), array('Improved_Simpler_CSS', 'revisions_meta_box'), 's-custom-css', 'side' );
	add_meta_box( 'settingsdiv', __( 'Settings', 'safecss' ), array('Improved_Simpler_CSS', 'settings_meta_box'), 's-custom-css', 'side' );

	$message = '';
	if ( isset( $_POST ) ) {

		if ( isset( $_POST['update_custom_css_field'] ) && isset( $_POST['editor'] ) ) {

			$nonce = $_POST['update_custom_css_field'];
			if ( wp_verify_nonce( $_POST['update_custom_css_field'], 'update_custom_css' ) ) {
				Improved_Simpler_CSS::update_css( $_POST['editor'] );
				$message = 'Updated Custom CSS';
			}
		}
	}

	$data = Improved_Simpler_CSS::get( false );

	if( isset($_GET['revision']) ) {

		$message = "Custom CSS restored to revision from ".self::revision_time( $data );

		Improved_Simpler_CSS::update_files( $data );

	}

	if ( isset( $data->post_excerpt ) ) {
		if ( ! file_exists( Improved_Simpler_CSS::$object->path_to . $data->post_excerpt ) ) {
			$message .= ' File Doesn\'t exist! Save the changes to create the file again';
		}
	}

	if( is_array( Improved_Simpler_CSS::$object->error ) ) {

		$message .= implode( ', ', Improved_Simpler_CSS::$object->error );
	}



	?>
	<div class="wrap">
		<div id="icon-themes" class="icon32"></div>
		<h2>Custom CSS</h2>
		<?php if( !empty( $message ) ): ?>
			<div class="updated below-h2" id="message"><p><?php echo $message; ?></p></div>
		<?php endif; ?>
		<form action="themes.php?page=custom-css" method="post" >
			<?php wp_nonce_field( 'update_custom_css','update_custom_css_field' ); ?>
			<div class="metabox-holder has-right-sidebar">

				<div class="inner-sidebar">

					<div class="postbox">
						<div class="inside" style="padding-bottom: 0;">
							<input class="button-primary" type="submit" name="publish" value="<?php _e( 'Update' ); ?>" />
						</div>
					</div>
					<?php
				do_meta_boxes( 's-custom-css', 'side', $data );

				?>
				</div> <!-- .inner-sidebar -->

				<?php
					$data = ( is_object( $data ) && isset( $data->post_content ) ) ? $data->post_content : '';
				?>

				<div id="post-body">
					<div id="post-body-content">
						<div id="global-editor-shell">
						<textarea  style="width:100%; height: 360px; resize: none;" id="editor" class="wp-editor-area" name="editor"><?php echo $data; ?></textarea>
						</div>
					</div> <!-- #post-body-content -->
				</div> <!-- #post-body -->




			</div> <!-- .metabox-holder -->
		</form>
		<div id="footer-info">
			db v<?php echo get_option( 'improved-custom-css-verion' ); ?> |
			<?php

			if( is_dir( Improved_Simpler_CSS::$object->path_to ) ): ?>
				folder create
			<?php endif; ?>



		 </div>
	</div> <!-- .wrap -->

<?php
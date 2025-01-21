<?php
/*
Plugin Name: Custom CSS
Plugin URI:
Description: Lets you add custom css to your web site
Version: 2.0.4
Author: CTLT Dev
Author URI: http://ctlt.ubc.ca

*/

/*  Copyright 2013  Enej - CTLT

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Improved_Simpler_CSS class.
 */
class Improved_Simpler_CSS {

	static $file_name;
	static $min_file_name;
	static $min_file_name_postfix;
	static $object;
	static $url;
	static $load_style;

	/**
	 * init function.
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {

		require( 'lib/cpt-to-file.php' );

		self::$url = false;
		self::$load_style = false;
		add_action('init', array( __CLASS__, 'start' ) );

		add_action( 'admin_init' , array(__CLASS__, 'admin' ) );
		add_action( 'admin_menu' , array(__CLASS__, 'admin_menu' ) );

		add_action( 'wp_ajax_submit_css', array(__CLASS__, 'ajax' ) );

		add_action( 'wp_enqueue_scripts', 	array( __CLASS__, 'load_scripts' ) );
		add_action( 'init',    array( __CLASS__, 'load_style' ) );
		add_action( 'admin_bar_menu', 		array(__CLASS__, 'adminbar_link' ),100 );


		if ( get_option( 'custom_css_include_in_editor' ) ) {
			add_action( 'enqueue_block_assets', array(__CLASS__, 'include_css_in_block_editor' ));
		}
		add_action( 'wp_head', array(__CLASS__, 'include_css' ));

		add_filter( 'CPT_to_file_save_to_file_filter-s-custom-css', array(__CLASS__, 'minify_css' ) );

		add_action( 'save_post', array( __CLASS__, 'save_settings' ), 10, 2 );

	}

	/**
	 * start function.
	 *
	 * @access public
	 * @return void
	 */
	public static function start(){

		self::create_object();

		self::$object->register();
		if( is_admin() ):
			self::version_update();
		endif;

		if ( isset( $_GET['delete-test'] ) && $_GET['delete-test'] ) {
			self::$object->delete_all();
		}
	}

	/**
	 * create_object function.
	 *
	 * @access public
	 * @return void
	 */
	public static function create_object() {

		self::$object = new CPT_to_file('s-custom-css', 'true', 'css', '.css' );

	}

	/**
	 * load_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public static function load_scripts() {

		/* only load it if the user can edit the stuff */
		if ( !(current_user_can( 'manage_options' )) || !is_admin_bar_showing() || is_admin() )
			return true;

		wp_enqueue_script( 'css-edit-window', plugins_url('js/edit-window.js', __FILE__), array('jquery'), 2, true );

		$custom_css_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'loader_image' => admin_url('images/wpspin_light.gif'),
			'editor' => plugins_url('js/editor.js', __FILE__),
			'load_ace' => plugins_url('ace/ace.js', __FILE__)
		);
		wp_localize_script( 'css-edit-window', 'custom_css_options', $custom_css_options );


	}

	/**
	 * version_update function.
	 *
	 * @access public
	 * @return void
	 */
	public static function version_update(){

		// do we need to run a version update?
		if( get_option( 'improved-custom-css-verion' ) < 3 ) {

			$data = self::$object->get();

			if( is_object( $data ) ) {
				// let's resave it
				self::update_css( $data->post_content );

			}
			update_option( 'improved-custom-css-verion', 3 );
		}

	}

	/**
	 * load_style function.
	 *
	 * @access public
	 * @return void
	 */
	public static function load_style(){
		/* only load it if the user can edit the stuff */
		if( !is_admin() ):
			self::$load_style = true;
		endif;
		/*if ( !(current_user_can( 'manage_options' )) && !is_admin() ):
			self::$url = self::get_url();
			if( self::$url ):
				wp_register_style( 'custom-css', self::get_url() );
				wp_enqueue_style( 'custom-css' );
			endif;

			return true;

		endif;
		*/

	}

	/**
	 * include_css function.
	 *
	 * @access public
	 * @return void
	 */
	public static function include_css() {
		self::$url = self::get_url();

		if( current_user_can( 'manage_options') || !self::$url ):
			echo '<style id="simpler-css-style" type="text/css">' . "\n";
			echo self::get_css();
			echo '</style><!-- end of custom css -->' . "\n";
		elseif( self::$load_style ):
			echo "<link rel='stylesheet' id='custom-css-css'  href='".self::make_url_agnostic( self::$url)."' type='text/css' media='all' />";
		endif;

	}

	public static function include_css_in_block_editor() {
		self::$url = self::get_url();

		wp_enqueue_style( 'simpler-css-in-editor', self::$url );
	}

	public static function make_url_agnostic( $url ) {

		$pattern = "/^https?:/i";
		return preg_replace( $pattern, "", trim( $url ) );
	}


	/**
	 * adminbar_link function.
	 *
	 * @access public
	 * @return void
	 */
	public static function adminbar_link() {

		global $wp_admin_bar;
		if ( !(current_user_can( 'manage_options' )) || !is_admin_bar_showing() || is_admin() )
			return;


		$wp_admin_bar->add_node( array(
			'id' => 'improved-simpler-css-link',
			'title' => __( 'Edit custom CSS'),
			'href' => '#',
		) );
	}

	/**
	 * admin function.
	 *
	 * @access public
	 * @return void
	 */
	public static function admin() {
		global $pagenow;

		if( 'post.php' == $pagenow && isset($_GET['post']) && isset($_GET['revision']) && isset($_GET['message']) && '5' == $_GET['message'] ){

			$data = get_post($_GET['post'] );
			if( $data->post_type == 's-custom-css' ) {
				wp_redirect( admin_url('themes.php?page=custom-css&revision='.$_GET['revision']) );
			}
		}

		//
		add_action( 'admin_print_scripts-appearance_page_simpler-css/simpler-css', 'improved_simpler_css_admin_enqueue_scripts');
		add_action( 'admin_print_styles-appearance_page_simpler-css/simpler-css', 'improved_simpler_css_admin_print_styles');

	}

	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public static function admin_menu(){

		$page_hook_suffix = add_theme_page( 'custom-css', 'Custom CSS',  'manage_options', 'custom-css', array( __CLASS__, 'admin_page' ) );

		add_action( 'admin_print_scripts-' . $page_hook_suffix, array(__CLASS__, 'admin_scripts' ) );
	}


	/**
	 * admin_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public static function admin_scripts() {
			wp_enqueue_style( 'custom-js-admin-styles', plugins_url( '/css/admin.css', __FILE__ ) );

			wp_register_script( 'acejs', plugins_url( '/ace/ace.js', __FILE__ ), '', '1.0', 'true' );
			wp_enqueue_script( 'acejs' );

			wp_register_script( 'aceinit', plugins_url( '/js/admin.js', __FILE__ ), array('acejs', 'jquery-ui-resizable'), '1.1', 'true' );
			wp_enqueue_script( 'aceinit' );

	}

	/**
	 * admin_page function.
	 *
	 * @access public
	 * @return void
	 */
	public static function admin_page() {

		require('view/admin.php');

	}

	/**
	 * ajax function.
	 *
	 * @access public
	 * @return void
	 */
	public static function ajax() {

		if( !current_user_can( 'manage_options' ) ):
			echo "Permission error. Try logging in again.";
			die();
		endif;

		if( isset( $_POST['css'] ) ):

			if( self::update_css(   $_POST['css'] ) ):
				echo "success";
			else:
				echo "Error saving data.";
			endif;
		endif;

		die(); // this is required to return a proper result
	}

	/**
	 * update_css function.
	 *
	 * @access public
	 * @param mixed $css (default: null)
	 * @return void
	 */
	public static function update_css( $css = null ) {

		return self::$object->update( strip_tags( $css ) );
	}


	/**
	 * get_css function.
	 *
	 * @access public
	 * @return void
	 */
	public static function get( $form_cache = true ) {
		return self::$object->get( $form_cache );
	}

	/**
	 * get_css function.
	 *
	 * @access public
	 * @param bool $form_cache (default: true)
	 * @return void
	 */
	public static function get_css( $form_cache = true ) {

		if( !is_object( self::$object ) )
			self::create_object();

		$css = self::$object->get( $form_cache );
		return $css->post_content;
	}

	/**
	 * get_url function.
	 *
	 * @access public
	 * @return void
	 */
	public static function get_url(){
		if(self::$object)
		return self::$object->get_url();

	}

	/**
	 * post_revisions_meta_box function.
	 *
	 * @access public
	 * @param mixed $safecss_post
	 * @return void
	 */
	public static function revisions_meta_box( $post ) {

		$type = 'revision';

		// Remove numberposts from args if show_all_rev is specified
		if ( isset( $_GET['show_all_rev'] ) ) {
			$type = 'all';
		}

		if ( is_object( $post ) && isset( $post->ID ) ) {
			wp_list_post_revisions( $post->ID, $type );
		}
	}

	/**
	 * revision_time function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public static function revision_time( $data ) {

		$timestamp = preg_replace('/\D/', '', $data->post_excerpt);
		return date( 'j F, Y @ g:ia', $timestamp );
	}

	public static function settings_meta_box() {
		wp_nonce_field( 'custom_css_nonce', 'custom_css_nonce' );
		$include_css_in_editor = get_option( 'custom_css_include_in_editor' );
		?>
			<label for="include_in_editor">
				<input name="include_in_editor" type="checkbox" id="include_in_editor" value="1" <?php echo $include_css_in_editor ? 'checked' : '' ?>>
				Include CSS in the block editor
			</label>
		<?php
	}

	public static function save_settings( $post_ID, $post ) {
		$nonce_name   = isset( $_POST['custom_css_nonce'] ) ? $_POST['custom_css_nonce'] : '';
		$nonce_action = 'custom_css_nonce';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_ID ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_ID ) ) {
			return;
		}

		update_option( 'custom_css_include_in_editor', $_POST['include_in_editor'] === '1' );
	}


	/**
	 * update_files function.
	 *
	 * @access public
	 * @param mixed $data
	 * @return void
	 */
	public static function update_files( $data ) {

		self::$object->update_file( 'custom-css-full.css', $data->post_content );

		self::$object->update_file( $data->post_excerpt , self::minify_css( $data->post_content ) );

		return null;
		// return self::$object->get_url();

	}

	/**
	 * minify_css function.
	 *
	 * @access public
	 * @param mixed $css
	 * @return void
	 */
	public static function minify_css( $css ) {

		require( 'min/css_compressor.php');
		$options = array(
            'remove_bslash' => true,
            'compress_colors' => true,
            'compress_font-weight' => true,
            'lowercase_s' => false,
            'optimise_shorthands' => 1,
            'remove_last_;' => false,
            'case_properties' => 1,
            'sort_properties' => false,
            'sort_selectors' => false,
            'merge_selectors' => 2,
            'discard_invalid_properties' => false,
            'css_level' => 'CSS2.1',
            'preserve_css' => false,
            'timestamp' => false,
            'template' => 'default'
        );

		return Minify_CSS_Compressor::process( stripcslashes($css), $options);

	}



}

add_action( 'plugins_loaded', array( 'Improved_Simpler_CSS', 'init' ) );


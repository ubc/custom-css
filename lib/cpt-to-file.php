<?php




/**
 * CPT_to_file class.
 */
class CPT_to_file {
	
	public $post_type;
	public $single;
	public $prefix;
	public $file_extension;
	public $path_to;
	public $singular_data;
	public $file_name;
	public $file_name_filtered;
	public $error;
	
	
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @param mixed $post_type
	 * @param mixed $single
	 * @param mixed $prefix
	 * @param mixed $file_extension
	 * @return void
	 */
	function __construct( $post_type, $single, $prefix, $file_extension ) {
		
		$this->single 			= $single;
		$this->post_type 		= $post_type;
		$this->prefix 			= $prefix;
		$this->file_extension 	= $file_extension;
		
		$this->file_type = 	substr( $this->file_extension, 1 );
		$this->singular_data    = null;
		
		$wp_upload_dir = wp_upload_dir();
		$this->file_name 			= 'custom-'.$this->file_type.'-full'.$this->file_extension;
		
		$this->path_to 	= trailingslashit( $wp_upload_dir['basedir'] ) . 'custom-'.$this->file_type.'/';
		$this->url_to 	= trailingslashit( $wp_upload_dir['baseurl'] ) . 'custom-'.$this->file_type.'/';

	}
	
	/**
	 * register function.
	 * 
	 * @access public
	 * @return void
	 */
	function register() {
			
			$args = array(
			    
			    'public' => false,
			    'publicly_queryable' => false,
			    'show_ui' => false, 
			    'show_in_menu' => false, 
			    'query_var' => false,
			    'rewrite' => array( 'slug' => $this->post_type ),
			    'capability_type' => 'post',
			    'has_archive' => false, 
			    'hierarchical' => false,
			    'menu_position' => null,
			    'supports' => array( 'revisions' )
			  ); 
			
			register_post_type( (string)$this->post_type, $args );
			
	
	}
	
	/**
	 * get function.
	 * 
	 * @access public
	 * @return void
	 */
	function get( $from_cache = true ) {
		if( $this->singular_data && $from_cache )
			return $this->singular_data;
		
		$args = array(
			'numberposts'     => 1,
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'post_type'       => $this->post_type,
			'post_status'     => 'publish',
			'suppress_filters' => true );
		
		$this->singular_data = get_posts( $args );
		
		$this->singular_data = array_shift( $this->singular_data );
			
		
		
		// get the whole db object 
		// return ;
		return $this->singular_data;
	}
	
	/**
	 * get_url function.
	 * 
	 * @access public
	 * @return void
	 */
	function get_url() {
		// the url is stored in the excerpt fields
		$data = $this->get();
		
		if( $this->minified_file_exists() ) {
			return $this->url_to.$data->post_excerpt;
			
		} else if( isset( $data->post_excerpt ) && !empty( $data->post_excerpt ) && isset( $data->post_content ) ) {
			
			// some one deleted the file lest regenerate it again
			$this->update_file( $data->post_excerpt , $data->post_content );
			// if the file was generated return the path
			if( file_exists( $this->path_to.$data->post_excerpt ) )
				return $this->url_to.$data->post_excerpt;
		}
		
		return false;
	}
	
	/**
	 * minified_file_exists function.
	 * 
	 * @access public
	 * @return bool
	 */
	function minified_file_exists() {
		$data = $this->get();
		if( isset( $data->post_excerpt ) && !empty( $data->post_excerpt ) && file_exists($this->path_to.$data->post_excerpt) ) 
			return true;
		
		return false;
	}
	
	/**
	 * update function.
	 * 
	 * @access public
	 * @param mixed $content
	 * @return void
	 */
	function update( $content ) {
		
		// get the data
		$data = $this->get();
		if( is_object( $data ) ):
			$data = get_object_vars( $data );
		endif;
		// generate new files	
		$data = $this->save_to_external_file( $content, $data );
		
		// remove after done testing
		// $data['post_excerpt'] = null;
		
		if( !isset( $data['post_id'] ) ) {
				
			$data['post_content'] = $content;
			$data['post_title']   = 'Custom '.strtoupper( $this->file_type );
			$data['post_status']  = 'publish';
			$data['post_type']    = $this->post_type;
			
			$post_id = wp_insert_post( $data );
			
		} else {
			
			$data['post_content'] = $content; // really update the stuff
			$post_id = wp_update_post( $data );
			
		}
		return $post_id;
	}
	
	
	
	/**
	 * save_to_external_file function.
	 * 
	 * @access public
	 * @param mixed $content
	 * @return void
	 */
	function save_to_external_file( $content, $data ) {

		if( !wp_mkdir_p( $this->path_to ) ){
			$this->error[] = 'couldn\'t create filder '.$this->path_to;
			
			return 1; // we can't make the folder
		}
			

		if( empty( $content ) ):
			$this->unlink_files( true ); // delete all the files 
			$data['post_excerpt'] = null; // don't link to any files
			return $data;
		endif;
		
		do_action( 'CPT_to_file_save_to_file'.$this->post_type, $content );
		
		// lets minify the javascript to save first to solve timing issues
		$filtered_content = apply_filters( 'CPT_to_file_save_to_file_filter-'.$this->post_type, $content );
		$timestamp = time();
		
		if( $filtered_content ):
		
			$this->filterd_file_name 			= 'custom-'.$this->file_type.'-'.$timestamp.'.min'.$this->file_extension;
			
			$this->update_file( $this->filterd_file_name , $filtered_content );
		else:
			$this->error[] = 'Minified file wasn\'t created';
		endif;
		
		
		// update the regular file
		$this->update_file( $this->file_name ,  $content );
		
		$data['post_excerpt'] = ( empty( $this->filterd_file_name ) ? $this->file_name : $this->filterd_file_name );
		
		if( function_exists( 'wp_cache_clear_cache' ) ):
			wp_cache_clear_cache();
		endif;	
		// lets delete the old minified files
		$this->unlink_files();
		

		return $data;

	}
	
	/**
	 * update_file function.
	 * 
	 * @access public
	 * @param mixed $file_name
	 * @param mixed $content
	 * @return void
	 */
	function update_file( $file_name, $content ) {
		
		if(! file_put_contents( $this->path_to. $file_name ,  $content )){
		
			$this->error[] = 'couldn\'t create file '.$file_name;
		}
	
	}
	
	
	/**
	 * unlink_file function.
	 * delete file or all files
	 * @access public
	 * @param bool $all (default: false)
	 * @return void
	 */
	function unlink_files( $all = false ) {
			
		if( $directory_handle = opendir( $this->path_to ) ):
			
			if( $all ):
				$new_files = array( '.', '..' );
			else:
				$new_files = array( $this->filterd_file_name, $this->file_name, '.', '..' );
			endif;
			
			while( false !== ( $file_handle = readdir( $directory_handle ) ) ):
	

				if( !in_array(  $file_handle, $new_files ) ):
				
					unlink( $this->path_to . '/' .$file_handle );
				
				endif;
			
			endwhile;
		
			closedir( $directory_handle );
		endif;	
	}
	
	/**
	 * delete_all function.
	 * 
	 * @access public
	 * @return void
	 */
	function delete_all(){
		if($this->single):
			$data = $this->get();
			wp_delete_post( $data->ID, true );
		endif;
	}

}
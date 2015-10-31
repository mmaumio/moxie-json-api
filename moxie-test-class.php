<?php
/**
 * Moxie Test Plugin Class
 *
 * @Author: Muntasir Mahmud Aumio
 * @package: moxiejson
 */


if ( ! class_exists('Moxie_Test') ) {

	/**
	 * Test Class as Base class
	 *
	 * @since 1.0
	 * 
	 */
	class Moxie_Test {

		public function __construct() {
			add_action( 'init', array($this, 'movie_post_type') );
			add_action( 'init', array($this, 'movie_custom_taxonomy') );
			add_action( 'init', array($this, 'moxie_rewrite_rule') );
			add_action( 'plugins_loaded', array($this, 'moxie_load_textdomain') );
			register_activation_hook( __FILE__, 'moxie_plugin_flush_rule' );
			add_action( 'wp_enqueue_scripts', array($this, 'moxie_enqueue_styles') );
			add_action( 'add_meta_boxes', array($this, 'movie_add_meta_box') );
			add_action( 'save_post', array($this, 'movie_meta_save') );
			add_action( 'save_post', array($this, 'flush_cache_update') );
			add_action( 'template_redirect', array($this, 'moxie_movie_endpoint_data') );
			add_filter( 'the_content',  array($this, 'moxie_movie_output') );
		}

		/**
		* Enqueue plugin stylesheet
		*
		*/
		public function moxie_enqueue_styles() {
			wp_enqueue_style( 'main-css', plugins_url( 'main.css', __FILE__ ), array(), '1.0', 'all' );
			wp_enqueue_script('media-upload');
		    wp_enqueue_script('thickbox');
		    wp_register_script('my-upload', plugins_url('my-script.js', __FILE__ ), array('jquery','media-upload','thickbox') );
		    wp_enqueue_script('my-upload');
		}


		/**
		* Static Instance of the object
		*
		* @since 1.0
		*/
		public static function get_instance(){
            static $inst = null;
            if( $inst === null ) {
                $inst = new Moxie_Test();
            }
            return $inst;
        }


		/**
		 * Register the custom post type 'Movie'
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 */
		public function movie_post_type() {

			$labels = array(
				'name'                => _x( 'Movies', 'Post Type General Name', 'wpmoxie' ),
				'singular_name'       => _x( 'Movie', 'Post Type Singular Name', 'wpmoxie' ),
				'menu_name'           => __( 'Movie', 'wpmoxie' ),
				'name_admin_bar'      => __( 'Movie', 'wpmoxie' ),
				'parent_item_colon'   => __( 'Parent Item:', 'wpmoxie' ),
				'all_items'           => __( 'All Movies', 'wpmoxie' ),
				'add_new_item'        => __( 'Add New Movie', 'wpmoxie' ),
				'add_new'             => __( 'Add New', 'wpmoxie' ),
				'new_item'            => __( 'New Movie', 'wpmoxie' ),
				'edit_item'           => __( 'Edit Movie', 'wpmoxie' ),
				'update_item'         => __( 'Update Movie', 'wpmoxie' ),
				'view_item'           => __( 'View Movie', 'wpmoxie' ),
				'search_items'        => __( 'Search Movie', 'wpmoxie' ),
				'not_found'           => __( 'Not found', 'wpmoxie' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'wpmoxie' ),
			);
			$args = array(
				'label'               => __( 'Movie', 'wpmoxie' ),
				'description'         => __( 'Custom Movie List', 'wpmoxie' ),
				'labels'              => $labels,
		        'capability_type'    => 'post',
				'supports'            => array( 'title', 'editor', 'thumbnail', ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 5,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,		
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
			);
			register_post_type( 'movies', $args );

		}

		/**
		 * Register Custom taxonomy 'Tags'
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 */
		public function movie_custom_taxonomy() {

			$labels = array(
				'name'                       => _x( 'Tags', 'Taxonomy General Name', 'wpmoxie' ),
				'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'wpmoxie' ),
				'menu_name'                  => __( 'Tag', 'wpmoxie' ),
				'all_items'                  => __( 'Tags', 'wpmoxie' ),
				'parent_item'                => __( 'Parent Tag', 'wpmoxie' ),
				'parent_item_colon'          => __( 'Parent Tag:', 'wpmoxie' ),
				'new_item_name'              => __( 'New Tag Name', 'wpmoxie' ),
				'add_new_item'               => __( 'Add New Tag', 'wpmoxie' ),
				'edit_item'                  => __( 'Edit Tag', 'wpmoxie' ),
				'update_item'                => __( 'Update Tag', 'wpmoxie' ),
				'view_item'                  => __( 'View Tags', 'wpmoxie' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'wpmoxie' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'wpmoxie' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'wpmoxie' ),
				'popular_items'              => __( 'Popular Items', 'wpmoxie' ),
				'search_items'               => __( 'Search Items', 'wpmoxie' ),
				'not_found'                  => __( 'Not Found', 'wpmoxie' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => false,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
			);
			register_taxonomy( 'moxie_tag', array( 'movies' ), $args );

		}

		/**
		 * Register Custom meta datas for Movie post type
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 */
		public function movie_add_meta_box() {
			add_meta_box(
				'movie_information-movie-information',
				__( 'Movie Information', 'wpmoxie' ),
				array($this, 'movie_meta_html'),
				'movies',
				'normal',
				'default'
			);
		}

		/**
		 * Rendering html for the custom meta data of Movie post type
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 * @param	$post object
		 */
		public function movie_meta_html( $post) {
			wp_nonce_field( '_movie_information_nonce', 'movie_information_nonce' ); ?>

			<p>
				<label for="poster_url"><?php _e( 'Poster URL', 'wpmoxie' ); ?></label><br>
				<input type="text" name="poster_url" id="poster_url" class="widefat" value="<?php echo $this->moxie_get_meta( 'poster_url' ); ?>">
			</p>
			<p>
				<label for="rating"><?php _e( 'Rating', 'wpmoxie' ); ?></label><br>
				<select name="rating" id="rating">
					<option <?php echo ($this->moxie_get_meta( 'rating' ) === '1' ) ? 'selected' : '' ?>>1</option>
					<option <?php echo ($this->moxie_get_meta( 'rating' ) === '2' ) ? 'selected' : '' ?>>2</option>
					<option <?php echo ($this->moxie_get_meta( 'rating' ) === '3' ) ? 'selected' : '' ?>>3</option>
					<option <?php echo ($this->moxie_get_meta( 'rating' ) === '4' ) ? 'selected' : '' ?>>4</option>
					<option <?php echo ($this->moxie_get_meta( 'rating' ) === '5' ) ? 'selected' : '' ?>>5</option>
				</select>
			</p>	
			<p>
				<label for="year"><?php _e( 'Year', 'wpmoxie' ); ?></label><br>
				<input type="text" name="year" id="year" class="widefat" value="<?php echo $this->moxie_get_meta( 'year' ); ?>">
			</p>		
			<p>
				<label for="description"><?php _e( 'Description', 'wpmoxie' ); ?></label><br>
				<textarea name="description" id="description" class="widefat" row="20"><?php echo $this->moxie_get_meta( 'description' ); ?></textarea>
			</p><?php
		}

		/**
		 * Saving custom meta data of movie post
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 * @param	$post_id Int
		 */
		public function movie_meta_save( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
			if ( ! isset( $_POST['movie_information_nonce'] ) || ! wp_verify_nonce( $_POST['movie_information_nonce'], '_movie_information_nonce' ) ) return;
			if ( ! current_user_can( 'edit_post', $post_id ) ) return;

			if ( isset( $_POST['poster_url'] ) )
				update_post_meta( $post_id, 'poster_url', esc_attr( $_POST['poster_url'] ) );
			if ( isset( $_POST['rating'] ) )
				update_post_meta( $post_id, 'rating', esc_attr( $_POST['rating'] ) );
			if ( isset( $_POST['year'] ) )
				update_post_meta( $post_id, 'year', esc_attr( $_POST['year'] ) );
			if ( isset( $_POST['description'] ) )
				update_post_meta( $post_id, 'description', esc_attr( $_POST['description'] ) );
		}

		/**
		 * Custom function to pull Movie post type custom meta data
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 * @param	$value
		 */
		public function moxie_get_meta( $value ) {
			global $post;

			$field = get_post_meta( $post->ID, $value, true );
			if ( ! empty( $field ) ) {
				return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
			} else {
				return false;
			}
		}

		/**
		 * Adding Rewrite rule for json endpoint with WordPress's reWrite API
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 */
		public function moxie_rewrite_rule() {
		    add_rewrite_tag( '%movies%', '([^&]+)' );
		    add_rewrite_rule( 'movie-api/([^&]+)/?', 'index.php?movies=$matches[1]', 'top' );
		}

		/**
		 * Retrieving required value according to the json endpoint request from database
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 */
		public function moxie_movie_endpoint_data() {
		 
		    global $wp_query;
		 
		    $movie_tag = $wp_query->get( 'movies' );
		 
		    if ( ! $movie_tag ) {
		        return;
		    }

		    if ( $movie_tag == 'all' ) {
		    	$movie_tag = false;
		    }
		 
		    $movie_data = array();
		 	
		    if ( false === ( $movie_query = get_transient( 'movie_cache_query' ) ) ) :

			    $args = array(
			        'post_type'      => 'movies',
			        'posts_per_page' => 100,
			        'moxie_tag'    => esc_attr( $movie_tag ),
			    );

			    $movie_query = new WP_Query( $args );

			    set_transient( 'movie_cache_query', $movie_query, 60*60*12 );

		    endif;

		    if ( $movie_query->have_posts() ) : while ( $movie_query->have_posts() ) : $movie_query->the_post();
		        
		        $movie_data[] = array(
		        	'id'    => get_the_id(),
		            'title' => get_the_title(),
		            'poster_url' => esc_url( $this->moxie_get_meta('poster_url') ),
		            'rating' => $this->moxie_get_meta('rating'), 
		            'year' => $this->moxie_get_meta('year'),
		            'short_description' => $this->moxie_get_meta('description'),
		        );
		    endwhile; 
		    wp_reset_postdata(); 
		    endif;
		 	//header('Content-Type: application/json');
		    wp_send_json( $movie_data );
		 
		}

		/**
		* Flush cache when new Movie added
		*
		*/
		public function flush_cache_update($post_id){
		    global $post; 
		    if ($post->post_type != 'movies'){
		        return;
		    }
		    
		    delete_transient( 'movie_cache_query' );
		}

		/**
		 * Custom function to return the json data in php
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 * @param	$url string
		 */
		public function moxie_get_json( $url ) {

			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message();
			}
			
			$data = wp_remote_retrieve_body( $response );
			
			if ( ! is_wp_error( $data )  ) {
				$user_data = json_decode( $data, true );
			}

			if ( !is_array( $user_data ) ) {
			    return false;
			}

			return $user_data;
		}

		

		/**
		 * Output the movie data and hooking into the_content filter to appear in the front page
		 * @since 1.0.0
		 *
		 * @access public
		 * 
		 * @param	$content
		 */
		public function moxie_movie_output( $content ) {
	
			if (is_front_page()) {
				$data = $this->moxie_get_json('http://localhost:8888/devs/movie-api/all'); 
				$output = '<div class="row">';
				if ( !empty($data) ) {
					$i = 0;
					foreach ($data as $total ) {
						$output .= '<div class="col-md-12 movie-cpt">';
						$output .= '<h2>' . $total['title'] . '</h2>';
						$output .= '<p><img class="poster-img" src="' . $total['poster_url'] . '" /></p>';
						$output .= '<p><strong>Rating</strong>: ' . $total['rating'] . '</p>';
						$output .= '<p><strong>Year</strong>: ' . $total['year'] . '</p>';
						$output .= '<p><strong>Description</strong>: ' . $total['short_description'] . '</p>';
						$output .= '</div>';
					}
				} else {
					$output .= 'No Movie added. Please add new movies to populate content';
				}

				$output .= '</div>';
				$content = $output . '<br/>' . $content;
			}

			return $content;

		}

		/**
		* Flushing re write rules upon plugin activation
		*
		*/
		public function moxie_plugin_flush_rule() {
			movie_post_type();
			flush_rewrite_rules();
		}
		
		/**
		 * Load plugin textdomain.
		 *
		 * @since 1.0.0
		 */
		public function moxie_load_textdomain() {
		  load_plugin_textdomain( 'wpmoxie', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' ); 
		}

	}
}





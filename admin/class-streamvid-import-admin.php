<?php

defined( 'ABSPATH' ) || exit;

/**
 * Jws_Admin_Importers Class.
 */
class Jws_Admin_Importers {

	/**
	 * Array of importer IDs.
	 *
	 * @var string[]
	 */
	protected $importers = array();
    protected $parsing_raw_data_index = 0;
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->import_allowed() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_to_menus' ));
		add_action( 'admin_init', array( $this, 'register_importers' ) );
		add_action( 'admin_head', array( $this, 'hide_from_menus' ) );
        add_action( 'wp_ajax_jws_do_ajax_movie_import', array( $this, 'do_ajax_movie_import' ) );
	

		$this->importers['tmdb_importer'] = array(
			'menu'       => 'import.php',
			'name'       => __( 'TMDB Import', 'jws' ),
			'capability' => 'import',
			'callback'   => array( $this, 'tmdb_importer' ),
		);
	}

	/**
	 * Return true if Jws imports are allowed for current user, false otherwise.
	 *
	 * @return bool Whether current user can perform imports.
	 */
	protected function import_allowed() {
		return ( current_user_can( 'edit_tv_shows' ) || current_user_can( 'edit_videos' ) || current_user_can( 'edit_movies' ) || current_user_can( 'edit_persons' ) ) && current_user_can( 'import' );
	}

	/**
	 * Add menu items for our custom importers.
	 */
	public function add_to_menus() {
		foreach ( $this->importers as $id => $importer ) {
			add_submenu_page( $importer['menu'], $importer['name'], $importer['name'], $importer['capability'], $id, $importer['callback']);
		}
	}

	/**
	 * Hide menu items from view so the pages exist, but the menu items do not.
	 */
	public function hide_from_menus() {
		global $submenu;

		foreach ( $this->importers as $id => $importer ) {
			if ( isset( $submenu[ $importer['menu'] ] ) ) {
				foreach ( $submenu[ $importer['menu'] ] as $key => $menu ) {
					if ( $id === $menu[2] ) {
						unset( $submenu[ $importer['menu'] ][ $key ] );
					}
				}
			}
		}
	}

	/**
	 * Register importer scripts.
	 */
	public function admin_scripts() {
	   
    }

	/**
	 * Register WordPress based importers.
	 */
	public function register_importers() {
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
	           	register_importer( 'jws_tmdb_import', __( 'Jws Videos TMDB Import', 'jws' ), __( 'Import <strong>movies, tv shows, persons</strong> to your website via TMDB API.', 'jws' ), array( $this, 'tmdb_importer' ) );
		}
	}

	/**
	 * The tmdb importer.
	 *
	 * This has a custom screen - the Tools > Import item is a placeholder.
	 * If we're on that screen, redirect to the custom one.
	 */
	public function tmdb_importer() {
	   

		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=tmdb_importer' ) );
            exit;
		}
        
        ?>  
    
        <?php 

		include_once JWS_ABSPATH . 'admin/importers/class-jws-tmdb-importer-controller.php';

		$importer = new Jws_TMDB_Importer_Controller();
		$importer->dispatch();
	}
    
   /**
	 * Read file.
	*/
	public function read_file($file) {
	
            WP_Filesystem();
            
            global $wp_filesystem;
    
            $json = $wp_filesystem->get_contents($file);     
        
            // Decode the JSON file
            $json_data = json_decode($json,true);
              
            // Display data
            return $json_data;
   
	}

   /**
     * Return movie ID based on IMDB Id.
     *
     * @since 3.0.0
     * @param string $imdb_id Movie IMDB Id.
     * @return int
     */
    public function get_id_by_imdb_id( $imdb_id , $post_type ) {
        global $wpdb;

        // phpcs:ignore WordPress.VIP.DirectDatabaseQuery.DirectQuery
        $id = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT posts.ID
                FROM {$wpdb->posts} as posts
                INNER JOIN {$wpdb->postmeta} AS pmeta ON posts.ID = pmeta.post_id
                WHERE
                posts.post_type IN ( '$post_type' )
                AND posts.post_status != 'trash'
                AND pmeta.meta_key = 'videos_tmdb'
                AND pmeta.meta_value = %s
                LIMIT 1
                ",
                $imdb_id
            )
        );
        
      
        return (int) apply_filters( 'jws_get_movie_id_by_imdb_id', $id, $imdb_id );
    }
  
    public function create_image_media( $post_id , $image_url ) { 
        
        if(empty($image_url)) return '';
        
        $image_url = "https://image.tmdb.org/t/p/original/$image_url"; 
 
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
        
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        
          
            $response = wp_remote_get($image_url);
        
            if (!is_wp_error($response) && isset($response['body'])) {
                
                $upload = wp_upload_bits(basename($image_url), null, $response['body']);
                
                 $info  = wp_check_filetype( $upload['file'] );
          
                if (!is_wp_error($upload)) {
                  
                    $attachment_id = wp_insert_attachment(array(
                        'post_mime_type' => $info['type'],
                        'post_parent' => $post_id,
                        'post_title' => basename($image_url),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    ), $upload['file'], $post_id);
        
                    if (!is_wp_error($attachment_id)) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                        wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                    }
                }
            } 
        } else {
            $attachment_id = '';
        }
        
        
        return $attachment_id;
        
        
    }
    
    
    public function update_term_category($post_id , $post_type  , $data ) { 
        
        if($post_type == 'movies') {
            $taxonomy = 'movies_cat';
        }elseif($post_type == 'tv_shows') {
            $taxonomy = 'tv_shows_cat';
        }
  
        if(!empty($data)) {
            
            foreach($data as $term) {
                
                $subject_term = $term['name'];
                $my_subject_term = term_exists($subject_term, $taxonomy); 
               
                if (!$my_subject_term) {
                    $my_subject_term = wp_insert_term($subject_term, $taxonomy);
                }
                
                $term_arry[] = $my_subject_term['term_id'];
        
            }
            
            if(isset($term_arry) && !empty($term_arry)) {
                wp_set_post_terms( $post_id, $term_arry, $taxonomy );
            }

            
        }
        
    
    }
    
    
    
    public function update_meta_data( $id , $data ) { 
        
        update_post_meta( $id , 'videos_trailer_type' , 'url' ); 
        update_post_meta( $id , 'videos_type' , 'url' ); 
        update_post_meta( $id , 'videos_tmdb' , $data['id']  ); 
        
        if(isset($data['vote_average'])) {
            
            update_post_meta( $id , 'videos_vote' , $data['vote_average']  ); 
            
        }
        
        //update_field('is_affiliate', 1, $id);
    
        update_post_meta( $id , 'videos_language' , $data['original_language']  );
        
        $run_time = isset($data['runtime']) && !empty($data['runtime'])  ? $data['runtime'].' mins'  : '';
        
        update_post_meta( $id , 'videos_time' , $run_time );  
        
         
        
        //if(isset($data['homepage'])) {
            
           //update_post_meta( $id , 'videos_url' , $data['homepage'] );  
            
        //}
        
        if(isset($data['release_date'])) {
           list($year) = explode("-", $data['release_date']);
           update_post_meta( $id , 'videos_years' , $year ); 
        }
        
        if(isset($data['first_air_date'])) {
           list($year) = explode("-", $data['first_air_date']);
           update_post_meta( $id , 'videos_years' , $year ); 
        }
        
        $fearture_two = get_post_meta( $id , 'featured_image_two', true );
   
        if(isset($data['backdrop_path']) && !empty($data['backdrop_path']) && empty($fearture_two)) {
            
                $image_id = $this->create_image_media($id , $data['backdrop_path']);

                if(!empty($image_id)) {
                        
                 update_post_meta( $id , 'featured_image_two' , $image_id ); 
                                            
                }
   
        }
         
         
        if(isset($data['videos_type'])) {
            
            foreach($data['videos_type'] as $key => $videos_type) {
 
                if(isset($data['videos_type'][$key]['key'])) {
            
                  if($data['videos_type'][$key]['site'] == 'YouTube') {
                    
                     $video_url = 'https://www.youtube.com/watch?v='.$data['videos_type'][$key]['key'] .'';
                     
                  } elseif($data['videos_type'][$key]['site'] == 'Vimeo') {
                    
                    $video_url = 'https://vimeo.com/'.$data['videos_type'][$key]['key'] .'';
                    
                  } else {
                    $video_url = $data['videos_type'][$key]['key'];
                  }
        
                  if($key == 'featurette') {
                    update_post_meta( $id , 'videos_url' , $video_url );  
                  }
                  if($key == 'trailer') {
                     update_post_meta( $id , 'videos_trailer_url' , $video_url);
                  }
     
                   
                }
 
           }
            
        } 
         
        
        
        
        
  
        
    }
    
    public function import_episodes_meta_data( $post_id , $data ) {  
 
        
        update_post_meta( $post_id,'episodes_number',$data['episode_number']);
        update_post_meta( $post_id,'videos_tmdb',$data['id']);
        update_post_meta( $post_id,'videos_time',$data['runtime'].' mins' );
        
            
        
    }
    
    public function import_episodes( $post_id , $episodes , $seasons) {  
        
        $limit = 100000000000000;
        $status_type = isset($_POST['status_type']) ? $_POST['status_type'] : 'publish' ;
        if(  !empty($episodes) )  { 
            
            $episode_array = array();
             
            foreach($episodes as $key => $value) {
                
                if($key > $limit) continue;
                
                $episodes_imdb_id = $this->get_id_by_imdb_id($value['id'],'episodes');
                
                if($episodes_imdb_id) {
                   
                   $id = $episodes_imdb_id;
                    
                } else {
                    
                   $id = wp_insert_post(
                        apply_filters(
                            'jws_new_cast_data', array(
                                'post_type'      => 'episodes',
                                'post_status'    => $status_type,
                                'post_author'    => get_current_user_id(),
                                'post_title'     => isset($value['name']) ? $value['name'] : __( 'No Name', 'jws' ),
                                'post_content'   => isset($value['overview']) ? $value['overview'] : '',
                                'post_excerpt'   => '',
                            )
                        ), true
                    ); 
                    
                }
                
                $id = array(
                    'ID' => $id,
                    'post_status' => $status_type
                );
         
                $id = wp_update_post($id); 
                
                
                $this->import_episodes_meta_data($id ,$value);
                
                if ( !has_post_thumbnail( $id ) ) {
                    
                    $image_id = $this->create_image_media($id , $value['still_path']);
                    
                    if(!empty($image_id)) {
                        
                      set_post_thumbnail($id, $image_id);  
                                            
                    }
                        
                }
                
                $episode_array['season_'.$value['season_number']][] = $id;
                
           
            } 
            

        }
        
   
            
        if(isset($seasons) && !empty($seasons)) {

            foreach($seasons as $key => $value) { 
                
             $season[] =  array('episodes' => $episode_array['season_'.$value['season_number']]);   
    
             update_field('tv_shows_seasons', $season,  $post_id);
             
            }
    
        }

  
    }
    
    
    
    public function import_person_meta_data( $post_id , $data ) {  
        
        if($data['gender'] == '0') {
            $gender = 'female';
        }elseif($data['gender'] == '2') {
            $gender = 'male';
        }else {
            $gender = 'other';
        }
        
        update_post_meta( $post_id,'gender',$gender);
        update_post_meta( $post_id,'videos_tmdb',$data['id']);
        
    }
    
    public function import_person( $post_id , $data ) { 
        
        
         
        $data_cast = isset( $data['cast'] ) && !empty($data['cast']) ? $data['cast'] : array();
        $data_crew = isset( $data['crew'] ) && !empty($data['crew']) ? $data['crew'] : array();
        

        $persons = array_merge($data_cast, $data_crew);
        
        
        $person_count = 0;
        
        $limit = 10000000000;
        $status_type = isset($_POST['status_type']) ? $_POST['status_type'] : 'publish' ;
    
        if(  !empty($persons) )  { 
             
            foreach($persons as $key => $value) {
                
                if($key > $limit) continue;
                
                $cast_imdb_id = $this->get_id_by_imdb_id($value['id'],'person');
                
                if($cast_imdb_id) {
                   
                   $id = $cast_imdb_id;
                    
                } else {
                    
                   $id = wp_insert_post(
                        apply_filters(
                            'jws_new_cast_data', array(
                                'post_type'      => 'person',
                                'post_status'    => $status_type,
                                'post_author'    => get_current_user_id(),
                                'post_title'     => isset($value['name']) ? $value['name'] : __( 'No Name', 'jws' ),
                                'post_content'   => isset($value['biography']) ? $value['biography'] : '',
                                'post_excerpt'   => '',
                            )
                        ), true
                    ); 
                    
                }
                
                $id = array(
                    'ID' => $id,
                    'post_status' => $status_type
                );
         
                $id = wp_update_post($id); 
                
                
                $this->import_person_meta_data($id ,$value);
                
                if ( !has_post_thumbnail( $id ) ) {
                    
                    $image_id = $this->create_image_media($id , $value['profile_path']);
                    
                    if(!empty($image_id)) {
                        
                      set_post_thumbnail($id, $image_id);  
                                            
                    }
                        
                }
                
                
                
                if(isset($value['character'])) {
                    
                    
                    
                    $cast[] =  array('person' => $id , 'as' => $value['character']);
                    
                    
                } else {
                    
                    
                    $crew[] =  array('person' => $id , 'job' => $value['job']);
                    
                }

                if(isset($cast)) {
            
                   update_field( 'cast', $cast , $post_id );
                    
                } 
                
                if(isset($crew)) {
                    
                     update_field( 'crew', $crew , $post_id ); 
               
                }   
                
                $person_count++;
      
            } 
            

        }
        
        
        return $person_count;

     
    }
    
    public function do_ajax_movie_import() { 
        
        check_admin_referer( 'jws-csv-importer' );
 
		if ( ! $this->import_allowed() || ! isset( $_POST['file_url'] ) ) { // PHPCS: input var ok.
			wp_send_json_error( array( 'message' => __( 'Insufficient privileges to import movies.', 'jws' ) ) );
		}

		include_once JWS_ABSPATH . 'admin/importers/class-jws-tmdb-importer-controller.php';
		
        $importer  = new Jws_TMDB_Importer_Controller();
        
        $data_result = $this->read_file($_POST['file_url']);
  
        $count = 0;
        
        $status_type = isset($_POST['status_type']) ? $_POST['status_type'] : 'publish' ;
        
        if(isset($data_result[0])) {
            // $oke = $this->set_image_thumbnail($id , $data_result[0]['poster_path']);
            //wp_send_json_success($oke);     
          //wp_send_json_success($data_result);
           
          foreach($data_result as $result) {
                $status = 'create'; 
                $check_imdb_id = $this->get_id_by_imdb_id($result['id'],$_POST['type']);
               
                if($check_imdb_id) {
                   $status = 'update';
                }

                if($status == 'create') {
                    
                    if($_POST['type'] == 'movies') {
                        $title = isset($result['title']) ? $result['title'] : 'No Name';
                    } else {
                        $title = isset($result['name']) ? $result['name'] : 'No Name';
                    }
                    
                    $id = wp_insert_post(
                        apply_filters(
                            'jws_new_video_data', array(
                                'post_type'      => $_POST['type'],
                                'post_status'    => $status_type,
                                'post_author'    => get_current_user_id(),
                                'post_title'     => $title,
                                'post_content'   => isset($result['overview']) ? $result['overview'] : '',
                                'post_excerpt'   => '',
                            )
                        ), true
                    ); 
                    
                } else {
                    
                    $id = $check_imdb_id;
                    
                }    
               
                
                if($id) {
        
                    
                    $id = array(
                        'ID' => $id,
                        'post_status' => $status_type
                    );
             
                    $id = wp_update_post($id); 
                     

                    $this->update_meta_data($id , $result);
                    
                    if(isset($result['genres'])) {
                      $this->update_term_category($id , $_POST['type'] , $result['genres'] );  
                    }
                    
                    
                    
             
                    if ( !has_post_thumbnail( $id ) ) {
                    
                        $image_id = $this->create_image_media($id , $result['poster_path']);
                        
                        if(!empty($image_id)) {
                            
                          set_post_thumbnail($id, $image_id);  
                          
                        }
                            
                    }
                    
                    if((isset($_POST['cast']) && $_POST['cast'] == 'on') || (isset($_POST['crew']) && $_POST['crew'] == 'on') ) {
                      
                      if(!isset($_POST['cast']) && isset($result['credits']['cast'])) {
                         unset($result['credits']['cast']);
                      }
                      if(!isset($_POST['crew']) && isset($result['credits']['crew'])) {
                         unset($result['credits']['crew']);
                      }
                        
                      $person = $this->import_person($id , $result['credits']);  
                      
                      
                    }
                    
                    if(isset($_POST['episodes']) && $_POST['episodes'] == 'on') {
                      $person = $this->import_episodes($id , $result['episodes'] , $result['seasons']);  
                    }
                    
                    
                }
                
                
                $count++;  
          
                
            }
            
         
           $message = 'Imported All';
           wp_send_json_success(compact( 'count','message' ));
        }
    
        
    }


}

new Jws_Admin_Importers();
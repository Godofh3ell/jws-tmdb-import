<?php
/**
 * Class Jws_TMDB_Importer_Controller file.
 *
 * @package Jws\Admin\Importers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_Importer' ) ) {
    return;
}

/**
 * Movie importer controller - handles tmdb api in admin.
 *
 * @package     Jws/Admin/Importers
 * @version     1.0.0
 */
class Jws_TMDB_Importer_Controller {

    /**
     * API results.
     *
     * @var array
     */
    protected $results = array();

    /**
     * API results data for CSV.
     *
     * @var array
     */
    protected $results_csv_data_key = array();

    /**
     * API results data for CSV.
     *
     * @var array
     */
    protected $results_csv_data = array();

    /**
     * API results data count.
     *
     * @var int
     */
    protected $results_csv_data_count = 0;
    
    protected $results_csv_data_episodes_count = 0;
    
    protected $results_csv_data_person_count = 0;
    
    protected $results_csv_data_crew_count = 0;

    /**
     * API results.
     *
     * @var array
     */
    protected $file = '';


    /**
     * Importer type.
     *
     * @var array
     */
    protected $type = '';

    /**
     * The current import step.
     *
     * @var string
     */
    protected $step = '';

    /**
     * Progress steps.
     *
     * @var array
     */
    protected $steps = array();

    /**
     * Errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor.
     */
    public function __construct() {

        $default_steps = array(
            'fetch'  => array(
                'name'    => __( 'Fetch TMDB API', 'jws' ),
                'view'    => array( $this, 'fetch_form' ),
                'handler' => array( $this, 'fetch_form_handler' ),
            ),
            'results'  => array(
                'name'    => __( 'Import', 'jws' ),
                'view'    => array( $this, 'import_form' ),
                'handler' => ''
            ),
            
        );

        $this->steps = apply_filters( 'jws_tmdb_importer_steps', $default_steps );

        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification
        $this->step             = isset( $_REQUEST['step'] ) ? sanitize_key( $_REQUEST['step'] ) : current( array_keys( $this->steps ) );
        $this->file             = isset( $_REQUEST['file'] ) ? jws_clean( wp_unslash( $_REQUEST['file'] ) ) : '';
        $this->type             = ! empty( $_REQUEST['type'] ) ? jws_clean( wp_unslash( $_REQUEST['type'] ) ) : '';
        $this->results_csv_data_count = ! empty( $_REQUEST['result_count'] ) ? jws_clean( wp_unslash( $_REQUEST['result_count'] ) ) : '';
        // $this->results_csv_data = ! empty( $_REQUEST['results_csv_data'] ) ? jws_clean( wp_unslash( $_REQUEST['results_csv_data'] ) ) : array();
        
        $this->results_csv_data_episodes_count = ! empty( $_REQUEST['episodes_result_count'] ) ? jws_clean( wp_unslash( $_REQUEST['episodes_result_count'] ) ) : 0;
        $this->results_csv_data_person_count = ! empty( $_REQUEST['person_result_count'] ) ? jws_clean( wp_unslash( $_REQUEST['person_result_count'] ) ) : 0;
        $this->results_csv_data_crew_count = ! empty( $_REQUEST['crew_result_count'] ) ? jws_clean( wp_unslash( $_REQUEST['crew_result_count'] ) ) : 0;
        
        
        
        
    }

    /**
     * Get the URL for the next step's screen.
     *
     * @param string $step  slug (default: current step).
     * @return string       URL for next step if a next step exists.
     *                      Admin URL if it's the last step.
     *                      Empty string on failure.
     */
    public function get_next_step_link( $step = '' ) {

        if ( ! $step ) {
            $step = $this->step;
        }

        $keys = array_keys( $this->steps );

        if ( end( $keys ) === $step ) {
            return admin_url();
        }

        $step_index = array_search( $step, $keys, true );

        if ( false === $step_index ) {
            return '';
        }

        $params = array(
            'step'            => $keys[ $step_index + 1 ],
            'file'            => str_replace( DIRECTORY_SEPARATOR, '/', $this->file ),
            'type'            => $this->type,
            'result_count'    => $this->results_csv_data_count,
            'episodes_result_count' => $this->results_csv_data_episodes_count,
            'person_result_count' => $this->results_csv_data_person_count,
            'crew_result_count' => $this->results_csv_data_crew_count,
            '_wpnonce'        => wp_create_nonce( 'jws-tmdb-fetch-data' ), // wp_nonce_url() escapes & to &amp; breaking redirects.
        );

        return add_query_arg( $params );
    }

    /**
     * Dispatch the output of api page.
     */
    public function dispatch() {        
        // phpcs:ignore WordPress.CSRF.NonceVerification.NoNonceVerification
        if ( ! empty( $_POST['save_step'] ) && ! empty( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'], $this );
        }
        // $this->output_header();
        // $this->output_steps();
        // $this->output_errors();
        call_user_func( $this->steps[ $this->step ]['view'], $this );
        // $this->output_footer();
    }

    /**
     * Output information about the uploading process.
     */
    protected function fetch_form() {
        $type_options = apply_filters( 'jws_tmdb_importer_type_options', array(
            'search-movie'          => __( 'Search Movie', 'jws' ),
            'movie-by-id'           => __( 'Movie By ID', 'jws' ),
            'latest-movie'          => __( 'Latest Movie', 'jws' ),
            'now-playing-movies'    => __( 'Now Playing Movies', 'jws' ),
            'upcoming-movies'       => __( 'Upcoming Movies', 'jws' ),
            'popular-movies'        => __( 'Popular Movies', 'jws' ),
            'top-rated-movies'      => __( 'Top Rated Movies', 'jws' ),
            'discover-movies'       => __( 'Discover Movies', 'jws' ),
            'search-tv-show'        => __( 'Search TV Show', 'jws' ),
            'tv-show-by-id'         => __( 'TV Show By ID', 'jws' ),
            'latest-tv-show'        => __( 'Latest TV Show', 'jws' ),
            'on-air-tv-shows'       => __( 'ON Air TV Shows', 'jws' ),
            'on-air-today-tv-shows' => __( 'ON Air Today TV Shows', 'jws' ),
            'popular-tv-shows'      => __( 'Popular TV Shows', 'jws' ),
            'top-rated-tv-shows'    => __( 'Top Rated TV Shows', 'jws' ),
            'discover-tv-shows'     => __( 'Discover TV Shows', 'jws' ),
        ) );
        include dirname( __FILE__ ) . '/views/html-tmdb-import-fetch-form.php';
    }

    /**
     * Handle the upload form and store options.
     */
    public function fetch_form_handler() {
        check_admin_referer( 'jws-tmdb-fetch-data' );

        // phpcs:disable WordPress.CSRF.NonceVerification.NoNonceVerification -- Nonce already verified in Jws_Movie_CSV_Importer_Controller::upload_form_handler()
        $api_key = jws_theme_get_option('tmdb_api');
        $language = isset( $_POST['jws-tmdb-language'] ) ? jws_clean( wp_unslash(  str_replace("_", "-", $_POST['jws-tmdb-language'])) ) : 'en';
        $type = isset( $_POST['jws-tmdb-type'] ) ? jws_clean( wp_unslash( $_POST['jws-tmdb-type'] ) ) : '';
        $page = isset( $_POST['jws-tmdb-page-number'] ) ? jws_clean( wp_unslash( $_POST['jws-tmdb-page-number'] ) ) : 1;
        $tmdb_id = isset( $_POST['jws-tmdb-id'] ) ? jws_clean( wp_unslash( $_POST['jws-tmdb-id'] ) ) : 1;
        $keyword = isset( $_POST['jws-tmdb-search-keyword'] ) ? jws_clean( wp_unslash( $_POST['jws-tmdb-search-keyword'] ) ) : '';

        if ( empty( $api_key ) || empty( $type ) ) {
            return;
        }

        include_once JWS_ABSPATH . 'integrations/class-streamvid-tmdb.php';

        // Configuration
        $cnf = array(
            'apikey'    => $api_key,
            'lang'      => $language,
            'timezone'  => 'Europe/London',
            'adult'     => false,
            'debug'     => false
        );

        // Data Return Configuration - Manipulate if you want to tune your results
        $cnf['appender'] = array(
            'movie'         => array( 'account_states', 'credits','keywords', 'release_dates','rating' ),
            'tvshow'        => array( 'account_states', 'content_rating', 'external_ids','keywords', 'rating' , 'aggregate_credits'),
            'season'        => array( 'changes', 'account_states', 'credits', 'external_ids'),
            'episode'       => array( 'changes', 'account_states', 'credits', 'external_ids', 'rating'),
            'person'        => array( 'movie_credits', 'tv_credits', 'combined_credits', 'external_ids', 'tagged_images', 'changes' ),
            'collection'    => array( 'images' ),
            'company'       => array( 'movies' ),
        );

        $tmdb = new Streamvid_TMDB( $cnf );

        switch ( $type ) {
            case 'now-playing-movies':
                $this->type = 'movies';
                $this->results = $tmdb->getNowPlayingMovies( $page );
                $this->results_csv_data_count = count( $this->results );
                
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
         
                break;

            case 'upcoming-movies':
                $this->type = 'movies';
                $this->results = $tmdb->getUpcomingMovies( $page );
                $this->results_csv_data_count = count( $this->results );
                
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
                break;

            case 'popular-movies':
                $this->type = 'movies';
                $this->results = $tmdb->getPopularMovies( $page );
                $this->results_csv_data_count = count( $this->results );
               
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
                break;

            case 'top-rated-movies':
                $this->type = 'movies';
                $this->results = $tmdb->getTopRatedMovies( $page );
                $this->results_csv_data_count = count( $this->results );
             
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
                break;

            case 'discover-movies':
                $this->type = 'movies';
                $this->results = $tmdb->getDiscoverMovies( $page );
                $this->results_csv_data_count = count( $this->results );
              
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
                break;

            case 'latest-movie':
                $this->type = 'movies';
                $this->results = $tmdb->getLatestMovie();
         
                $this->results_csv_data_count = 1;
                 
                $movies = array();
             
                $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $this->results['id'] )) ;
                $movies[] = $movie;
              
               
                $this->results_csv_data = $movies;
                break;

            case 'movie-by-id':
                $this->type = 'movies';
                $this->results =  $tmdb->getMovie( $tmdb_id );
                $this->results_csv_data_count = 1;
                
                $movies = array();
             
                $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $this->results['id'] )) ;
                $movies[] = $movie;
              
               
                $this->results_csv_data = $movies;
                break;

            case 'search-movie':
                $this->type = 'movies';
                $this->results = $tmdb->searchMovie( $keyword );
                $this->results_csv_data_count = count( $this->results );
                
                $movies = array();
                foreach ( $this->results as $key => $movie ) {
                    $movie = $this->format_movies($tmdb ,$tmdb->getMovie( $movie['id'] )) ;
                    $movies[] = $movie;
              
                }
                $this->results_csv_data = $movies;
                break;

            case 'on-air-tv-shows':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getOnTheAirTVShows( $page );
                $this->results_csv_data_count = count( $this->results );
         
                $tv_shows = array();
        
                foreach ( $this->results as $key => $tv_show ) {
                    if ( ! empty( $tv_show ) ) {
                        $tv_show = $this->format_tv_show( $tmdb, $tmdb->getTVShow( $tv_show['id'] ) );
                      

                        $tv_shows[] = $tv_show;

                
                    }
                }
        
   
                $this->results_csv_data = $tv_shows;
                break;

            case 'on-air-today-tv-shows':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getAiringTodayTVShows( $page );
                $this->results_csv_data_count = count( $this->results );
           
                $tv_shows = array();
        
                foreach ( $this->results as $key => $tv_show ) {
                    if ( ! empty( $tv_show ) ) {
                        $tv_show = $this->format_tv_show( $tmdb, $tmdb->getTVShow( $tv_show['id'] ) );
                      

                        $tv_shows[] = $tv_show;

                
                    }
                }
        
   
                $this->results_csv_data = $tv_shows;
                break;

            case 'popular-tv-shows':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getAiringTodayTVShows( $page );
                $this->results_csv_data_count = count( $this->results );
             
               $tv_shows = array();
        
                foreach ( $this->results as $key => $tv_show ) {
                    if ( ! empty( $tv_show ) ) {
                        $tv_show = $this->format_tv_show( $tmdb, $tmdb->getTVShow( $tv_show['id'] ) );
                      

                        $tv_shows[] = $tv_show;

                
                    }
                }

   
                $this->results_csv_data = $tv_shows;
                break;

            case 'top-rated-tv-shows':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getTopRatedTVShows( $page );
                $this->results_csv_data_count = count( $this->results );
      
                $tv_shows = array();
        
                foreach ( $this->results as $key => $tv_show ) {
                    if ( ! empty( $tv_show ) ) {
                        $tv_show = $this->format_tv_show( $tmdb, $tmdb->getTVShow( $tv_show['id'] ) );
                      

                        $tv_shows[] = $tv_show;

                
                    }
                }
                
                
   
                $this->results_csv_data = $tv_shows;
                break;

            case 'discover-tv-shows':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getDiscoverTVShows( $page );
                $this->results_csv_data_count = count( $this->results );
              
                $tv_shows = array();
        
                foreach ( $this->results as $key => $tv_show ) {
                    if ( ! empty( $tv_show ) ) {
                        $tv_show = $this->format_tv_show( $tmdb, $tmdb->getTVShow( $tv_show['id'] ) );
                      

                        $tv_shows[] = $tv_show;
      

                
                    }
                }
        
   
                $this->results_csv_data = $tv_shows;
                break;

            case 'latest-tv-show':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getLatestTVShow();
                $this->results_csv_data_count = 1;
                
                $this->results = $this->format_tv_show($tmdb,$this->results);
                
                $this->results_csv_data = $this->results;
                break;

            case 'tv-show-by-id':
                $this->type = 'tv_shows';
                $this->results = $tmdb->getTVShow( $tmdb_id );
                $this->results_csv_data_count = 1;
                
                
                $this->results = $this->format_tv_show($tmdb,$this->results);
                
                
                $this->results_csv_data = $this->results;
                break;

            case 'search-tv-show':
                $this->type = 'tv_shows';
                $this->results = $tmdb->searchTVShow( $keyword );
                $this->results_csv_data_count = count( $this->results );
                $this->results = $this->format_tv_show($tmdb,$this->results);
                $this->results_csv_data = $this->results ;
                break;

            default:
                break;
        }

   
       
       if( is_array($this->results_csv_data) &&  !isset($this->results_csv_data[0])) {
           $this->results_csv_data = array($this->results_csv_data);
       }
       
       
     
      
       
       //echo '<pre>' . print_r(  $this->results_csv_data  , 1 ) . '</pre>';
       //exit;

        if ( empty( $this->results_csv_data ) ) {
            return;
        }
        
         $file = $this->handle_upload();


        if ( is_wp_error( $file ) ) {
             $this->add_error( $file->get_error_message() );
            return;
        } else {
            $this->file = $file;
        }
       

       wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
       exit;
    }

      protected function format_tv_show($tmdb , $data_array) {
        
        
          $video_data = $tmdb->getVideoTvhows( $data_array['id'] );
              
             if(isset($video_data) && is_array($video_data)) {
                
               foreach($video_data as $video) {
                
                if($video['type'] == 'Trailer' && !isset($video_type['trailer'])) {
                   $video_type['trailer'] = $video; 
                }
                if($video['type'] == 'Featurette' && !isset($video_type['featurette'])) {
                   $video_type['featurette'] = $video; 
                }
                
               }
               
               $data_array['videos_type'] = $video_type;
                
           }
        
         
        
         if(isset($data_array['seasons'])) {
        
           if(isset($data_array['episodes'])) {
               unset($data_array['episodes']);
           }
           
           foreach( $data_array['seasons'] as $season ) {
            $season_data = $tmdb->getSeason( $data_array['id'], $season['season_number'] );
            
            if( ! empty( $season_data['episodes'] ) ) {
            
                foreach ( $season_data['episodes'] as $key => $episode ) {
                    
                    unset($episode['guest_stars']);
                    unset($episode['crew']);
                   
                      $episodes[] = $episode;
                    
                    
                   
              
                }
            }
          
        }
        
        
        $data_array['episodes'] = $episodes;
       
        $this->results_csv_data_episodes_count += count($data_array['episodes']);
        
       }
       
       $person = 0; 
       $crew = 0;
       
       
       
       
     
       
       
          
       if(isset($data_array['aggregate_credits'])) {
            
             if( isset( $data_array['aggregate_credits']['cast'] ) && !empty($data_array['aggregate_credits']['cast']) )  { 
                
                $person += count($data_array['aggregate_credits']['cast']);
                
                $this->results_csv_data_person_count += $person; 
                
             }
             
             if( isset( $data_array['aggregate_credits']['crew'] ) &&  !empty($data_array['aggregate_credits']['crew']) )  { 
                
                $crew += count($data_array['aggregate_credits']['crew']);
                
                $this->results_csv_data_crew_count += $crew; 
                
             }
    
    
      }
      
       if(isset($data_array['aggregate_credits'])) {
             
            $data_array['credits'] = array('cast' => array() , 'crew' => array());
        
            foreach($data_array['aggregate_credits']  as $role => $credits) {
              
                     if(isset($data_array['aggregate_credits'][$role])) {
                        
                        foreach($credits as $key => $credit) {
                            
                                $data_array['credits'][$role][$key] = $credit;
                                
                                if(isset($credit['roles'][0]['character'])) { 
                                   
                                   $data_array['credits'][$role][$key]['character'] = $credit['roles'][0]['character'];
                                    
                                } elseif(isset($credit['jobs'][0]['job'])) {
                            
                                   $data_array['credits'][$role][$key]['job'] = $credit['jobs'][0]['job'];
                                   
                                }
                                
                        }
                      
                     }
      
            } 
            
            unset($data_array['aggregate_credits']);
             
       }
         
     
       
       return $data_array;
        
      }   
      
      
       protected function format_movies($tmdb , $data_array) {  
        
         
          
             $video_data = $tmdb->getVideoMovies( $data_array['id'] );
              
             if(isset($video_data) && is_array($video_data)) {
                
               foreach($video_data as $video) {
                
                if($video['type'] == 'Trailer' && !isset($video_type['trailer'])) {
                   $video_type['trailer'] = $video; 
                }
                if($video['type'] == 'Featurette' && !isset($video_type['featurette'])) {
                   $video_type['featurette'] = $video; 
                }
                
               }
               
               $data_array['videos_type'] = $video_type;
                
             }
        
            $person = 0; 
            $crew = 0;
                 
            
             if(isset($data_array['credits'])) {
                
                 if( isset( $data_array['credits']['cast'] ) && !empty($data_array['credits']['cast']) )  { 
                    
                    $person += count($data_array['credits']['cast']);
                    $this->results_csv_data_person_count += $person; 
                    
                 }
                 
                 if( isset( $data_array['credits']['crew'] ) &&  !empty($data_array['credits']['crew']) )  { 
                    
                    $crew += count($data_array['credits']['crew']);
                    $this->results_csv_data_crew_count += $crew; 
                    
                 }
         
                

              }
          
            
         
            return $data_array;     
       }
       
    
    /**
     * Store results in CSV file.
     */
    protected function handle_upload() {
        $upload_dir = wp_upload_dir( null, false );

        $file_name = 'jws-tmdb-csv-output' . date('U') . '.json';
        $file = $upload_dir['path'] . '/' . $file_name;
        
        WP_Filesystem();
        global $wp_filesystem;

        $wp_filesystem->put_contents($file, json_encode($this->results_csv_data));

        // Construct the object array.
        $object = array(
            'post_title'     => basename( $file ),
            'post_content'   => $upload_dir['url'] . '/' . $file_name,
            'guid'           => $upload_dir['url'] . '/' . $file_name,
            'context'        => 'import',
            'post_status'    => 'private',
        );

        // Save the data.
        $id = wp_insert_attachment( $object, $file );

        /*
         * Schedule a cleanup for one day from now in case of failed
         * import or missing wp_import_cleanup() call.
         */
        wp_schedule_single_event( time() + DAY_IN_SECONDS, 'importer_scheduled_cleanup', array( $id ) );

        return $upload_dir['url'] . '/' . $file_name; 
    }
    
    /**
	 * Remove UTF-8 BOM signature.
	 *
	 * @param  string $string String to handle.
	 * @return string
	 */
	protected function remove_utf8_bom( $string ) {
		if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
			$string = substr( $string, 3 );
		}

		return $string;
	}

     

    /**
     * Import the results.
     */
    protected function import_form() {
        $action = admin_url( 'admin.php?page=tmdb_importer&step=results&file='.str_replace( DIRECTORY_SEPARATOR, '/', $this->file ).'&post_type=' . $this->type );
        $file_url = $this->file ;
        include dirname( __FILE__ ) . '/views/html-tmdb-import-form.php';
   }

   
   
}

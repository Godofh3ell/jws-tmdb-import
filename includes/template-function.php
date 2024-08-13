<?php

function jws_clean( $var ) {
    if ( is_array( $var ) ) {
        return array_map( 'jws_clean', $var );
    } else {
        return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
    }
}

function is_existing_tmdb_id($posttype, $tmdb_id , $movie_id) {
        global $wpdb;

        // phpcs:ignore WordPress.VIP.DirectDatabaseQuery.DirectQuery
        return $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT posts.ID
                FROM {$wpdb->posts} as posts
                INNER JOIN {$wpdb->postmeta} AS pmeta ON posts.ID = pmeta.post_id
                WHERE
                posts.post_type IN ( %1$s )
                AND posts.post_status != 'trash'
                AND pmeta.meta_key = '_tmdb_id'
                AND pmeta.meta_value = %2$s
                AND pmeta.post_id <> %3$s
                LIMIT 1
                ",
                $posttype,
                wp_slash( $tmdb_id ),
                $movie_id
                
            )
        );
    }


function tv_shows_mappinng() { 
    
          // Get index for special column names.
        $index = $item;

        if ( preg_match( '/\d+$/', $item, $matches ) ) {
            $index = $matches[0];
        }

        // Properly format for meta field.
        $meta = str_replace( 'meta:', '', $item );

        // Available options.
        $weight_unit    = get_option( 'jws_weight_unit' );
        $dimension_unit = get_option( 'jws_dimension_unit' );
        $options        = array(
            'id'                     => __( 'ID', 'jws' ),
            'type'                   => __( 'Type', 'jws' ),
            'parent_tv_show'         => __( 'Parent TV Show', 'jws' ),
            'parent_season'          => __( 'Parent Season', 'jws' ),
            'name'                   => __( 'Name', 'jws' ),
            'published'              => __( 'Published', 'jws' ),
            'featured'               => __( 'Is featured?', 'jws' ),
            'catalog_visibility'     => __( 'Visibility in catalog', 'jws' ),
            'short_description'      => __( 'Short description', 'jws' ),
            'description'            => __( 'Description', 'jws' ),
            'genre_ids'              => __( 'Genres', 'jws' ),
            'tag_ids'                => __( 'Tags', 'jws' ),
            'images'                 => __( 'Images', 'jws' ),
            'episode_number'         => __( 'Episode Number', 'jws' ),
            'episode_choice'         => __( 'Episode Choice', 'jws' ),
            'episode_attachment_id'  => __( 'Episode Attachment', 'jws' ),
            'episode_embed_content'  => __( 'Episode Embed Content', 'jws' ),
            'episode_url_link'       => __( 'Episode Link', 'jws' ),
            'episode_release_date'   => __( 'Episode Release Date', 'jws' ),
            'episode_run_time'       => __( 'Episode Run Time', 'jws' ),
            'imdb_id'                => __( 'IMDB ID', 'jws' ),
            'tmdb_id'                => __( 'TMDB ID', 'jws' ),
            'seasons'                => array(
                'name'    => __( 'Seasons', 'jws' ),
                'options' => array(
                    'seasons:name' . $index         => __( 'Season name', 'jws' ),
                    'seasons:image_id' . $index     => __( 'Season image', 'jws' ),
                    'seasons:episodes' . $index     => __( 'Season episode(s)', 'jws' ),
                    'seasons:year' . $index         => __( 'Season year', 'jws' ),
                    'seasons:description' . $index  => __( 'Season description', 'jws' ),
                    'seasons:position' . $index     => __( 'Season position', 'jws' ),
                ),
            ),
            'cast'                   => array(
                'name'    => __( 'Cast', 'jws' ),
                'options' => array(
                    'cast:id' . $index                 => __( 'Cast Person ID', 'jws' ),
                    'cast:imdb_id' . $index            => __( 'Cast Person IMDB ID', 'jws' ),
                    'cast:tmdb_id' . $index            => __( 'Cast Person TMDB ID', 'jws' ),
                    'cast:name' . $index               => __( 'Cast Person Name', 'jws' ),
                    'cast:images' . $index             => __( 'Cast Person Images', 'jws' ),
                    'cast:category' . $index           => __( 'Cast Person Category', 'jws' ),
                    'cast:character' . $index          => __( 'Cast Person Character', 'jws' ),
                    'cast:position' . $index           => __( 'Cast Position', 'jws' ),
                ),
            ),
            'crew'                   => array(
                'name'    => __( 'Crew', 'jws' ),
                'options' => array(
                    'crew:id' . $index                 => __( 'Crew Person ID', 'jws' ),
                    'crew:imdb_id' . $index            => __( 'Crew Person IMDB ID', 'jws' ),
                    'crew:tmdb_id' . $index            => __( 'Crew Person TMDB ID', 'jws' ),
                    'crew:name' . $index               => __( 'Crew Person Name', 'jws' ),
                    'crew:images' . $index             => __( 'Crew Person Images', 'jws' ),
                    'crew:category' . $index           => __( 'Crew Person Category', 'jws' ),
                    'crew:job' . $index                => __( 'Crew Person Job', 'jws' ),
                    'crew:position' . $index           => __( 'Crew Position', 'jws' ),
                ),
            ),
            'attributes'             => array(
                'name'    => __( 'Attributes', 'jws' ),
                'options' => array(
                    'attributes:name' . $index         => __( 'Attribute name', 'jws' ),
                    'attributes:value' . $index        => __( 'Attribute value(s)', 'jws' ),
                    'attributes:taxonomy' . $index     => __( 'Is a global attribute?', 'jws' ),
                    'attributes:visible' . $index      => __( 'Attribute visibility', 'jws' ),
                    'attributes:default' . $index      => __( 'Default attribute', 'jws' ),
                ),
            ),
            'sources'                => array(
                'name'    => __( 'Sources', 'jws' ),
                'options' => array(
                    'sources:name' . $index            => __( 'Source Name', 'jws' ),
                    'sources:choice' . $index          => __( 'Source Choice', 'jws' ),
                    'sources:embed_content' . $index   => __( 'Source Embed Content', 'jws' ),
                    'sources:link' . $index            => __( 'Source Link', 'jws' ),
                    'sources:quality' . $index         => __( 'Source Quality', 'jws' ),
                    'sources:language' . $index        => __( 'Source Language', 'jws' ),
                    'sources:player' . $index          => __( 'Source Player', 'jws' ),
                    'sources:date_added' . $index      => __( 'Source Date Added', 'jws' ),
                    'sources:position' . $index        => __( 'Source Position', 'jws' ),
                ),
            ),
            'reviews_allowed'        => __( 'Allow customer reviews?', 'jws' ),
            'meta:' . $meta          => __( 'Import as meta', 'jws' ),
            'menu_order'             => __( 'Position', 'jws' ),
        );

        return apply_filters( 'jws_csv_tv_show_import_mapping_options', $options, $item );
    
}


function movies_mappinng() {
    
    
// Get index for special column names.
		$index = $item;

		if ( preg_match( '/\d+$/', $item, $matches ) ) {
			$index = $matches[0];
		}

		// Properly format for meta field.
		$meta = str_replace( 'meta:', '', $item );

	
		$options        = array(
			'id'                     => __( 'ID', 'jws' ),
			'title'                   => __( 'Name', 'jws' ),
			'published'              => __( 'Published', 'jws' ),
			'featured'               => __( 'Is featured?', 'jws' ),
			'catalog_visibility'     => __( 'Visibility in catalog', 'jws' ),
			'short_description'      => __( 'Short description', 'jws' ),
			'description'            => __( 'Description', 'jws' ),
			'genre_ids'              => __( 'Genres', 'jws' ),
			'tag_ids'                => __( 'Tags', 'jws' ),
			'images'                 => __( 'Images', 'jws' ),
			'movie_choice'           => __( 'Movie Choice', 'jws' ),
			'movie_attachment_id'    => __( 'Movie Attachment', 'jws' ),
			'movie_embed_content'    => __( 'Movie Embed Content', 'jws' ),
			'movie_url_link'         => __( 'Movie Link', 'jws' ),
			'movie_is_affiliate_link'=> __( 'Is Affiliate URL ?', 'jws' ),
			'movie_release_date'     => __( 'Movie Release Date', 'jws' ),
			'movie_run_time'         => __( 'Movie Run Time', 'jws' ),
			'movie_censor_rating'    => __( 'Movie Censor Rating', 'jws' ),
			'recommended_movie_ids'  => __( 'Recommended Movies', 'jws' ),
			'related_video_ids'      => __( 'Related Video', 'jws' ),
			'imdb_id'                => __( 'IMDB ID', 'jws' ),
			'tmdb_id'                => __( 'TMDB ID', 'jws' ),
			'cast'                   => array(
				'name'    => __( 'Cast', 'jws' ),
				'options' => array(
					'cast:id' . $index                 => __( 'Cast Person ID', 'jws' ),
					'cast:imdb_id' . $index            => __( 'Cast Person IMDB ID', 'jws' ),
					'cast:tmdb_id' . $index            => __( 'Cast Person TMDB ID', 'jws' ),
					'cast:name' . $index               => __( 'Cast Person Name', 'jws' ),
					'cast:images' . $index             => __( 'Cast Person Images', 'jws' ),
					'cast:category' . $index           => __( 'Cast Person Category', 'jws' ),
					'cast:character' . $index          => __( 'Cast Person Character', 'jws' ),
					'cast:position' . $index           => __( 'Cast Position', 'jws' ),
				),
			),
			'crew'                   => array(
				'name'    => __( 'Crew', 'jws' ),
				'options' => array(
					'crew:id' . $index                 => __( 'Crew Person ID', 'jws' ),
					'crew:imdb_id' . $index            => __( 'Crew Person IMDB ID', 'jws' ),
					'crew:tmdb_id' . $index            => __( 'Crew Person TMDB ID', 'jws' ),
					'crew:name' . $index               => __( 'Crew Person Name', 'jws' ),
					'crew:images' . $index             => __( 'Crew Person Images', 'jws' ),
					'crew:category' . $index           => __( 'Crew Person Category', 'jws' ),
					'crew:job' . $index                => __( 'Crew Person Job', 'jws' ),
					'crew:position' . $index           => __( 'Crew Position', 'jws' ),
				),
			),
			'attributes'             => array(
				'name'    => __( 'Attributes', 'jws' ),
				'options' => array(
					'attributes:name' . $index         => __( 'Attribute name', 'jws' ),
					'attributes:value' . $index        => __( 'Attribute value(s)', 'jws' ),
					'attributes:taxonomy' . $index     => __( 'Is a global attribute?', 'jws' ),
					'attributes:visible' . $index      => __( 'Attribute visibility', 'jws' ),
					'attributes:default' . $index      => __( 'Default attribute', 'jws' ),
				),
			),
			'sources'                => array(
				'name'    => __( 'Sources', 'jws' ),
				'options' => array(
					'sources:name' . $index            => __( 'Source Name', 'jws' ),
					'sources:choice' . $index          => __( 'Source Choice', 'jws' ),
					'sources:embed_content' . $index   => __( 'Source Embed Content', 'jws' ),
					'sources:link' . $index            => __( 'Source Link', 'jws' ),
					'sources:is_affiliate' . $index    => __( 'Source Is Affiliate', 'jws' ),
					'sources:quality' . $index         => __( 'Source Quality', 'jws' ),
					'sources:language' . $index        => __( 'Source Language', 'jws' ),
					'sources:player' . $index          => __( 'Source Player', 'jws' ),
					'sources:date_added' . $index      => __( 'Source Date Added', 'jws' ),
					'sources:position' . $index        => __( 'Source Position', 'jws' ),
				),
			),
			'reviews_allowed'        => __( 'Allow customer reviews?', 'jws' ),
			'meta:' . $meta          => __( 'Import as meta', 'jws' ),
			'menu_order'             => __( 'Position', 'jws' ),
		);

		return apply_filters( 'jws_csv_movie_import_mapping_options', $options, $item );
    
    
    
}





?>
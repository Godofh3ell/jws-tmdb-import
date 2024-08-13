<?php
/**
 * Admin View: Fetch TMDB data
 *
 * @package Jws/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<form class="jws-tmdb-form-content jws-tmdb-fetch-data" enctype="multipart/form-data" method="post">
    <header>
        <h2><?php esc_html_e( 'Fetch TMDB API', 'jws' ); ?></h2>
        <p><?php esc_html_e( 'This tool allows you to fetch data from TMDB.', 'jws' ); ?></p>
    </header>
    <section>
        <div class="options_group">
            <p class="form-field jws-tmdb-language_field">
                <label for="jws-tmdb-language"><?php esc_html_e( 'Language:', 'jws' ); ?></label>
                <?php wp_dropdown_languages( array(
                    'id' => 'jws-tmdb-language',
                    'name' => 'jws-tmdb-language',
                    'selected' => get_locale()
                ) ); ?>
            </p>
            <p class="form-field jws-tmdb-type_field">
                <label for="jws-tmdb-type"><?php esc_html_e( 'Type:', 'jws' ); ?></label>
                <?php if( ! empty( $type_options ) )
                    ?><select id="jws-tmdb-type" name="jws-tmdb-type" class="show_hide_select"><?php
                        foreach ( $type_options as $key => $value ) {
                            ?><option value="<?php echo esc_attr( $key ); ?>">
                                <?php echo esc_html( $value ); ?>
                            </option><?php
                        }
                    ?></select><?php
                ?>
            </p>
            <p class="form-field jws-tmdb-search-keyword_field show_if_search-movie show_if_search-tv-show hide">
                <label for="jws-tmdb-search-keyword"><?php esc_html_e( 'Keyword', 'jws' ) ?> : </label>
                <input type="text" class="short" name="jws-tmdb-search-keyword" id="jws-tmdb-search-keyword" value="" placeholder="" style="width: auto;"> 
            </p>
            <p class="form-field jws-tmdb-id_field show_if_movie-by-id show_if_tv-show-by-id hide" style="display: none;">
                <label for="jws-tmdb-id"><?php esc_html_e( 'TMDB ID', 'jws' ) ?> : </label>
                <input type="number" class="short" name="jws-tmdb-id" id="jws-tmdb-id" value="" placeholder="" style="width: auto;"> 
            </p>
            <p class="form-field jws-tmdb-page-number_field show_if_now-playing-movies show_if_popular-movies show_if_top-rated-movies show_if_upcoming-movies show_if_discover-movies show_if_on-air-tv-shows show_if_on-air-today-tv-shows show_if_top-rated-tv-shows show_if_popular-tv-shows show_if_discover-tv-shows hide" style="display: none;">
                <label for="jws-tmdb-page-number"><?php esc_html_e( 'Page Number', 'jws' ) ?> : </label>
                <input type="number" class="short" name="jws-tmdb-page-number" id="jws-tmdb-page-number" value="1" placeholder="" style="width: auto;">
            </p>
        </div>
    </section>
    <div class="jws-actions">
        <button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Continue', 'jws' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'jws' ); ?></button>
        <?php wp_nonce_field( 'jws-tmdb-fetch-data' ); ?>
    </div>
</form>

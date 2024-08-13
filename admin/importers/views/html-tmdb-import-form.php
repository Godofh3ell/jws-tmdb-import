<?php
/**
 * Admin View: Import TMDB data
 *
 * @package Jws/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<form class="jws-tmdb-form-content jws-tmdb-import-data" enctype="multipart/form-data" action="<?php echo esc_url( $action ) ?>" method="post">
    <header>
        <h2><?php esc_html_e( 'Import', 'jws' ); ?></h2>
        <p><?php esc_html_e( 'Here is the data taken from Tmdb Api with your request. Please click import to import data into your website. Import time depends on data size.', 'jws' ); ?></p>
        <p>
          If the import process is interrupted and there is no 'Imported successfully!' message, continue pressing the Import button until you receive the completion notification. If the interruption occurs due to the host's low max_execution_time, simply increase it to 1000 or higher to extend the import waiting time.
        </p>
        <?php if( $this->type === 'movies' ) : ?>
            <p>
            <?php esc_html_e( 'You can open other browser tabs to check the imported data.', 'jws' ); ?>
            <a href="<?php echo esc_url(admin_url('/edit.php?post_type=movies')); ?>" target="_blank">View</a>
            </p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_count, esc_html__( 'Movies found.', 'jws' ) ); ?></p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_person_count, esc_html__( 'Cast found.', 'jws' ) ); ?></p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_crew_count, esc_html__( 'Crew found.', 'jws' ) ); ?></p>
        <?php elseif( $this->type === 'tv_shows' ) : ?>
            <p>
            <?php esc_html_e( 'You can open other browser tabs to check the imported data.', 'jws' ); ?>
            <a href="<?php echo esc_url(admin_url('/edit.php?post_type=tv_shows')); ?>" target="_blank">View</a>
            </p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_count, esc_html__( 'TV Shows found.', 'jws' ) ); ?></p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_person_count, esc_html__( 'Cast found.', 'jws' ) ); ?></p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_crew_count, esc_html__( 'Crew found.', 'jws' ) ); ?></p>
            <p style="font-weight: bold;"><?php echo sprintf( '%d %s', $this->results_csv_data_episodes_count, esc_html__( 'Episodes found.', 'jws' ) ); ?></p>
            <p><input type="checkbox" name="episodes" /><?php echo esc_html__('Import Episodes.','jws') ?></p>
        <?php endif; ?>
        <p><input type="checkbox" name="cast" /><?php echo esc_html__('Import Cast.','jws') ?></p>
        <p><input type="checkbox" name="crew" /><?php echo esc_html__('Import Crew.','jws') ?></p>
        <p>
           <select name="status_type">
              <option value="publish">Published</option>
              <option value="pending">Pending</option>
           </select>
        </p>
    </header>
    <section data-count="<?php echo $this->results_csv_data_count; ?>" data-person="<?php echo $this->results_csv_data_person_count; ?>" data-episodes="<?php echo $this->results_csv_data_episodes_count; ?>">
        <input type="hidden" name="file_url" value="<?php echo esc_attr( $file_url ) ?>" />
        <input type="hidden" name="type" value="<?php echo esc_attr( $_GET['type'] ) ?>" />
        <input type="hidden" name="update_existing" value="1" />
        <input type="hidden" name="action" value="jws_do_ajax_movie_import" />
    </section>
    <div class="jws-actions">
        <button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Continue', 'jws' ); ?>" name="save_step"><?php esc_html_e( 'Import', 'jws' ); ?></button>
        <?php wp_nonce_field( 'jws-csv-importer' ); ?>
    </div>
</form>

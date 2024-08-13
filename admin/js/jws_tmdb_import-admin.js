(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
     $(document).ready(function() {

        		
        
         $( '.show_hide_select' ).on( 'change', function() {
       
            var shortcode_select = $(this).val(), 
                $jws_wp_shortcode = $(this).parents( '.options_group' );
    
                $jws_wp_shortcode.find( '.hide' ).hide();
                $jws_wp_shortcode.find( '.show_if_' + shortcode_select ).show();
                
        }).change();
               
               
               
        
             
               $(document).on('submit' , '.jws-tmdb-import-data' , function(e) { 
                
                        e.preventDefault();
                        
                        var form = $(this);
           
                        var formData = new FormData(this);
                        
                        form.addClass('loading');
                        
                        if(!form.find('.loader').length) {
                                 form.append('<div class="loader"><svg class="circular" viewBox="25 25 50 50"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div>');    
                        }
              
                   
                        $.ajax({
                            
                            url: jws_script.ajax_url,
                            data: formData,
                            method: 'POST',
                            contentType: false,
        			        processData: false,
                            success: function(response) {
                                
                               if(!form.find('.successfully').length) {
                                        form.append('<p class="successfully">Imported successfully!</p>');    
                               }
                        
                               console.log(response);
                                				
                            },
                            error: function() {
                                console.log('error');
                            },
                            complete: function() {form.removeClass('loading')},
                        })
              });

        
        
	});
      

})( jQuery );

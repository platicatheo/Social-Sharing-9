
(function($) {

	$(window).on('load', function() {

			$.post( document.location.origin + '/wp-content/plugins/social_sharing_9/ajax/social_pulling_ajax.php' , '', function(response) {

				var response = JSON.parse(response);
				// console.log( response.placement );

				$.each(response.placement, function(key, value) {
					if(value == 'floating_left') {
						$('body').prepend('<div class="floating_left_socials9">' + response.content + '</div>');						
					}
					if(value == 'inside_featured_image') {
						if( $('.wp-post-image').length ) {
							$('.wp-post-image').parent().prepend(response.content);	
						}					
					}
					if(value == 'below_post_title') {
						$('.entry-title').append(response.content);
					}
				});

			});

	});



})(jQuery);
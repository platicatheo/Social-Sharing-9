
(function($) {

	$(window).on('load', function() {

			var ajax_url = fe_localize_params.ajax_url;
			var post_id = fe_localize_params.post_id;

			var data = {
									'action': 'social_pulling_ajax'
								};
			
			// console.log( data );
			// console.log( ajax_url );

			$.post( ajax_url , data, function(response) {

				var response = JSON.parse(response);

				if( response.content ) {
					var correct_urls_resp = response.content.toString().replace(new RegExp(window.location.hostname, 'g'), document.location.href);

					// console.log( 'window.location.origin: '+window.location.origin );
					// console.log( 'document.location.href: '+document.location.href );
					// console.log( correct_urls_resp );

					$.each(response.placement, function(key, value) {
						if(value == 'floating_left') {
							$('body').prepend('<div class="floating_left_socials9">' + correct_urls_resp + '</div>');						
						}
						if(value == 'inside_featured_image') {
							if( $('.inside_featured_img_social9.'+post_id).length ) {
								$('.inside_featured_img_social9.'+post_id).prepend(correct_urls_resp);	
							}					
						}
						if(value == 'below_post_title') {
							$socials_title_span = $('.socials_title_span');
							var $title_parent_cont = $socials_title_span.parent();

							$socials_title_span.remove();
							$title_parent_cont.append('<div class="after_title_socials9">' + correct_urls_resp + '</div>');

						}
					});
				}

			});

	});



})(jQuery);
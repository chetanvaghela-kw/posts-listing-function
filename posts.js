jQuery(document).ready(function ($) {

     jQuery('.get-category-posts').on('click', function (e) {
	      jQuery('.get-category-posts').removeClass('active');
	      jQuery(this).addClass('active');
	      var term_id =  jQuery(this).attr('data-term_id');
	      var term_slug =  jQuery(this).attr('data-term_slug');
	      jQuery('#custom-post-container').removeClass().addClass(term_slug + '-wrapper');
	      jQuery('#custom-post-container').addClass('row');
	      //jQuery('#custom-post-container').html('<div class="section btn-wrap text-center"><span class="btn btn-secondary custom-load-more-btn">Loading...</span></div>');
          
	      jQuery('.custom-load-more-btn').attr('data-term_id', term_id);
	      jQuery('.custom-load-more-btn').attr('data-term_slug', term_slug);
	      jQuery('.custom-load-more-page').val(parseInt(0));
          var taxonomy = jQuery('.custom-load-more-btn').attr('data-taxonomy'); // Get the value from `data-value`
	      var post_type = jQuery('.custom-load-more-btn').attr('data-post_type'); // Get the value from `data-value`
	      jQuery('.custom-load-more-btn').hide();
          var is_loadmore = false;
	      customPosts(term_slug,term_id, is_loadmore, taxonomy, post_type);
	});

    $(document).on('click', '.custom-load-more-btn', function (e) {
	      e.preventDefault();
	       $('.custom-load-more-btn').hide();
	      const term_slug = $(this).attr('data-term_slug'); // Get the value from `data-value`
	      const term_id = $(this).attr('data-term_id'); // Get the value from `data-value`
	      const taxonomy = $(this).attr('data-taxonomy'); // Get the value from `data-value`
	      const post_type = $(this).attr('data-post_type'); // Get the value from `data-value`
	      //alert(selectedValue);
	      var is_loadmore = true;
	      customPosts(term_slug,term_id,is_loadmore, taxonomy, post_type);
	}); 

       // Perform AJAX request
	function customPosts(term_slug = 'all', term_id = '0', is_loadmore = false, get_taxonomy = 'category', get_post_type = 'post') {

	    if(is_loadmore === false)
	    {
	      jQuery('#custom-post-container').html('');
	    }
	    const get_current_page = jQuery('.custom-load-more-page').val(); 
	    const get_page =  parseInt(get_current_page) + 1;
	      $.ajax({
	          url: posts_ajaxurl.ajaxurl, // WordPress AJAX URL
	          type: 'POST',
	          data: {
	              action: 'custom_load_more_posts', // AJAX action hook
	              get_category: term_id,
	              get_current_page: get_page,
                  get_taxonomy: get_taxonomy,
                  get_post_type: get_post_type,
	              posts_nonce: posts_ajaxurl.nonce,
	          },
	          success: function (response) {
	            if (response.success) {
	              $('.custom-load-more-page').val(parseInt(get_page));
	              //jQuery('#custom-post-container').append(response.html);

                   // Get current last item before adding new content
                    var $container = $('#custom-post-container');
                    var $lastElement = $container.children().last();

                    // Append new content
                    $container.append(response.html);

                    // Find first new element
                    var $newElement = $lastElement.next();

                    if ($newElement.length) {
                        $('html, body').animate({
                            scrollTop: $newElement.offset().top - 100 // adjust offset as needed
                        }, 600);
                    }

	              if (get_page >= response.maxPage) {
	                  $('.custom-load-more-btn').hide();
	                }
	                else
	                {
	                   $('.custom-load-more-btn').show();
	                } 
	            }
	            else {
	                $('.custom-load-more-btn').hide();
	            }
	          },
	          error: function () {
	               jQuery('#custom-post-container').append('<p>Something went wrong. Please try again.</p>');
	          }
	      });
	}

	jQuery(document).on('click', '.posts-load-more-items-btn', function (e) { 
	      e.preventDefault();
	      $('.posts-load-more-wrap').find('.posts-load-more-items-btn').hide();
	      const get_current_page = jQuery('.posts-load-more-wrap').find('.posts-load-more-page').val(); 
	      const get_per_page = jQuery('.posts-load-more-wrap').find('.posts-load-per-page').val(); 
	      const get_category = jQuery('.posts-load-more-wrap').find('.posts-load-categoty').val(); 
	      const get_taxonomy = jQuery('.posts-load-more-wrap').find('.posts-load-taxonomy').val(); 
	      const get_post_type = jQuery('.posts-load-more-wrap').find('.posts-load-post_type').val(); 
	      const get_page =  parseInt(get_current_page) + 1;
	        $.ajax({
	            url: posts_ajaxurl.ajaxurl, // WordPress AJAX URL
	            type: 'POST',
	            data: {
	                action: 'custom_load_more_posts',
	                get_current_page: get_page,
	                get_per_page: get_per_page,
	                get_category: get_category,
	                get_taxonomy: get_taxonomy,
	                get_post_type: get_post_type,
	                posts_nonce: posts_ajaxurl.nonce,
	            },
	            success: function (response) {
	              $('.posts-load-more-wrap').find('.posts-load-more-items-btn').show();
	              if (response.success) {
	                $('.posts-load-more-wrap').find('.posts-load-more-page').val(parseInt(get_page));
	                //$('.posts-listing-wrap').append(response.html);
                    var $container = $('.posts-listing-wrap');
                    var $lastElement = $container.children().last();

                    // Append new content
                    $container.append(response.html);

                    // Find first new element
                    var $newElement = $lastElement.next();

                    if ($newElement.length) {
                        $('html, body').animate({
                            scrollTop: $newElement.offset().top - 100 // adjust offset as needed
                        }, 600);
                    }
	                if (get_page >= response.maxPage) {
	                    $('.posts-load-more-wrap').find('.posts-load-more-items-btn').hide();
	                  } 
	              }
	              else {
	                  $('.posts-load-more-wrap').find('.posts-load-more-items-btn').hide();
	              }
	            },
	            error: function () {
	                $('.posts-listing-wrap').append('<p>Something went wrong. Please try again.</p>');
	            }
	        });
	});  

    jQuery(document).ready(function($) {
      if ( $('.posts-load-more-scroll').length ) {
          var $container = $('#custom-post-container');
          var $loader = $('.blog-loader');
          var $scrollTrigger = $('.posts-load-more-scroll');

          var page = parseInt($('.posts-load-more-page').val());
          var perPage = parseInt($('.posts-load-per-page').val());
          var postType = $('.posts-load-post_type').val();
          var taxonomy = $('.posts-load-taxonomy').val();
          var termId = $('.posts-load-categoty').val();
          var loadMore = true;
          var isLoading = false;

          // Hide loader initially
          if ($loader.length) {
              $loader.css('visibility', 'hidden');
          }

          function loadMorePosts() {
              if (isLoading || !loadMore) return;

              isLoading = true;
              $loader.css('visibility', 'visible');

              var data = {
                  action: 'custom_load_more_posts',
                  get_current_page: page + 1,
                  get_per_page: perPage,
                  get_post_type: postType,
                  get_taxonomy: taxonomy,
                  get_category: termId,
                  posts_nonce: posts_ajaxurl.nonce,
              };

              $.ajax({
                  type: 'POST',
                  url: posts_ajaxurl.ajaxurl,
                  data: data,
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                          $container.append(response.html);
                          page++;
                          $('.posts-load-more-page').val(page);

                          if (page >= response.maxPage) {
                              loadMore = false;
                          }
                      } else {
                          loadMore = false;
                      }

                      isLoading = false;
                      $loader.css('visibility', 'hidden');
                  },
                  error: function() {
                      isLoading = false;
                        $loader.css('visibility', 'hidden');
                    }
                });
            }

            $(window).on('scroll', function() {
                if (!loadMore || isLoading) return;

                var scrollTriggerOffset = $scrollTrigger.offset().top;
                var windowBottom = $(window).scrollTop() + $(window).height();

                if (windowBottom >= scrollTriggerOffset - 100) {
                    loadMorePosts();
                }
            });
        }
    });

});

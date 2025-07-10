<?php
/**
 * Additional Function and Definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package    WordPress
 * @subpackage my-theme
 * @since      1.0
 */

/**
 * Enqueue scripts and css.
 */
function custom_themes_scripts() {

	wp_register_script(
		'posts-listing-js',
		get_template_directory_uri() . '/assets/js/posts.js',
		array( 'jquery' ),
		true,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_enqueue_script( 'posts-listing-js' );
	wp_localize_script(
		'posts-listing-js',
		'posts_ajaxurl',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'posts_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'custom_themes_scripts' );


/**
 * Load more Posts.
 */
function custom_load_more_posts_callback() {

	// Check if the form was submitted.
	if ( ! isset( $_POST['posts_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['posts_nonce'] ) ), 'posts_nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
	}

	// Sanitize the input.
	$get_per_page     = isset( $_POST['get_per_page'] ) ? sanitize_text_field( wp_unslash( $_POST['get_per_page'] ) ) : '';
	$get_post_type    = isset( $_POST['get_post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['get_post_type'] ) ) : 'post';
	$get_term_id      = isset( $_POST['get_category'] ) ? absint( $_POST['get_category'] ) : 0;
	$get_taxonomy     = isset( $_POST['get_taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['get_taxonomy'] ) ) : 'category';
	$get_current_page = isset( $_POST['get_current_page'] ) ? sanitize_text_field( wp_unslash( $_POST['get_current_page'] ) ) : '';

	// Perform actions based on the selected value (query posts, fetch data, etc.).
	if ( $get_current_page ) {
		$args = array(
			'post_type'      => $get_post_type,
			'posts_per_page' => $get_per_page,
			'post_status'    => 'publish',
		);

		if ( ! empty( $get_current_page ) ) {
			$args['paged'] = $get_current_page;
		}

		// Handle taxonomy filtering if term ID is provided.
		if ( $get_term_id && $get_taxonomy ) {
			$args['tax_query'][] = array(
				'taxonomy' => $get_taxonomy,
				'field'    => 'term_id',
				'terms'    => $get_term_id,
			);
		}

		if ( ! empty( $get_category ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $get_category,
			);
		}

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			ob_start();
			$get_post_img = get_template_directory_uri() . '/assets/images/placeholder/placeholder.webp';
			while ( $query->have_posts() ) {
				$query->the_post();
				$get_blog_id = get_the_ID();
				// phpcs:ignore
				// $post_categories = get_the_terms( $get_blog_id, 'CATEGORY_SLUG' );	// UPDATE A SLUG OF CATEGORY FOR POST TYPE		
				?>
				<div class="col-sm-4 mt-4">
					<div class="card">
						<a href="<?php echo esc_url( get_permalink( $get_blog_id ) ); ?>">
							<?php if ( has_post_thumbnail( $get_blog_id ) ) : ?>
								<?php
								$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $get_blog_id ), 'large' );
								?>
								<img class="card-img-top" src="<?php echo esc_url( $large_image_url[0] ); ?>" alt="Image">
								<?php else : ?>
								<img class="card-img-top" src="<?php echo esc_url( $get_post_img ); ?>" alt="Placeholder">
								<?php endif; ?>
							</a>							
							<div class="card-body">
									<h5 class="card-title">
									<a href="<?php echo esc_url( get_permalink( $get_blog_id ) ); ?>">
										<?php echo esc_html( get_the_title( $get_blog_id ) ); ?>
									</a>
								</h5>
								<p class="card-text"><?php echo esc_html( wp_trim_words( get_the_excerpt( $get_blog_id ), 20, '...' ) ); ?></p>
								<a class="btn btn-primary" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'View', 'text-domain' ); ?></a>
							</div>
					</div>
				</div>				
				<?php
			}
			$output   = ob_get_clean();
			$response = array(
				'success' => true,
				'html'    => $output,
				'maxPage' => $query->max_num_pages,
			);
		} else {
			$response = array(
				'success' => false,
				'message' => 'No more Posts available.',
			);
		}
		wp_reset_postdata();
	}
	wp_send_json( $response );
}
add_action( 'wp_ajax_custom_load_more_posts', 'custom_load_more_posts_callback' );
add_action( 'wp_ajax_nopriv_custom_load_more_posts', 'custom_load_more_posts_callback' );

/**
 * Custom Pagination.
 *
 * @param Object $cquery widget id.
 * @param string $classname class name.
 */
function custom_pagination_numeric( $cquery = array(), $classname = '' ) {

	global $wp_query;
	$cquery = ( ! empty( $cquery ) ) ? $cquery : $wp_query;
	if ( ! empty( $cquery ) ) :
		$big      = 999999999;
		$paginate = paginate_links(
			array(
				'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'    => '?paged=%#%',
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $cquery->max_num_pages,
				'type'      => 'list',
				'mid_size'  => 1,
				'prev_text' => '<',
				'next_text' => '>',
				'end_size'  => 1,
			)
		);

		if ( ! empty( $paginate ) ) :
			echo '<div class="pagination-wrap ' . esc_attr( $classname ) . '"><nav>';
			$paginate     = str_replace( "<ul class='page-numbers'>", '<ul class="pagination">', $paginate );
			$paginate     = str_replace( '<li>', '<li class="page-item">', $paginate );
			$paginate     = str_replace( 'page-numbers', 'page-link', $paginate );
			$paginate     = str_replace(
				'<li class="page-item"><span aria-current="page" class="page-link current">',
				'<li class="page-item active"><span aria-current="page" class="page-link">',
				$paginate
			);
			$allowed_html = array(
				'a'      => array(
					'id'    => true,
					'href'  => true,
					'title' => true,
				),
				'div'    => true,
				'span'   => true,
				'ul'     => true,
				'li'     => array(
					'class' => true,
				),
				'strong' => true,
			);
			echo wp_kses( $paginate, $allowed_html );
			echo '</nav></div>';
		endif;
	endif;
}

/**
 * Custom Post Listing Function.
 *
 * @param string $get_post_type         Custom post type (default: 'post').
 * @param string $more_post_type    'pagination' or 'loadmore'.
 * @param int    $posts_per_page    Posts per page (optional).
 */
function custom_post_listing( $get_post_type = 'post', $more_post_type = 'pagination', $posts_per_page = 3 ) {

	$get_post_type = ! empty( $get_post_type ) ? $get_post_type : get_post_type();

	$default_posts_per_page = get_option( 'posts_per_page' );
	$post_per_page          = ! empty( $posts_per_page ) ? $posts_per_page : $default_posts_per_page;

	global $wp_query;
	$current_page = max( 1, get_query_var( 'paged' ) );

	// Detect if we are on archive (category, tag, taxonomy) and fetch terms.
	$term_query = get_queried_object();

	$is_archive = is_category() || is_tag() || is_tax();
	// If not using default WP_Query (like single template or you pass a different post_type)
	// We manually create a new query if post_type is not default 'post' or when not using the default loop.
	if ( ! is_main_query() || ( get_post_type() !== $get_post_type && ! $is_archive ) ) {
		$args = array(
			'post_type'      => $get_post_type,
			'posts_per_page' => $post_per_page,
			'paged'          => $current_page,
		);

		// If on taxonomy archive, include taxonomy filtering.
		if ( $is_archive && isset( $term_query->taxonomy ) && isset( $term_query->term_id ) ) {
			$tax_query = 'tax_query';

			$args[ $tax_query ] = array(
				array(
					'taxonomy' => $term_query->taxonomy,
					'field'    => 'term_id',
					'terms'    => $term_query->term_id,
				),
			);
		}

		$posts_query = new WP_Query( $args );
	} else {
		$posts_query   = $wp_query;
		$post_per_page = ! empty( $default_posts_per_page ) ? $default_posts_per_page : $post_per_page;
	}

	if ( 'loadmore-with-filter' === $more_post_type ) :
		$taxonomies = get_object_taxonomies( $get_post_type, 'objects' );
		$taxonomy   = ''; // Default taxonomy.
		foreach ( $taxonomies as $tax ) {
			if ( $tax->public && $tax->show_ui ) {
				$taxonomy = $tax->name;
				break;
			}
		}

		$categories = array();
		if ( $taxonomy ) {
			$categories = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
					'exclude'    => array( 1 ),
				)
			);
		}

		if ( ! empty( $categories ) ) :
			?>
			<div class="blog-filter row p-3">
				<ul class="nav nav-pills">
					<li class="nav-item"><button type="button" class="active get-category-posts nav-link" data-term_id="0" data-term_slug="all">All</button></li>
					<?php
					foreach ( $categories as $category ) :
						echo '<li class="nav-item"><button class="get-category-posts nav-link"  type="button" data-term_id="' . esc_attr( $category->term_id ) . '" data-term_slug="' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</button></li>';
					endforeach;
					?>
				</ul>
			</div>
			<?php
		endif;
	endif;

	if ( $posts_query->have_posts() ) :
		?>
		<div class="posts-listing-wrap row p-3" id="custom-post-container">
			<?php
			$get_post_img = get_template_directory_uri() . '/assets/images/placeholder/placeholder.webp';
			while ( $posts_query->have_posts() ) :
				$posts_query->the_post();
				$get_blog_id = get_the_ID();
				?>
				<div class="col-sm-4 mt-4">
					<div class="card">
						<a href="<?php echo esc_url( get_permalink( $get_blog_id ) ); ?>">
							<?php if ( has_post_thumbnail( $get_blog_id ) ) : ?>
								<?php
								$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $get_blog_id ), 'large' );
								?>
								<img class="card-img-top" src="<?php echo esc_url( $large_image_url[0] ); ?>" alt="Image">
								<?php else : ?>
								<img class="card-img-top" src="<?php echo esc_url( $get_post_img ); ?>" alt="Placeholder">
								<?php endif; ?>
							</a>							
							<div class="card-body">
									<h5 class="card-title">
									<a href="<?php echo esc_url( get_permalink( $get_blog_id ) ); ?>">
										<?php echo esc_html( get_the_title( $get_blog_id ) ); ?>
									</a>
								</h5>
								<p class="card-text"><?php echo esc_html( wp_trim_words( get_the_excerpt( $get_blog_id ), 20, '...' ) ); ?></p>
								<a class="btn btn-primary" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'View', 'text-domain' ); ?></a>
							</div>
					</div>
				</div>
				<?php
			endwhile;
			?>
		</div>

		<?php if ( 'pagination' === $more_post_type ) : ?>
			<?php custom_pagination_numeric( $posts_query ); ?>
		<?php elseif ( 'loadmore' === $more_post_type && $posts_query->found_posts > $post_per_page ) : ?>
			<div class="posts-load-more-wrap row">
				<div class="col-2 mx-auto">
					<a href="javascript:void(0);" class="posts-load-more-items-btn btn btn-primary">
						<?php esc_html_e( 'Load More', 'text-domain' ); ?>
					</a>
					<input type="hidden" class="posts-load-more-page" value="1">
					<input type="hidden" class="posts-load-per-page" value="<?php echo esc_attr( $post_per_page ); ?>">
					<input type="hidden" class="posts-load-categoty" value="<?php echo esc_attr( $term_query->term_id ?? '' ); ?>">
					<input type="hidden" class="posts-load-taxonomy" value="<?php echo esc_attr( $term_query->taxonomy ?? 'category' ); ?>">
					<input type="hidden" class="posts-load-post_type" value="<?php echo esc_attr( $get_post_type ); ?>">
				</div>
			</div>
		<?php elseif ( 'scroll' === $more_post_type ) : ?>
			<div class="posts-load-more-scroll"></div>
			<div class="blog-loader"><?php esc_html_e( 'Loading...', 'text-domain' ); ?></div>
			<input type="hidden" class="posts-load-more-page" value="1">
			<input type="hidden" class="posts-load-per-page" value="<?php echo esc_attr( $post_per_page ); ?>">
			<input type="hidden" class="posts-load-categoty" value="<?php echo esc_attr( $term_query->term_id ?? '' ); ?>">
			<input type="hidden" class="posts-load-taxonomy" value="<?php echo esc_attr( $term_query->taxonomy ?? 'category' ); ?>">
			<input type="hidden" class="posts-load-post_type" value="<?php echo esc_attr( $get_post_type ); ?>">
		<?php elseif ( 'loadmore-with-filter' === $more_post_type ) : ?>

			<div class="row">
				<div class="col-2 mx-auto">
					<a href="javascript:void(0);" data-term_slug="all" data-term_id=""  data-taxonomy="<?php echo esc_attr( $taxonomy ?? 'category' ); ?>"  data-post_type="<?php echo esc_attr( $get_post_type ); ?>" class="custom-load-more-btn btn btn-primary" style="<?php echo $posts_query->found_posts > $post_per_page ? '' : 'display:none;'; ?>" >
						<?php esc_html_e( 'Load More', 'text-domain' ); ?>
					</a>
					<input type="hidden" class="custom-load-more-page" value="1">
				</div>
			</div>

		<?php endif; ?>

		<?php
		else :
			?>
		<div class="container">
			<p><?php esc_html_e( 'Posts not available.', 'text-domain' ); ?></p>
		</div>
			<?php
	endif;
		wp_reset_postdata();
}

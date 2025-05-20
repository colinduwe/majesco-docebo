<?php
/**
 * Server-side rendering of the `majesco-docebo/course-grid` block.
 *
 * @package Majesco\Docebo
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Get the block attributes
$attributes = $args['attributes'] ?? [];

// Get the selected course types
$selected_types = $attributes['selectedTypes'] ?? [];

// Set up the query arguments
$query_args = array(
	'post_type' => 'majesco_course',
	'posts_per_page' => -1,
	'orderby' => 'title',
	'order' => 'ASC',
);

$courses = new WP_Query($query_args);

if( $courses->have_posts() ):

?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="block-majesco-resource-hub-cards-grid gray-background-behind-cards">
		<div class="container">
			<div class="block-majesco-solution-content__inner load-more-target row row-cols-1 row-cols-md-3 g-4">
				<?php while( $courses->have_posts() ): $courses->the_post(); ?>
				<div class="col">
					<article <?php post_class( 'card h-100 majesco-content-card'); ?> id="post-<?php echo esc_attr(get_the_ID()); ?>">

						<div class="card-body">

							<header class="entry-header">

								<?php $course_types = get_the_terms( get_the_ID(), 'majesco_course_type' ); ?>
								<?php if ( $course_types && !is_wp_error($course_types) ): ?>
									<h5 class="is-style-pill is-course-type-<?php echo esc_attr($course_types[0]->slug); ?>"><?php echo esc_html($course_types[0]->name); ?></h5>
								<?php endif; ?>
								<h3 class="entry-title card-title"><?php the_title(); ?></h3>
							</header><!-- .entry-header -->

							<div class="entry-content card-text">
								<div class="info-pills">
								<?php \Majesco\Docebo\number_of_courses_pill( get_the_ID() ); ?>
								<?php \Majesco\Docebo\duration_pill( get_the_ID() ); ?>
								<?php \Majesco\Docebo\price_pill( get_the_ID() ); ?>
								</div>
								<div class="wp-block-button is-style-transparent-expand">
									<a class="wp-block-button__link majesco-2022-read-more-link" 
									   rel="bookmark" 
									   href="" 
									   data-bs-toggle="modal" 
									   data-bs-target="#majesco_course-modal" 
									   data-post-id="<?php echo esc_attr(get_the_ID()); ?>" 
									   data-post-type="majesco_course"
									   data-nonce="<?php echo esc_attr(wp_create_nonce('majesco_docebo_nonce')); ?>">
										<?php esc_html_e('Learn More', 'majesco-docebo'); ?>
									</a>
								</div>
							</div><!-- .entry-content -->

						</div>

					</article><!-- #post-## -->
				</div>
				<?php endwhile; ?>
		</div>
		<div class="row">
			<div class="modal modal-xl fade" id="majesco_course-modal" tabindex="-1" aria-labelledby="majesco_courseModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content" data-object-type="post">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<script>
	const majesco_docebo_ajax_load_more = {
		ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ); ?>'
	};
</script>
<?php
endif;
wp_reset_postdata();

<?php
namespace Majesco\Docebo;
// Course modal content template
$course_type = get_post_meta( get_the_ID(), 'item_type', true );
$course_type_int = get_post_meta( get_the_ID(), 'item_type_int', true );
$course_id = get_post_meta( get_the_ID(), 'item_id', true );
$course_slug = get_post_meta( get_the_ID(), 'item_slug', true );
$course_terms = get_the_terms( $post, 'majesco_course_type' );
$course_description_label = $course_type_int == 2 ? 'Learning Plan Description' : 'Course Description';
$course_type_color = $course_type_int == 2 ? 'periwinkle' : 'teal';
$course_url = $course_type_int == 2 ? 'https://majesco.docebosaas.com/learn/learning-plans/' : 'https://majesco.docebosaas.com/learn/courses/';
$course_url .= $course_id . '/' . $course_slug;
$lp_details = get_post_meta( get_the_ID(), 'lp_details', true );
?>

<button type="button" class="btn btn-outline-white modal-close-button" data-bs-dismiss="modal" aria-label="Close">Close</button>
<div class="modal-header transparent-card-color-<?php echo $course_type_color; ?> has-background">
	<div id="modal-previous-card" class="card-modal-nav<?php echo  !$args['prevID'] ? ' invisible' : ''; ?>" data-post-id="<?php echo $args['prevID']; ?>">
		<span class="radix-chevron-left"></span>
	</div>
	<div class="w-100">
		<h4 class=""><?php echo $course_terms[0]->name; ?></h4>
		<h2><?php the_title(); ?></h2>
	</div>
	<div id="modal-next-card" class="card-modal-nav<?php echo  !$args['nextID'] ? ' invisible' : ''; ?>" data-post-id="<?php echo $args['nextID']; ?>">
		<span class="radix-chevron-right"></span>
	</div>
</div>
<div class="modal-body container-fluid">
	<div class="row row-col-10 has-pale-gray-background-color has-background">
		<div class="col-md-8 offset-md-1 majesco-course-col">
			<h3><?php echo $course_description_label; ?></h3>
			<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button is-style-outline" href="<?php echo esc_url( $course_url ); ?>" target="_blank"><?php _e('Go to course', 'majesco-docebo'); ?></a></div>
			</div>
			<?php \Majesco\Docebo\duration_pill( get_the_ID() ); ?>
			<?php the_content(); ?>
			<a href="<?php echo esc_url( $course_url ); ?>" target="_blank"><?php _e('Go to course', 'majesco-docebo'); ?></a>
		</div>
	</div>
</div>

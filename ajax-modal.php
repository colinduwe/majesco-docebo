<?php
/*	-----------------------------------------------------------------------------------------------
	AJAX MODALS
	Called in view.js when content is needed inside a modal.
--------------------------------------------------------------------------------------------------- */

namespace Majesco\Docebo;

if (!function_exists('ajax_modals')) {
	function ajax_modals() {
		// Verify nonce
		if (!check_ajax_referer('majesco_docebo_nonce', 'nonce', false)) {
			wp_send_json_error('Invalid nonce');
		}

		// Verify user capabilities
		if (!current_user_can('read')) {
			wp_send_json_error('Insufficient permissions');
		}

		// Sanitize and validate input
		$data = isset($_POST['data']) ? json_decode(wp_unslash($_POST['data']), true) : null;
		
		if (!$data || !isset($data['postType'], $data['id'])) {
			wp_send_json_error('Invalid data');
		}

		// Handle course modal request
		if ($data['postType'] === 'majesco_course') {
			$course_id = absint($data['id']);
			$course = get_post($course_id);

			if (!$course || $course->post_type !== 'majesco_course') {
				wp_send_json_error('Invalid course');
			}

			$args = array(
				'nextID' => absint($data['nextID'] ?? 0),
				'prevID' => absint($data['prevID'] ?? 0),
			);

			global $post;
			$post = $course;
			
			ob_start();
			include(MAJESCO_DOCEBO_PLUGIN_DIR_PATH . 'template-parts/modal-majesco_course.php');
			$content = ob_get_clean();
			
			echo $content;
			wp_die();
		}

		wp_die('Invalid post type');
	}
	add_action('wp_ajax_nopriv_majesco_docebo_ajax_modals', __NAMESPACE__ . '\\ajax_modals');
	add_action('wp_ajax_majesco_docebo_ajax_modals', __NAMESPACE__ . '\\ajax_modals');
}

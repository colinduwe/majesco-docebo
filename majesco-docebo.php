<?php
/**
* Plugin Name: Majesco Docebo
* Description: Plugin for Docebo integration.
* Version: 1.0
* Author: Colin Duwe
* Author URI: https://www.colinduwe.com
*/

namespace Majesco\Docebo;

// Define the plugin version
define('MAJESCO_DOCEBO_PLUGIN_VERSION', '1.0.1');
define('MAJESCO_DOCEBO_PLUGIN_DIR_PATH', trailingslashit(plugin_dir_path( __FILE__ )) );
 
function plugin_uninstall() {	
	// Remove all custom posts of the "Course" type
	$args = array(
		'post_type' => 'majesco_course',
		'posts_per_page' => -1,
	);
	
	$course_posts = get_posts($args);
	foreach ($course_posts as $course_post) {
		wp_delete_post($course_post->ID, true); // Force delete
	}
	
	// Delete all terms from the custom taxonomy
	$taxonomy = 'majesco_course_type';
	$terms = get_terms($taxonomy, array('hide_empty' => false));
	foreach ($terms as $term) {
		wp_delete_term($term->term_id, $taxonomy);
	}
	
	// Unregister the custom post type and taxonomy (optional)
	unregister_post_type('majesco_course');
	unregister_taxonomy('majesco_course_type');
}
register_uninstall_hook(__FILE__, __NAMESPACE__ . '\plugin_uninstall');

 
function register_course_post_type() {
	$args = array(
		'public' => false, // Hide the post type on the front end
		'show_ui' => false, // Hide the admin UI
		'label' => 'Courses',
		'supports' => array('title', 'editor', 'custom-fields'),
		'capability_type' => 'post',
		'rewrite' => false,
		'show_in_rest' => true,
	);
	
	register_post_type('majesco_course', $args);
}
add_action('init', __NAMESPACE__ . '\register_course_post_type');

function register_course_type_taxonomy() {
	$args = array(
		'public' => false, // Hide the taxonomy on the front end
		'hierarchical' => true,
		'show_ui' => true, // Hide the admin UI
		'labels' => array(
			'name' => 'Course Types',
			'singular_name' => 'Course Type',
		),
		'show_in_rest'	=> true,
	);
	
	register_taxonomy('majesco_course_type', 'majesco_docebo_course', $args);
}
add_action('init', __NAMESPACE__ . '\register_course_type_taxonomy');

// Add post_meta to the course REST API response
function register_course_meta(){
	$course_metas = array(
		'item_id' => array(
			'type'	=> 'integer',
			'description'	=> 'item id from API'
		),
		'item_type' => array(
			'type'	=> 'string',
			'description'	=> 'item type from API'
		),
		'item_type_int' => array(
			'type'	=> 'integer',
			'description'	=> 'item type number from API'
		),
		'item_slug' => array(
			'type'	=> 'string',
			'description'	=> 'slug from API'
		),
		'duration' => array(
			'type'	=> 'string',
			'description'	=> 'duration'
		),
		'item_price' => array(
			'type'	=> 'string',
			'description'	=> 'price'
		),
		'number_of_courses' => array(
			'type'	=> 'integer',
			'description'	=> 'Number of courses in the learning plan'
		),
		'lp_details' => array(
			'type'	=> 'string',
			'description'	=> 'learning plan JSON'
		),
	);
	
	$object_type = 'post';
	foreach( $course_metas as $key => $course_meta ){
		$meta_args = array( // Validate and sanitize the meta value.
			// Note: currently (4.7) one of 'string', 'boolean', 'integer',
			// 'number' must be used as 'type'. The default is 'string'.
			'object_subtype'	=> 'majesco_course',
			'type'         => $course_meta['type'],
			// Shown in the schema for the meta key.
			'description'  => $course_meta['description'],
			// Return a single value of the type.
			'single'       => true,
			// Show in the WP REST API response. Default: false.
			'show_in_rest' => true,
		);
		register_meta( $object_type, $key, $meta_args );
	}
}
add_action('init', __NAMESPACE__ . '\register_course_meta');
 
function menu_page() {
	add_options_page(
		'Majesco Docebo Settings',
		'Majesco Docebo',
		'manage_options',
		'majesco-docebo-settings',
		__NAMESPACE__ . '\\settings_page'
	);
}
add_action('admin_menu', __NAMESPACE__ . '\menu_page');
 
/**
 * Encrypt a string using WordPress's AUTH_SALT
 *
 * @param string $value The value to encrypt
 * @return string The encrypted value
 */
function encrypt_api_key($value) {
    if (empty($value)) {
        return '';
    }
    
    $encryption_key = AUTH_SALT;
    
    // Encrypt the value
    $encrypted = openssl_encrypt(
        $value,
        'AES-256-CBC',
        $encryption_key,
        0,
        substr($encryption_key, 0, 16)
    );
    
    return base64_encode($encrypted);
}

/**
 * Decrypt a string using WordPress's AUTH_SALT
 *
 * @param string $encrypted_value The encrypted value
 * @return string The decrypted value
 */
function decrypt_api_key($encrypted_value) {
    if (empty($encrypted_value)) {
        return '';
    }
    
    $encryption_key = AUTH_SALT;
    
    // Decrypt the value
    $decrypted = openssl_decrypt(
        base64_decode($encrypted_value),
        'AES-256-CBC',
        $encryption_key,
        0,
        substr($encryption_key, 0, 16)
    );
    
    return $decrypted;
}

/**
 * Mask an API key for display
 *
 * @param string $key The API key to mask
 * @return string The masked API key
 */
function mask_api_key($key) {
    if (empty($key)) {
        return '';
    }
    
    $length = strlen($key);
    if ($length <= 8) {
        return str_repeat('*', $length);
    }
    
    return substr($key, 0, 4) . str_repeat('*', $length - 8) . substr($key, -4);
}

function settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'majesco-docebo'));
    }
    ?>
    <div class="wrap">
        <h2><?php _e('Majesco Docebo Settings', 'majesco-docebo'); ?></h2>
        <form method="post" action="options.php">
            <?php 
            settings_fields('majesco-docebo-settings-group');
            wp_nonce_field('majesco_docebo_settings', 'majesco_docebo_nonce');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Client ID', 'majesco-docebo'); ?></th>
                    <td>
                        <?php 
                        $client_id = get_option('majesco_docebo_client_id');
                        $display_id = $client_id ? mask_api_key(decrypt_api_key($client_id)) : '';
                        ?>
                        <input type="text" 
                               name="majesco_docebo_client_id" 
                               value="<?php echo esc_attr($display_id); ?>" 
                               placeholder="<?php esc_attr_e('Enter new Client ID', 'majesco-docebo'); ?>"
                               required />
                        <?php if ($client_id): ?>
                            <p class="description"><?php _e('Current key:', 'majesco-docebo'); ?> <?php echo esc_html($display_id); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Client Secret', 'majesco-docebo'); ?></th>
                    <td>
                        <?php 
                        $client_secret = get_option('majesco_docebo_client_secret');
                        $display_secret = $client_secret ? mask_api_key(decrypt_api_key($client_secret)) : '';
                        ?>
                        <input type="password" 
                               name="majesco_docebo_client_secret" 
                               value="<?php echo esc_attr($display_secret); ?>" 
                               placeholder="<?php esc_attr_e('Enter new Client Secret', 'majesco-docebo'); ?>"
                               required />
                        <?php if ($client_secret): ?>
                            <p class="description"><?php _e('Current key:', 'majesco-docebo'); ?> <?php echo esc_html($display_secret); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Catalog ID', 'majesco-docebo'); ?></th>
                    <td>
                        <input type="number" 
                               name="majesco_docebo_catalog_id" 
                               value="<?php echo esc_attr(get_option('majesco_docebo_catalog_id')); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
        <button class="button button-primary" id="test-auth"><?php _e('Test Authorization', 'majesco-docebo'); ?></button>
        <div id="test-response"></div>
        <button class="button" id="fetch-courses"><?php _e('Fetch Courses', 'majesco-docebo'); ?></button>
        <div id="courses-response"></div>
    </div>
    <?php
}
 
function register_settings() {
    register_setting('majesco-docebo-settings-group', 'majesco_docebo_client_id', [
        'sanitize_callback' => function($value) {
            return encrypt_api_key($value);
        }
    ]);
    register_setting('majesco-docebo-settings-group', 'majesco_docebo_client_secret', [
        'sanitize_callback' => function($value) {
            return encrypt_api_key($value);
        }
    ]);
    register_setting('majesco-docebo-settings-group', 'majesco_docebo_catalog_id');
}
add_action('admin_init', __NAMESPACE__ . '\register_settings');

function authorization($return = false) {
    $client_id = decrypt_api_key(get_option('majesco_docebo_client_id'));
    $client_secret = decrypt_api_key(get_option('majesco_docebo_client_secret'));
    
    if (empty($client_id) || empty($client_secret)) {
        return new \WP_Error('missing_credentials', __('API credentials are not configured.', 'majesco-docebo'));
    }
    
    $response = wp_safe_remote_post(
        'https://majesco.docebosaas.com/oauth2/token',
        array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'client_credentials',
                'scope' => 'api'
            )
        )
    );
    
    $response_body = wp_remote_retrieve_body($response);
    $auth_data = json_decode($response_body, true);
    
    // Store the token as a transient with expiration time
    if (!empty($auth_data['access_token']) && !empty($auth_data['expires_in'])) {
        set_transient('majesco_docebo_access_token', $auth_data['access_token'], $auth_data['expires_in']);
    }	
    
    if( $return ){
        return $response;
    }
    
}
 
function test_authorization() {

    $response = authorization( true );
    
    if( $response['response']['code'] == 200 ){
        echo '<h3>Success!</h3>';
    } else {
        echo '<h3>Error</h3>';
        echo '<pre>' . esc_html($response_body) . '</pre>';
    }
    
    wp_die();
}
add_action('wp_ajax_test_majesco_docebo_authorization', __NAMESPACE__ . '\test_authorization');
 
 
function enqueue_admin_scripts($hook) {
	// Enqueue script only on your plugin's settings page
	if ($hook === 'settings_page_majesco-docebo-settings') {
		wp_enqueue_script('majesco-docebo-script', plugin_dir_url(__FILE__) . 'majesco-docebo.js', array('jquery'), '1.0.0', true);

		$trigger_automatic_fetch = wp_next_scheduled('majesco_docebo_fetch_courses_event') ? 'true' : 'false';
		wp_localize_script('majesco-docebo-script', 'majesco_docebo_vars', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'triggerAutomaticFetch' => $trigger_automatic_fetch,
		));
	}
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_admin_scripts');

function fetch_courses_schedule() {
	if (!wp_next_scheduled('majesco_docebo_fetch_courses_event_cron')) {
		wp_schedule_event(time(), 'twicedaily', 'majesco_docebo_fetch_courses_event_cron');
	}
}
add_action('wp', __NAMESPACE__ . '\fetch_courses_schedule');
add_action( 'majesco_docebo_fetch_courses_event_cron', __NAMESPACE__ . '\fetch_courses_event_callback' );

function fetch_learning_plan_details($item_id, $item_type_int){
	if( $item_type_int != 2 ){
		return '';
	}
	$access_token = get_transient('majesco_docebo_access_token');
	
	if (!$access_token) {
		authorization();
	}
	
	$url = 'https://majesco.docebosaas.com/learn/v1/lp/' . $item_id;
	
	$args = array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $access_token,
		),
	);
	
	$response = wp_safe_remote_get($url, $args);
	
	if (is_wp_error($response)) {
		write_log( $response->get_error_message() );
		return '';
	} else {
		$response_body = wp_remote_retrieve_body($response);
		$lp_data = json_decode($response_body, true);
		
		// Check if we have valid data and courses array
		if (!isset($lp_data['data']['courses']) || !is_array($lp_data['data']['courses'])) {
			write_log('Error fetching Docebo learning plan details. Invalid response format. Learning plan id: ' . $item_id);
			return '';
		}
		
		$lp = $lp_data['data']['courses'];
		if( empty($lp) ){
			write_log('Error fetching Docebo learning plan details. No courses found. Learning plan id: ' . $item_id);
			return '';
		}
		
		return $lp;
	}
	
	return '';
}

function human_readable_duration( $duration = 0 ) {
	if ( ( empty( $duration ) || ! is_integer( $duration ) || $duration < 1 ) ) {
		return false;
	}

	// Extract duration parts.
	$hour = floor($duration / 3600);
	$minute = floor(($duration / 60)%60);

	$human_readable_duration = array();

	// Add the hour part to the string.
	if ( is_numeric( $hour ) && $hour > 0 ) {
		/* translators: %s: Time duration in hour or hours. */
		$human_readable_duration[] = sprintf( _n( '%s HR', '%s HRS', $hour ), (int) $hour );
	}

	// Add the minute part to the string.
	if ( is_numeric( $minute ) && $minute > 0 ) {
		/* translators: %s: Time duration in minute or minutes. */
		$human_readable_duration[] = sprintf( _n( '%s MIN', '%s MINS', $minute ), (int) $minute );
	}

	return implode( ' ', $human_readable_duration );
}

function format_price( $price = '' ){
	if ( ( empty( $price ) || ! is_string( $price ) ) ) {
		return false;
	}
	if ( $price == '0.00' || $price == '0' ) {
		return 'Free';
	}
	$price = trim( $price );
	$price_array = explode( '.', $price );
	return '$' . $price_array[0];
}


function process_majesco_docebo_catalog($catalog) {
	// Create an array to store item IDs of processed posts
	$processed_item_ids = array();

	// Loop through each item in the catalog
	foreach ($catalog as $item) {
		$item_id = $item['item_id'];
		$item_type = $item['item_type'];
		$item_name = $item['item_name'];
		$item_description = $item['item_description'];
		$course_type = $item['course_type'];
		$item_type_int = $item['item_type_int'];
		if( $item_type_int == 1 ){
			$item_duration = human_readable_duration((int)$item['duration']);
		} else {
			$item_duration = human_readable_duration((int)$item['total_duration']); 
		}
		$item_slug = $item['item_slug'];
		$item_price = format_price( $item['item_price'] );
		$item_number_of_courses = $item['number_of_courses'];
		$learning_plan_details = fetch_learning_plan_details($item_id, $item_type_int);

		// Query posts based on the 'item_id' meta value
		$query_args = array(
			'post_type' => 'majesco_course',
			'meta_key' => 'item_id',
			'meta_value' => $item_id,
		);

		$existing_posts_query = new WP_Query($query_args);

		if ($existing_posts_query->have_posts()) {
			// Update existing posts
			while ($existing_posts_query->have_posts()) {
				$existing_posts_query->the_post();
				$post_id = get_the_ID();
				wp_update_post(array(
					'ID' => $post_id,
					'post_content' => $item_description,
				));
				$processed_item_ids[] = $item_id;
			}
		} else {
			// Create new post
			$post_id = wp_insert_post(array(
				'post_type' => 'majesco_course',
				'post_title' => $item_name,
				'post_content' => $item_description,
				'post_status' => 'publish',
			));

			// Set the meta values for the new post
			update_post_meta($post_id, 'item_id', $item_id);
			update_post_meta($post_id, 'item_type', $item_type);
			update_post_meta($post_id, 'item_type_int', $item_type_int);
			update_post_meta($post_id, 'item_slug', $item_slug);
			
			$processed_item_ids[] = $item_id;
		}

		// Set course_type as a term in 'majesco_course_type' taxonomy
		wp_set_object_terms($post_id, array( $course_type ), 'majesco_course_type');
		// Set the meta values for the existing/new post
		update_post_meta($post_id, 'duration', $item_duration); //TODO make a pretty string
		update_post_meta($post_id, 'item_price', $item_price); //TODO make a pretty string
		update_post_meta($post_id, 'number_of_courses', $item_number_of_courses);
		update_post_meta($post_id, 'lp_details', $learning_plan_details);

	}

	// Reset post data
	wp_reset_postdata();

	// Delete posts that are no longer in the catalog
	$existing_posts_query = new WP_Query(array(
		'post_type' => 'majesco_course',
		'meta_key' => 'item_id',
	));

	while ($existing_posts_query->have_posts()) {
		$existing_posts_query->the_post();
		$post_id = get_the_ID();
		$existing_item_id = get_post_meta($post_id, 'item_id', true);

		// If the existing post's item_id is not in the processed_item_ids, delete it
		if (!in_array($existing_item_id, $processed_item_ids)) {
			wp_delete_post($post_id, true); // Force delete
		}
	}

	// Reset post data
	wp_reset_postdata();
}


// Fetch courses function
function fetch_courses_event_callback() {
    $access_token = get_transient('majesco_docebo_access_token');
    $catalog_id = get_option('majesco_docebo_catalog_id');
    
    if (!$access_token) {
        authorization();
    }
    
    $url = 'https://majesco.docebosaas.com/learn/v1/catalog_content/public/' . $catalog_id;
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
        ),
    );
    
    $response = wp_safe_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        write_log($response->get_error_message());
        return;
    }
    
    $response_body = wp_remote_retrieve_body($response);
    $catalog_data = json_decode($response_body, true);
    
    // Validate the response data structure
    if (!isset($catalog_data['data']['items'][0]['data']['sub_items']) || 
        !is_array($catalog_data['data']['items'][0]['data']['sub_items'])) {
        write_log('Error fetching Docebo catalog. Invalid response format.');
        return;
    }
    
    $catalog = $catalog_data['data']['items'][0]['data']['sub_items'];
    
    if (empty($catalog)) {
        write_log('Error fetching Docebo catalog. No courses found.');
        return;
    }
    
    process_majesco_docebo_catalog($catalog);
}
add_action('majesco_docebo_fetch_courses_event', __NAMESPACE__ . '\fetch_courses_event_callback');

function fetch_courses() {
	ob_start();

	fetch_courses_event_callback();

	$response = ob_get_clean();
	wp_send_json_success($response);
}
add_action('wp_ajax_fetch_majesco_docebo_courses', __NAMESPACE__ . '\fetch_courses');

function block_block_init() {
	register_block_type( __DIR__ . '/majesco-docebo-block/build' );

}
add_action( 'init', __NAMESPACE__ . '\block_block_init' );

// Ajax modal 
require_once( MAJESCO_DOCEBO_PLUGIN_DIR_PATH . 'ajax-modal.php' );

// Template functions

function number_of_courses_pill( $post_id ){
	$number_of_courses = (int)get_post_meta( $post_id, 'number_of_courses', true);
	if( $number_of_courses ){
		echo '<h5 class="is-style-pill course-count">' . $number_of_courses . ' Courses</h5>';
	}
}

function duration_pill( $post_id, $label = false ){
	$duration = get_post_meta( $post_id, 'duration', true);
	if( $duration && $label){
		echo '<h5 class="is-style-pill course-duration"><span style="text-transform: capitalize;">Total Duration: </span>' . $duration . '</h5>';
	}
	else if( $duration ){
		echo '<h5 class="is-style-pill course-duration">' . $duration . '</h5>';
	}
}

function price_pill( $post_id ){
	$price = get_post_meta( $post_id, 'item_price', true);
	if( $price ){
		echo '<h5 class="is-style-pill course-price">' . $price . '</h5>';
	}
}

 
if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

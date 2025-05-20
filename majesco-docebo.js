jQuery(document).ready(function($) {
	var ajaxurl = majesco_docebo_vars.ajaxurl;

	$('#fetch-courses').on('click', function() {
		fetchCourses();
	});

	function fetchCourses() {
		$.post(ajaxurl, {
			action: 'fetch_majesco_docebo_courses'
		}, function(response) {
			$('#courses-response').html(response);
		});
	}

	// If the button is clicked automatically (every 12 hours)
	if (majesco_docebo_vars.triggerAutomaticFetch) {
		fetchCourses();
	}
});

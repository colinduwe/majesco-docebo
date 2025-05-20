import { useState, useEffect } from 'react'; // Import the useState hook

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Spinner } from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import Course from './course';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit() {
	const [courses, setCourses] = useState(null); // Initialize courses as null
	// In useSelect callback, set the courses state when data is fetched
	// In useSelect callback, update courses state when data is fetched
	const fetchedCourses = useSelect((select) => {
		return select('core').getEntityRecords('postType', 'majesco_course', {
			per_page: -1,
			_embed: true
		});
	});

	// Use useEffect to update courses state only when fetchedCourses changes
	useEffect(() => {
		if (fetchedCourses) {
			setCourses(fetchedCourses);
		}
	}, [fetchedCourses]);

	// Store the loading state
	const isLoading = !courses;
	// Unconditionally call useBlockProps
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			{isLoading ? (
				<Spinner />
			) : (
			<div className={'container'}>
				<div className={'block-majesco-solution-content__inner load-more-target row row-cols-1 row-cols-md-3 g-4'}>
					{ courses && courses.map( course => (
						<Course key={course.id} course={course} />
					))}
				</div>
				<div className={'row'}>
					<div className={'modal modal-xl fade'} id="majesco_course-modal" tabIndex="-1" aria-labelledby="majesco_courseModalLabel" aria-hidden="true">
						<div className={'modal-dialog'}>
							<div className={'modal-content'} data-object-type="post">
							</div>
						</div>
					</div>
				</div>
			</div>
		)}
		</div>
	);
}

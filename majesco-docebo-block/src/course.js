import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { Spinner } from '@wordpress/components';

const Course = ({ course }) => {
	const articleClasses = ['card', 'h-100', 'majesco-content-card', 'majesco_course', 'type-majesco_course', 'status-publish', 'hentry'];
	const articleId = `post-${course.id}`;

	const articleType = course.meta.item_type == 'learning_plan' ? 'Learning Plan' : 'E-Learning Course';
	const articleTypeClass = course.meta.item_type == 'learning_plan' ? 'is-course-type-learning_plan' : 'is-course-type-elearning';
	const articleTypeClasses = ['is-style-pill', articleTypeClass];

	return (
		<div className={'col'}>
			<article className={articleClasses.join(' ')} id={articleId}>
				<div className={'card-body'}>
					<header className={'entry-header'}>
						<h5 className={articleTypeClasses.join(' ')}>{articleType}</h5>
						<h3 className={'entry-title card-title'}>{course.title.rendered}</h3>
					</header>
					<div className={'entry-content card-text'}>
						<div className={'info-pills'}>
							{course.meta.number_of_courses && (
						    	<h5 className="is-style-pill course-count">{course.meta.number_of_courses} Courses</h5>
							)}
							{course.meta.duration !== '' && (
								<h5 className="is-style-pill course-duration">{course.meta.duration}</h5>
							)}
							{course.meta.item_price !== '' && (
								<h5 className="is-style-pill course-price">{course.meta.item_price}</h5>
							)}
						</div>
						<div className={'wp-block-button is-style-transparent-arrow'}>
							<a className={'wp-block-button__link majesco-2022-read-more-link'} href="" data-bs-toggle="modal" data-bs-target="#majesco_course-modal" data-post-id={course.id} data-post-type="majesco_course">
								Learn More
							</a>
						</div>
					</div>
				</div>
			</article>
		</div>
	);
};

export default Course;

// src/index.js
import { createElement, useState, useEffect } from '@wordpress/element';
import { render } from '@wordpress/element';

/**
 * TestimonialCard – renders a single testimonial.
 * testimonialText may contain HTML (<p> etc.), so we render it as HTML.
 */
const TestimonialCard = ({ name, role, testimonialText, imageUrl }) => {
	const displayName = name || 'Jane Doe';
	const displayRole = role || 'Head of Marketing, Acme Corp';
	const displayText =
		testimonialText ||
		'Working with this team transformed our campaigns. We saw a significant uplift in engagement and conversions within just a few weeks.';

	const altText = displayName
		? `${displayName} portrait`
		: 'Testimonial portrait';

	return (
		<article className="testimonial-card">
			{imageUrl && (
				<div className="testimonial-card__image">
					<img src={imageUrl} alt={altText} />
				</div>
			)}

			<div className="testimonial-card__content">
				<p
					className="testimonial-card__text"
					// PHP already sanitized this via wp_kses_post.
					dangerouslySetInnerHTML={{ __html: displayText }}
				/>
				<p className="testimonial-card__name">
					{displayName}
				</p>
				{displayRole && (
					<p className="testimonial-card__role">
						{displayRole}
					</p>
				)}
			</div>
		</article>
	);
};

/**
 * TestimonialSlider – shows one card at a time with dots navigation.
 */
const TestimonialSlider = ({ testimonials }) => {
	const items = Array.isArray(testimonials) ? testimonials : [];
	const [activeIndex, setActiveIndex] = useState(0);

	// Autoplay: slide every 2 seconds if more than 1 testimonial.
	useEffect(() => {
		if (items.length <= 1) {
			return undefined;
		}

		const intervalId = window.setInterval(() => {
			setActiveIndex((prev) =>
				prev + 1 >= items.length ? 0 : prev + 1
			);
		}, 2000); // 2000ms = 2 seconds

		// Cleanup when component unmounts or deps change.
		return () => {
			window.clearInterval(intervalId);
		};
	}, [items.length, activeIndex]);

	if (!items.length) {
		return null;
	}

	const current = items[Math.min(activeIndex, items.length - 1)];

	return (
		<div className="testimonial-slider">
			<TestimonialCard {...current} />
			{items.length > 1 && (
				<div
					className="testimonial-slider__dots"
					role="tablist"
					aria-label="Testimonial navigation"
				>
					{items.map((item, index) => {
						const isActive = index === activeIndex;
						return (
							<button
								key={index}
								type="button"
								className={
									'testimonial-slider__dot' +
									(isActive
										? ' testimonial-slider__dot--active'
										: '')
								}
								onClick={() => setActiveIndex(index)}
								aria-label={`Go to testimonial ${index + 1}`}
								aria-pressed={isActive}
							/>
						);
					})}
				</div>
			)}
		</div>
	);
};


/**
 * Mounts TestimonialSlider into all wrappers on the page/editor.
 */
const mountTestimonialBlocks = () => {
	const nodes = document.querySelectorAll(
		'.testimonial-card-wrapper[data-testimonial-props]'
	);

	if (!nodes.length) {
		return;
	}

	nodes.forEach((node) => {
		const json = node.getAttribute('data-testimonial-props');
		if (!json) {
			return;
		}

		let props = {};
		try {
			props = JSON.parse(json);
		} catch (e) {
			// Leave props as empty object on parse error.
		}

		const testimonials = Array.isArray(props.testimonials)
			? props.testimonials
			: [];

		render(<TestimonialSlider testimonials={testimonials} />, node);
	});
};

// Initial mount on DOM ready (frontend + editor).
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', mountTestimonialBlocks);
} else {
	mountTestimonialBlocks();
}

// ACF editor: re-mount when preview re-renders.
if (window.acf && typeof window.acf.addAction === 'function') {
	window.acf.addAction(
		'render_block_preview/type=testimonial',
		() => {
			mountTestimonialBlocks();
		}
	);
}

export default TestimonialSlider;

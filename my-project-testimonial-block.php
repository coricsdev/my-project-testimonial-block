<?php
/**
 * Plugin Name: My Project - Testimonial Block
 * Description: A React-powered ACF Testimonial block.
 * Version: 1.0.2
 * Author: Rico Dadiz
 * Author URI: https://dadizrico.com
 * Text Domain: my-project
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register ACF block and field group on acf/init.
 */
add_action( 'acf/init', 'my_project_register_testimonial_block' );

function my_project_register_testimonial_block(): void {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	// Register the ACF block.
	acf_register_block_type(
		array(
			'name'            => 'testimonial',
			'title'           => __( 'Testimonial', 'my-project' ),
			'description'     => __( 'A testimonial card with image, name, role, and quote.', 'my-project' ),
			'category'        => 'formatting',
			'icon'            => 'format-quote',
			'keywords'        => array( 'testimonial', 'quote', 'review' ),
			'render_callback' => 'my_project_render_testimonial_block',
			'mode'            => 'preview', // Show preview by default.
			'supports'        => array(
				'align' => false,
				'mode'  => true,
				'jsx'   => true,
			),
			'enqueue_assets'  => 'my_project_enqueue_testimonial_assets',
		)
	);

	// Register the field group programmatically (you can also do this via UI).
	my_project_register_testimonial_field_group();
}

/**
 * Enqueue block JS and CSS whenever this block appears.
 */
function my_project_enqueue_testimonial_assets(): void {
	$plugin_url = plugin_dir_url( __FILE__ );

	// JS (built via @wordpress/scripts, assumed output path).
	wp_enqueue_script(
		'my-project-testimonial-block-js',
		$plugin_url . 'build/index.js',
		array( 'wp-element' ),
		'1.0.0',
		true
	);

	// CSS.
	wp_enqueue_style(
		'my-project-testimonial-block-css',
		$plugin_url . 'assets/css/testimonial-card.css',
		array(),
		'1.0.0'
	);
}

/**
 * Render callback for the Testimonial block (editor + frontend).
 *
 * @param array      $block      Block settings and attributes.
 * @param string     $content    Block content (empty for ACF blocks).
 * @param bool       $is_preview Whether this is an editor preview.
 * @param int|string $post_id    Post ID.
 */
function my_project_render_testimonial_block( array $block, string $content = '', bool $is_preview = false, $post_id = 0 ): void {
	// Build testimonials array from ACF repeater.
	$testimonials = array();

	if ( function_exists( 'have_rows' ) && have_rows( 'testimonials' ) ) {
		while ( have_rows( 'testimonials' ) ) {
			the_row();

			$name             = (string) get_sub_field( 'name' );
			$role             = (string) get_sub_field( 'role' );
			$testimonial_text = (string) get_sub_field( 'testimonial_text' );
			$image            = get_sub_field( 'image' );

			if ( '' === $name ) {
				$name = __( 'Jane Doe', 'my-project' );
			}

			if ( '' === $role ) {
				$role = __( 'Head of Marketing, Acme Corp', 'my-project' );
			}

			if ( '' === $testimonial_text ) {
				$testimonial_text = __(
					'Working with this team transformed our campaigns. We saw a significant uplift in engagement and conversions within just a few weeks.',
					'my-project'
				);
			}

			// Allow basic HTML but ensure it is safe – this prevents <p> from showing as text.
			$testimonial_text = wp_kses_post( $testimonial_text );

			$image_url = '';
			if ( $image && is_array( $image ) && ! empty( $image['ID'] ) ) {
				$src = wp_get_attachment_image_src( (int) $image['ID'], 'thumbnail' );
				if ( $src && isset( $src[0] ) ) {
					$image_url = (string) $src[0];
				}
			}

			if ( '' === $image_url ) {
				$image_url = plugin_dir_url( __FILE__ ) . 'assets/img/default-avatar.png';
			}

			$testimonials[] = array(
				'name'            => $name,
				'role'            => $role,
				'testimonialText' => $testimonial_text,
				'imageUrl'        => $image_url,
			);
		}
	}

	// Fallback: if no repeater rows, provide a single default testimonial.
	if ( empty( $testimonials ) ) {
		$default_text = __(
			'Working with this team transformed our campaigns. We saw a significant uplift in engagement and conversions within just a few weeks.',
			'my-project'
		);

		$testimonials[] = array(
			'name'            => __( 'Jane Doe', 'my-project' ),
			'role'            => __( 'Head of Marketing, Acme Corp', 'my-project' ),
			'testimonialText' => wp_kses_post( $default_text ),
			'imageUrl'        => plugin_dir_url( __FILE__ ) . 'assets/img/default-avatar.png',
		);
	}

	$data = array(
		'testimonials' => $testimonials,
		'isPreview'    => $is_preview,
	);

	$wrapper_id    = 'testimonial-card-' . ( isset( $block['id'] ) ? $block['id'] : wp_unique_id() );
	$wrapper_class = 'testimonial-card-wrapper';
	if ( ! empty( $block['className'] ) ) {
		$wrapper_class .= ' ' . esc_attr( $block['className'] );
	}

	$json_props = wp_json_encode( $data );
	?>
	<div
		id="<?php echo esc_attr( $wrapper_id ); ?>"
		class="<?php echo esc_attr( $wrapper_class ); ?>"
		data-testimonial-props="<?php echo esc_attr( (string) $json_props ); ?>"
	>
		<!-- Testimonial slider content is rendered by React (editor + frontend). -->
	</div>
	<?php
}

/**
 * Register the local ACF field group for the Testimonial block.
 */
function my_project_register_testimonial_field_group(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_testimonial_block',
			'title' => __( 'Testimonial Block Fields', 'my-project' ),
			'fields' => array(
				array(
					'key'          => 'field_testimonials_repeater',
					'label'        => __( 'Testimonials', 'my-project' ),
					'name'         => 'testimonials',
					'type'         => 'repeater',
					'instructions' => __( 'Add one or more testimonials to display in the slider.', 'my-project' ),

					// Make each item look like a “card”.
					'layout'       => 'block', // block | row | table
					'button_label' => __( 'Add Testimonial', 'my-project' ),

					// Use the name field as the collapsed row label.
					'collapsed'    => 'field_testimonial_name',

					'sub_fields'   => array(
						array(
							'key'           => 'field_testimonial_name',
							'label'         => __( 'Name', 'my-project' ),
							'name'          => 'name',
							'type'          => 'text',
							'wrapper'       => array(
								'width' => '50',
							),
							'default_value' => __( 'Jane Doe', 'my-project' ),
						),
						array(
							'key'           => 'field_testimonial_role',
							'label'         => __( 'Role', 'my-project' ),
							'name'          => 'role',
							'type'          => 'text',
							'wrapper'       => array(
								'width' => '50',
							),
							'default_value' => __( 'Head of Marketing', 'my-project' ),
						),
						array(
							'key'           => 'field_testimonial_text',
							'label'         => __( 'Testimonial Text', 'my-project' ),
							'name'          => 'testimonial_text',
							'type'          => 'textarea',
							'rows'          => 4,
							'new_lines'     => 'wpautop',
							'default_value' => __(
								'Working with this team transformed our campaigns. We saw a significant uplift in engagement and conversions within just a few weeks.',
								'my-project'
							),
						),
						array(
							'key'          => 'field_testimonial_image',
							'label'        => __( 'Image', 'my-project' ),
							'name'         => 'image',
							'type'         => 'image',
							'return_format'=> 'array',
							'preview_size' => 'thumbnail',
							'library'      => 'all',
							'wrapper'      => array(
								'width' => '30',
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'acf/testimonial',
					),
				),
			),
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'active'                => true,
		)
	);
}



/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';
import { RichText } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'swissspidy/animation';

export const settings = {
	title: _x( 'Animation', 'block name', 'animate-block' ),

	description: __( 'Displays an Adobe Animate CC animation.', 'animate-block' ),

	keywords: [
		__( 'animation', 'animate-block' ),
		__( 'animate', 'animate-block' ),
		__( 'adobe', 'animate-block' ),
	],

	icon: 'format-video',

	category: 'embed',

	supports: {
		customClassName: false,
		html: false,
		align: true,
	},

	attributes: {
		id: {
			type: 'number',
		},
		src: {
			type: 'string',
			source: 'attribute',
			selector: 'iframe',
			attribute: 'src',
		},
		caption: {
			type: 'string',
			source: 'html',
			selector: 'figcaption',
		},
	},


	edit,

	save( { attributes } ) {
		const { src, caption } = attributes;
		return (
			<figure
				className="wp-block-embed-animation wp-embed-aspect-16-9 wp-has-aspect-ratio wp-block-embed is-type-animation"
			>
				<div className="wp-block-embed__wrapper">
					{src && (
						<iframe
							src={src}
							frameBorder="0"
						/>
					)}
				</div>
				{!RichText.isEmpty( caption ) && (
					<RichText.Content tagName="figcaption" value={caption}/>
				)}
			</figure>
		);
	},
};

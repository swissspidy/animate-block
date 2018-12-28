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

	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 23"><path fill="#ff4a19" d="M0 0h23v23H0z"/><path fill="#260600" fillRule="evenodd" d="M.957.984h21.086v21.032H.957zm0 0"/><path fill="#ff4a19" fillRule="evenodd" d="M6.422 13.016l-.77 2.937c-.015.078-.047.09-.14.09h-1.41c-.098 0-.114-.031-.098-.148l2.734-9.79c.047-.18.074-.285.094-.777 0-.07.031-.098.078-.098h2.012c.066 0 .098.02.113.098l3.059 10.586c.02.078 0 .129-.078.129h-1.579c-.078 0-.124-.012-.144-.07l-.797-2.957zm2.672-1.625c-.266-1.09-.899-3.457-1.14-4.618h-.02c-.2 1.149-.707 3.086-1.11 4.618zm3.902-1.72c0-.097 0-.44-.05-1.01 0-.071.019-.079.09-.118.804-.305 1.855-.648 2.948-.648 1.352 0 2.829.539 2.829 2.906v5.113c0 .098-.028.129-.118.129h-1.437c-.094 0-.121-.05-.121-.129v-4.973c0-.945-.328-1.464-1.278-1.464-.41 0-.804.078-1.082.175v6.282c0 .066-.027.117-.093.117H13.12c-.078 0-.117-.031-.117-.117V9.672zm0 0"/></svg>,

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

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment, renderToString } from '@wordpress/element';
import { SandBox, withNotices } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { RichText, MediaPlaceholder, mediaUpload } from '@wordpress/editor';
import { getBlobByURL, isBlobURL } from '@wordpress/blob';

/**
 * Internal dependencies
 */
import './editor.css';

const ALLOWED_MEDIA_TYPES = [ 'application/zip', 'application/x-zip-compressed' ];

class Edit extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			editing: !this.props.attributes.src,
		};
	}

	componentDidMount() {
		const { attributes: { id, src = '' }, noticeOperations, setAttributes } = this.props;

		if ( !id && isBlobURL( src ) ) {
			const file = getBlobByURL( src );

			if ( file ) {
				mediaUpload( {
					filesList:    [ file ],
					onFileChange: ( [ media ] ) => {
						setAttributes( { src: media.animation_url } );
					},
					onError:      ( message ) => {
						this.setState( { editing: true } );
						noticeOperations.createErrorNotice( message );
					},
					allowedTypes: ALLOWED_MEDIA_TYPES,
					additionalData: {
						isAnimation: true,
					}
				} );
			}
		}
	}

	render() {
		const { setAttributes, isSelected, className, noticeOperations, noticeUI, attributes: { src, caption } } = this.props;

		const onCaptionChange = ( value ) => setAttributes( { caption: value } );

		const onSelectZip = ( media ) => {
			if ( !media || !media.url ) {
				// in this case there was an error and we should continue in the editing state
				// previous attributes should be removed because they may be temporary blob urls
				setAttributes( { src: undefined, id: undefined } );
				this.setState( { editing: true } );
				return;
			}
			// sets the block's attribute and updates the edit component from the
			// selected media, then switches off the editing UI
			setAttributes( { src: media.animation_url, id: media.id } );
		};

		if ( ! src ) {
			return (
				<Fragment>
					<MediaPlaceholder
						icon="format-video"
						className={className}
						onSelect={onSelectZip}
						accept={ALLOWED_MEDIA_TYPES}
						allowedTypes={ALLOWED_MEDIA_TYPES}
						value={this.props.attributes}
						notices={noticeUI}
						onError={noticeOperations.createErrorNotice}
						labels={{
							title:        __( 'Animation', 'animate-block' ),
							instructions: __( 'Drag a ZIP file, upload a new one or select a file from your library.', 'animate-block' )
						}
						}
					/>
				</Fragment>
			);
		}

		const html = renderToString(
			<iframe
				src={src}
				frameBorder="0"
				style="width: 100%; height: auto;"
			/>
		);

		return (
			<Fragment>
				<figure
					className="wp-block-embed-animation wp-embed-aspect-16-9 wp-has-aspect-ratio wp-block-embed is-type-animation"
				>
					<div className="wp-block-embed__wrapper">
						<SandBox
							html={html}
							title={__( 'Animation', 'animate-block' )}
							type="embed"
						/>
					</div>
					{(!RichText.isEmpty( caption ) || isSelected) && (
						<RichText
							tagName="figcaption"
							placeholder={__( 'Write caption…', 'animate-block' )}
							value={caption}
							onChange={onCaptionChange}
							inlineToolbar
						/>
					)}
				</figure>
			</Fragment>
		);
	}
}

export default compose( [
	withNotices,
	withSelect( ( select ) => {
		const { getThemeSupports } = select( 'core' );
		const themeSupports        = getThemeSupports();

		return {
			themeSupportsResponsive: themeSupports[ 'responsive-embeds' ],
		};
	} ),
] )( Edit );

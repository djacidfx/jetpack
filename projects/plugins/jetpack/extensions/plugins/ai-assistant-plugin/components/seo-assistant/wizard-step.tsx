import { useRef } from '@wordpress/element';
import clsx from 'clsx';
import { default as WizardMessages } from './wizard-messages';

export default function WizardStep( { className = '', messages, visible, onSelect } ) {
	const stepRef = useRef( null );
	const classes = clsx( 'assistant-wizard-step', className );
	return (
		<div ref={ stepRef } className={ classes } style={ { display: visible ? 'block' : 'none' } }>
			<WizardMessages messages={ messages } onSelect={ onSelect } />
		</div>
	);
}

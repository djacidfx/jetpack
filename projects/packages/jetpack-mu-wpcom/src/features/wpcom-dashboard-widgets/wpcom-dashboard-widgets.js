import '../../common/public-path';
import React from 'react';
import ReactDOM from 'react-dom/client';
import CelebrateLaunchModal from './celebrate-launch/celebrate-launch-modal';
import WpcomLaunchpadWidget from './wpcom-launchpad-widget';
import WpcomSiteManagementWidget from './wpcom-site-management-widget';
const data = typeof window === 'object' ? window.JETPACK_MU_WPCOM_DASHBOARD_WIDGETS : {};

const widgets = [
	{
		id: 'wpcom_launchpad_widget_main',
		Widget: WpcomLaunchpadWidget,
	},
	{
		id: 'wpcom_site_preview_widget_main',
		Widget: WpcomSiteManagementWidget,
	},
];

widgets.forEach( ( { id, Widget } ) => {
	const container = document.getElementById( id );
	if ( container ) {
		const root = ReactDOM.createRoot( container );
		root.render( <Widget { ...data } /> );
	}
} );

const url = new URL( window.location.href );
if ( url.searchParams.has( 'celebrate-launch' ) ) {
	url.searchParams.delete( 'celebrate-launch' );
	window.history.replaceState( null, '', url.toString() );
	const rootElement = document.createElement( 'div' );
	document.body.appendChild( rootElement );
	const root = ReactDOM.createRoot( rootElement );
	root.render(
		<CelebrateLaunchModal
			{ ...data }
			onRequestClose={ () => {
				root.unmount();
				rootElement.remove();
			} }
		/>
	);
}

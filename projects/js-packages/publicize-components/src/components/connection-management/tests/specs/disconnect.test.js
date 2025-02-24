import { render, screen } from '@testing-library/react';
import { getManagementPageObject, setup } from '../../../../utils/test-factory';
import { Disconnect } from '../../disconnect';

jest.mock( '../../../../hooks/use-user-can-share-connection', () => ( {
	useUserCanShareConnection: jest.fn( () => true ),
} ) );

describe( 'Disconnecting a connection', () => {
	afterEach( () => {
		jest.clearAllMocks();
	} );

	test( 'disconnecting a connection should call the disconnect method', async () => {
		const { stubDeleteConnectionById } = setup();
		const management = getManagementPageObject();

		const facebookPanel = management.connectionPanels[ 0 ];
		expect( facebookPanel.body ).not.toHaveAttribute( 'inert' );

		await facebookPanel.open();
		expect( facebookPanel.disconnectButton ).toBeEnabled();

		await facebookPanel.disconnectFully();
		expect( stubDeleteConnectionById ).toHaveBeenCalledWith( { connectionId: '2' } );
	} );

	test( 'panel is disabled while updating', async () => {
		setup( { getDeletingConnections: [ '2' ] } );
		const management = getManagementPageObject();

		const facebookPanel = management.connectionPanels[ 0 ];

		expect( facebookPanel.body ).toHaveAttribute( 'inert' );
	} );

	test( 'button changes name and is disabled while updating', async () => {
		setup( { getDeletingConnections: [ '2' ] } );
		render(
			<Disconnect
				connection={ {
					service_name: 'facebook',
					connection_id: '2',
					display_name: 'Facebook',
				} }
			/>
		);

		const button = screen.getByRole( 'button', { name: 'Disconnecting…' } );
		expect( button ).toBeDisabled();
	} );
} );

import { render, screen } from '@testing-library/react';
import NumberSlider from '../index';

describe( 'NumberSlider', () => {
	it( 'renders the number slider', () => {
		render( <NumberSlider /> );
		expect( screen.getByTestId( 'number-slider' ) ).toBeInTheDocument();
	} );
} );

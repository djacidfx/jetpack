const loadIgnorePatterns = require( 'jetpack-js-tools/load-eslint-ignore.js' );

module.exports = {
	ignorePatterns: loadIgnorePatterns( __dirname ),
	rules: {
		'react/forbid-elements': [
			'error',
			{
				forbid: [
					[ 'circle', 'Circle' ],
					[ 'g', 'G' ],
					[ 'path', 'Path' ],
					[ 'polygon', 'Polygon' ],
					[ 'rect', 'Rect' ],
					[ 'svg', 'SVG' ],
				].map( ( [ element, componentName ] ) => ( {
					element,
					message: `use <${ componentName }> from @wordpress/components`,
				} ) ),
			},
		],
		'react/jsx-no-bind': 0,
		'react/react-in-jsx-scope': 0,

		// eslint 6.x migration
		'react-hooks/rules-of-hooks': 1,
		'no-async-promise-executor': 1,

		// Don't require JSDoc on functions.
		// Jetpack Extensions are often self-explanatory functional React components.
		'jsdoc/require-jsdoc': 0,
		'jsdoc/require-returns': 0,
		'jsdoc/require-description': 0,
		'jsdoc/require-property-description': 0,
		'jsdoc/require-param-description': 0,
		'jsdoc/require-param-type': 0,
	},
};

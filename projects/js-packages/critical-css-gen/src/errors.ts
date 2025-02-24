export type MetaType = {
	[ key: string ]: string | number;
};

export type ErrorSpec = {
	message: string;
	type: string;
	meta: MetaType;
};

/**
 * SuccessTargetError - Indicates that insufficient pages loaded to meet
 * a specified success target. Contains information about each error that caused
 * problems and the URLs they affect.
 */
export class SuccessTargetError extends Error {
	public readonly isSuccessTargetError: boolean;
	public readonly urlErrors: { [ url: string ]: ErrorSpec };

	constructor( urlErrors: { [ url: string ]: UrlError } ) {
		super(
			'Insufficient pages loaded to meet success target. Errors:\n' +
				Object.values( urlErrors )
					.map( e => e.message )
					.join( '\n' )
		);

		// Mark this as a SuccessTargetError in an easy way for other code to check.
		this.isSuccessTargetError = true;

		// Convert any Error object into reliable {message,type,meta} objects.
		this.urlErrors = {};
		for ( const [ url, errorObject ] of Object.entries( urlErrors ) ) {
			this.urlErrors[ url ] = {
				message: errorObject.message,
				type: errorObject.type || 'UnknownError',
				meta: errorObject.meta || {},
			};
		}
	}
}

/**
 * Base class for URL specific errors, which can be bundled inside a
 * SuccessTargetError.
 */
export class UrlError extends Error {
	constructor(
		public readonly type: string,
		public readonly meta: MetaType,
		message: string
	) {
		super( message );
	}
}

/**
 * HttpError - Indicates an HTTP request has failed with a non-2xx status code.
 */
export class HttpError extends UrlError {
	constructor( { url, code }: { url: string; code: number } ) {
		super( 'HttpError', { url, code }, `HTTP error ${ code } on URL ${ url }` );
	}
}

/**
 * UnknownError - Indicates that fetch() threw an error with its own error string.
 * Contains a raw (and difficult to translate) error message generated by fetch.
 */
export class UnknownError extends UrlError {
	constructor( { url, message }: { url: string; message: string } ) {
		super( 'UnknownError', { url, message }, `Error while loading ${ url }: ${ message }` );
	}
}

/**
 * CrossDomainError - Indicates that a requested URL failed due to CORS / security
 * limitations imposed by the browser.
 */
export class CrossDomainError extends UrlError {
	constructor( { url }: { url: string } ) {
		super( 'CrossDomainError', { url }, `Failed to fetch cross-domain content at ${ url }` );
	}
}

/**
 * LoadTimeoutError - Indicates that an HTTP request failed due to a timeout.
 */
export class LoadTimeoutError extends UrlError {
	constructor( { url }: { url: string } ) {
		super( 'LoadTimeoutError', { url }, `Timeout while reading ${ url }` );
	}
}

/**
 * RedirectError - Indicates that a requested URL failed due to an HTTP redirection of that url.
 */
export class RedirectError extends UrlError {
	constructor( { url, redirectUrl }: { url: string; redirectUrl: string } ) {
		super(
			'RedirectError',
			{ url, redirectUrl },
			`Failed to process ${ url } because it redirects to ${ redirectUrl } which cannot be verified`
		);
	}
}

/**
 * UrlVerifyError - Indicates that a provided BrowserInterface verifyUrl
 * callback returned false for a page which was otherwise loaded successfully.
 */
export class UrlVerifyError extends UrlError {
	constructor( { url }: { url: string } ) {
		super( 'UrlVerifyError', { url }, `Failed to verify page at ${ url }` );
	}
}

/**
 * EmptyCSSError - Indicates that a requested URL does not have any CSS in its external style sheet(s) and therefore Critical CSS could not be generated.
 */
export class EmptyCSSError extends UrlError {
	constructor( { url }: { url: string } ) {
		super(
			'EmptyCSSError',
			{ url },
			`The ${ url } does not have any CSS in its external style sheet(s).`
		);
	}
}

/**
 * XFrameDenyError - Indicates that a requested URL failed due to x-frame-options deny configuration
 */
export class XFrameDenyError extends UrlError {
	constructor( { url } ) {
		super(
			'XFrameDenyError',
			{ url },
			`Failed to load ${ url } due to the "X-Frame-Options: DENY" header`
		);
	}
}

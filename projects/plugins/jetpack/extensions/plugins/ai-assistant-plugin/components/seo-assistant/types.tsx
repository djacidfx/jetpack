type StepType = 'welcome' | 'input' | 'options' | 'completion';

export interface Message {
	id?: string;
	content: string | React.ReactNode;
	isUser?: boolean;
	showIcon?: boolean;
	type?: string;
	selected?: boolean;
}

export type OptionMessage = Pick< Message, 'id' | 'content' | 'selected' >;

export interface Results {
	[ key: string ]: {
		value: string;
		type: string;
		label: string;
	};
}

export interface Step {
	id: string;
	title: string;
	label?: string;
	messages: Message[];
	type: StepType;
	onStart?: ( options?: { fromSkip: boolean; stepValue: string; results: Results } ) => void;
	onSubmit?: () => Promise< string >;
	onSkip?: () => void;
	value?: string;
	setValue?:
		| React.Dispatch< React.SetStateAction< string > >
		| React.Dispatch< React.SetStateAction< Array< string > > >;
	autoAdvance?: number;
	includeInResults?: boolean;

	// Input step properties
	placeholder?: string;
	rawInput?: string;
	setRawInput?: React.Dispatch< React.SetStateAction< string > >;
	// Options step properties
	options?: OptionMessage[];
	onSelect?: ( option: OptionMessage ) => void;
	submitCtaLabel?: string;
	onRetry?: () => void;
	retryCtaLabel?: string;
	hasSelection?: boolean;
}

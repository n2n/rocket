import { SiInput } from '../input/si-input';
import { SiControlCall } from './si-control-call';
import { SiSortRequest } from './si-sort-request';
import { SiFieldCall } from './si-field-call';

export class SiApiCall {

	constructor(public input?: SiInput|null, public controlCall?: SiControlCall|null, public fieldCall?: SiFieldCall|null,
			public sortCall?: SiSortRequest|null) {
	}

	static sortCall(sortCall: SiSortRequest): SiApiCall {
		return new SiApiCall(undefined, undefined, undefined, sortCall);
	}

	static fieldCall(fieldCall: SiFieldCall): SiApiCall {
		return new SiApiCall(undefined, undefined, fieldCall);
	}

	toJsonStruct(): object {
		return {
			input: this.input?.toJsonStruct(),
			controlCall: this.controlCall,
			fieldCall: this.fieldCall,
			sortCall: this.sortCall
		};
	}
}
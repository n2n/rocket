import { SiInput } from '../input/si-input';
import { SiControlCall } from './si-control-call';
import { SiSortRequest } from './si-sort-request';
import { SiFieldCall } from './si-field-call';
import { SiGetRequest } from './si-get-request';

export class SiApiCall {

	constructor(public input?: SiInput|null, public controlCall?: SiControlCall|null, public fieldCall?: SiFieldCall|null,
			public sortCall?: SiSortRequest|null, public getRequest?: SiGetRequest|null) {
	}

	static fieldCall(fieldCall: SiFieldCall): SiApiCall {
		return new SiApiCall(undefined, undefined, fieldCall);
	}

	static sortCall(sortCall: SiSortRequest): SiApiCall {
		return new SiApiCall(undefined, undefined, undefined, sortCall);
	}

	static getRequest(getRequest: SiGetRequest): SiApiCall {
		return new SiApiCall(undefined, undefined, undefined, undefined, getRequest);
	}

	toJsonStruct(): object {
		return {
			input: this.input?.toJsonStruct(),
			controlCall: this.controlCall,
			fieldCall: this.fieldCall,
			sortCall: this.sortCall,
			getRequest: this.getRequest
		};
	}
}
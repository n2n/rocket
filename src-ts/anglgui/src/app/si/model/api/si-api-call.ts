import { SiInput } from '../input/si-input';
import { SiControlCall } from './si-control-call';
import { SiSortRequest } from './si-sort-request';

export interface SiApiCall {

	input?: SiInput;
	controlCall?: SiControlCall;
	// fieldCall?: SiFieldCall
	sortCall?: SiSortRequest;
}
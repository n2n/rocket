import { SiCallResponse, SiInputResult } from '../../manage/si-control-result';
import { SiFieldCallResponse } from './si-field-call-response';
import { SiGetResponse } from './si-get-response';
import { SiValResponse } from './si-val-response';

export interface SiApiCallResponse  {
	inputResult?: SiInputResult;
	callResponse?: SiCallResponse;
	fieldCallResponse?: SiFieldCallResponse;
	getResponse?: SiGetResponse;
	valResponse?: SiValResponse;
}
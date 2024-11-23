import { SiCallResponse, SiInputResult } from '../../manage/si-control-result';
import { SiFieldCallResponse } from './si-field-call-response';

export interface SiApiCallResponse  {
	inputResult?: SiInputResult;
	callResponse?: SiCallResponse;
	fieldCallResponse?: SiFieldCallResponse;
}
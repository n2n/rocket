import { SiCallResponse, SiInputResult } from '../../manage/si-control-result';

export interface SiApiCallResponse  {
	inputResult?: SiInputResult;
	callResponse?: SiCallResponse;
}
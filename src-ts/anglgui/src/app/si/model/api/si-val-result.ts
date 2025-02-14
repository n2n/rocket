import { SiValGetResult } from './si-val-get-result';
import { SiValueBoundary } from '../content/si-value-boundary';

export class SiValResult {
	readonly getResults: SiValGetResult[] = [];

	constructor(public valid: boolean, public valueBoundary: SiValueBoundary) {
	}
}

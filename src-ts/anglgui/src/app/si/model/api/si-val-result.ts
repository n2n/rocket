import { SiValGetResult } from './si-val-get-result';
import { SiEntry } from '../content/si-entry';

export class SiValResult {
	public errorEntry: SiEntry|null = null;
	readonly getResults: SiValGetResult[] = [];
}

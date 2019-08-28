import { SiEntryError } from '../input/si-entry-error';
import { SiValGetResult } from './si-val-get-result';

export class SiValResult {
	public entryError: SiEntryError|null = null;
	readonly getResults: SiValGetResult[] = [];
}

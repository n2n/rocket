import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { EmbeInSource } from './embe-collection';

export class SiPanel implements EmbeddedEntriesConfig, EmbeInSource {
	values: SiEmbeddedEntry[] = [];
	allowedSiTypeQualifiers: SiTypeQualifier[]|null = null;
	min = 0;
	max: number|null = null;
	gridPos: SiGridPos|null = null;
	nonNewRemovable = true;
	sortable = false;
	reduced = true;

	constructor(public name: string, public label: string) {
	}

	setValues(values: SiEmbeddedEntry[]): void {
		this.values = values;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}
}

export interface SiGridPos {
	colStart: number;
	colEnd: number;
	rowStart: number;
	rowEnd: number;
}

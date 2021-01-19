import { SiEmbeddedEntry } from './si-embedded-entry';
import { EmbeddedEntriesInConfig } from './embe/embedded-entries-config';
import { EmbeInSource } from './embe/embe-collection';

export class SiPanel implements EmbeddedEntriesInConfig, EmbeInSource {
	values: SiEmbeddedEntry[] = [];
	allowedTypeIds: string[]|null = null;
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

	readInput(): object {
		return {
			'name': this.name,
			'entryInputs': this.values.map(embe => embe.entry.readInput())
		};
	}
}

export interface SiGridPos {
	colStart: number;
	colEnd: number;
	rowStart: number;
	rowEnd: number;
}

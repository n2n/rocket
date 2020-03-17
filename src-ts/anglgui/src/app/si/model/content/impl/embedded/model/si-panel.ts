import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { EmbeddedEntriesConfig } from './embedded-entries-config';

export class SiPanel implements EmbeddedEntriesConfig {

	constructor(public name: string, public label: string) {
	}

	values: SiEmbeddedEntry[] = [];
	allowedSiTypeQualifiers: SiTypeQualifier[]|null = null;
	min = 0;
	max: number|null = null;
	gridPos: SiGridPos|null = null;
	nonNewRemovable = true;
	sortable = false;
	reduced = true;
}

export interface SiGridPos {
	colStart: number;
	colEnd: number;
	rowStart: number;
	rowEnd: number;
}

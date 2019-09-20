import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiType } from '../../si-type';

export class SiPanel {
	label: string;
	embeddedEntries: SiEmbeddedEntry[] = [];
	allowedSiTypes: SiType[]|null = null;
	min = 0;
	max: number|null = null;
	gridPos: SiGridPos|null = null;
}

export interface SiGridPos {
	colStart: number;
	colEnd: number;
	rowStart: number;
	rowEnd: number;
}

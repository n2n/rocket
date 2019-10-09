import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry|null = null,
			public uiStructure: UiStructure|null = null,
			public summaryUiStructure: UiStructure|null = null) {
	}

	isPlaceholder(): boolean {
		return !this.siEmbeddedEntry;
	}

	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}

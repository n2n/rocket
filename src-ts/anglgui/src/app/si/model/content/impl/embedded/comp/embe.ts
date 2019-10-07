import { SiEmbeddedEntry } from 'src/app/si/model/content/impl/embedded/si-embedded-entry';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiEntry } from 'src/app/si/model/content/si-entry';

export class Embe {
	constructor(public siEmbeddedEntry: SiEmbeddedEntry|null = null,
			public uiStructure: UiStructure|null = null,
			public summarySiStructure: UiStructure|null = null) {
	}

	isPlaceholder(): boolean {
		return !this.siEmbeddedEntry;
	}

	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}

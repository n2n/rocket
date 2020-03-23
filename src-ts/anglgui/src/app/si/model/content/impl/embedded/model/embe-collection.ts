import { Embe } from './embe';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';


export interface EmbeSource {
	getValues(): SiEmbeddedEntry[];
}
export interface EmbeInSource extends EmbeSource {
	setValues(values: SiEmbeddedEntry[]): void;
}

export class EmbeCollection {
	public embes: Embe[] = [];

	constructor(private source: EmbeSource, protected getUiStructure: () => UiStructure) {
	}

	// protected unregisterEmbe(embe: Embe) {
	// }

	// initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry): Embe {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;

	// 	return embe;
	// }

	removeEmbes() {
		let embe: Embe;
		// tslint:disable-next-line: no-conditional-assignment
		while (undefined !== (embe = this.embes.pop())) {
			embe.clear();
			// this.unregisterEmbe(embe);
		}
	}

	createEmbe(siEmbeddedEntry: SiEmbeddedEntry|null = null): Embe {
		const embe = new Embe(siEmbeddedEntry, this.getUiStructure);
		this.embes.push(embe);
		return embe;
	}

	readEmbes() {
		this.removeEmbes();

		for (const siEmbeddedEntry of this.source.getValues()) {
			this.createEmbe(siEmbeddedEntry);
		}
	}
}

export class EmbeInCollection extends EmbeCollection {
	constructor(private inSource: EmbeInSource, getUiStructure: () => UiStructure, private config: EmbeddedEntriesConfig) {
		super(inSource, getUiStructure);
	}

	createEntriesResetPoints(): SiGenericEntry[] {
		const entries: SiGenericEntry[] = [];
		for (const embe of this.embes) {
			entries.push(embe.siEntry.createResetPoint());
		}
		return entries;
	}

	writeEmbes() {
		const values = new Array<SiEmbeddedEntry>();

		for (const embe of this.embes) {
			if (embe.isPlaceholder()) {
				continue;
			}

			values.push(embe.siEmbeddedEntry);
		}

		this.inSource.setValues(values);
	}

	// fillWithPlaceholderEmbes() {
	// 	if (!this.config.allowedSiTypeQualifiers) {
	// 		return;
	// 	}

	// 	const min = this.config.min;
	// 	while (this.embes.length < min) {
	// 		this.createEmbe();
	// 	}
	// }

	removeEmbe(embe: Embe) {
		const i = this.embes.indexOf(embe);
		if (i < 0) {
			throw new Error('Unknown Embe');
		}

		this.embes.splice(i, 1);
		embe.clear();
		// this.unregisterEmbe(embe);
	}

	changeEmbePosition(oldIndex: number, newIndex: number) {
		const moveEmbe = this.embes[oldIndex];

		if (oldIndex < newIndex) {
			for (let i = oldIndex; i < newIndex; i++) {
				this.embes[i] = this.embes[i + 1];
			}
		}

		if (oldIndex < newIndex) {
			for (let i = oldIndex; i > newIndex; i--) {
				this.embes[i] = this.embes[i - 1];
			}
		}

		this.embes[newIndex] = moveEmbe;
	}
}

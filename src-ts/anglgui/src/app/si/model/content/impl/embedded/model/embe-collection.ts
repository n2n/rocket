import { Embe } from './embe';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';



export class EmbeCollection {
	public embes: Embe[] = [];

	constructor(protected values: SiEmbeddedEntry[], protected getUiStructure: () => UiStructure) {
	}

	// protected unregisterEmbe(embe: Embe) {
	// }

	// initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry): Embe {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;

	// 	return embe;
	// }

	clearEmbes() {
		let embe: Embe;
		// tslint:disable-next-line: no-conditional-assignment
		while (undefined !== (embe = this.embes.pop())) {
			// this.unregisterEmbe(embe);
		}
	}

	createEmbe(siEmbeddedEntry: SiEmbeddedEntry|null = null): Embe {
		const embe = new Embe(siEmbeddedEntry, this.getUiStructure);
		this.embes.push(embe);
		return embe;
	}

	readEmbes() {
		this.clearEmbes();

		for (const siEmbeddedEntry of this.values) {
			this.createEmbe(siEmbeddedEntry);
		}
	}
}

export class EmbeInCollection extends EmbeCollection {
	constructor(values: SiEmbeddedEntry[], getUiStructure: () => UiStructure, private config: EmbeddedEntriesConfig) {
		super(values, getUiStructure);
	}

	createEntriesResetPoints(): SiGenericEntry[] {
		const entries: SiGenericEntry[] = [];
		for (const embe of this.embes) {
			entries.push(embe.siEntry.createResetPoint());
		}
		return entries;
	}

	writeEmbes() {
		this.values.splice(0, this.values.length);

		for (const embe of this.embes) {
			if (embe.isPlaceholder()) {
				continue;
			}

			this.values.push(embe.siEmbeddedEntry);
		}
	}

	fillWithPlaceholderEmbes() {
		if (!this.config.allowedSiTypeQualifiers) {
			return;
		}

		const min = this.config.min;
		while (this.embes.length < min) {
			this.createEmbe();
		}
	}

	removeEmbe(embe: Embe) {
		const i = this.embes.indexOf(embe);
		if (i < 0) {
			throw new Error('Unknown Embe');
		}

		this.embes.splice(i, 1);
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

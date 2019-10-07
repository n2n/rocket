import { Embe } from './embe';
import { EmbeddedEntryModel } from './embedded-entry-model';
import { EmbeddedEntriesInModel } from './embedded-entry-in-model';
import { SiEmbeddedEntry } from 'src/app/si/model/content/impl/embedded/si-embedded-entry';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiEntry } from 'src/app/si/model/content/si-entry';

export class EmbeCollection {
	public embes: Embe[] = [];

	constructor(private parentSiStructure: UiStructure, private model: EmbeddedEntryModel) {
	}

	protected unregisterEmbe(embe: Embe) {
		if (embe.uiStructure) {
			embe.uiStructure.dispose();
			embe.uiStructure = null;
		}

		if (embe.summarySiStructure) {
			embe.summarySiStructure.dispose();
			embe.summarySiStructure = null;
		}
	}

	initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		const uiStructure = this.parentSiStructure.createChild(null, null, siEmbeddedEntry.comp);
		const summarySiStructure = (siEmbeddedEntry.summaryComp
				? this.parentSiStructure.createChild(null, null, siEmbeddedEntry.summaryComp)
				: null);

		// if (this.reduced) {
		// 	siEmbeddedEntry.comp.controls = [
		// 		new SimpleSiControl(
		// 				new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-save'),
		// 				() => { this.apply(); }),
		// 		new SimpleSiControl(
		// 				new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-trash-restore-alt'),
		// 				() => { this.cancel(); })
		// 	];
		// }

		embe.siEmbeddedEntry = siEmbeddedEntry;
		embe.uiStructure = uiStructure;
		embe.summarySiStructure = summarySiStructure;

		return embe;
	}

	clearEmbes() {
		let embe: Embe;
		// tslint:disable-next-line: no-conditional-assignment
		while (undefined !== (embe = this.embes.pop())) {
			this.unregisterEmbe(embe);
		}
	}

	createEmbe(): Embe {
		const embe = new Embe();
		this.embes.push(embe);
		return embe;
	}

	readEmbes() {
		this.clearEmbes();

		for (const siEmbeddedEntry of this.model.getValues()) {
			this.initEmbe(this.createEmbe(), siEmbeddedEntry);
		}
	}
}

export class EmbedInCollection extends EmbeCollection {
	constructor(parentSiStructure: UiStructure, private inModel: EmbeddedEntriesInModel) {
		super(parentSiStructure, inModel);
	}

	copyEntries(): SiEntry[] {
		const entries: SiEntry[] = [];
		for (const embe of this.embes) {
			entries.push(embe.siEntry.copy());
		}
		return entries;
	}

	writeEmbes() {
		const values: SiEmbeddedEntry[] = [];

		for (const embe of this.embes) {
			if (embe.isPlaceholder()) {
				continue;
			}

			values.push(embe.siEmbeddedEntry);
		}

		this.inModel.setValues(values);
	}

	fillWithPlaceholderEmbes() {
		if (!this.inModel.getAllowedSiTypeQualifiers()) {
			return;
		}

		const min = this.inModel.getMin();
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
		this.unregisterEmbe(embe);
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

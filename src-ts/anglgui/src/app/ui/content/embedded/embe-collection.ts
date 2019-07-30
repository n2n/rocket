import { Embe } from './embe';
import { EmbeddedEntryModel } from './embedded-entry-model';
import { EmbeddedEntryInModel } from './embedded-entry-in-model';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiStructure } from 'src/app/si/model/structure/si-structure';

export class EmbeCollection {
	public embes: Embe[] = [];

	constructor(private model: EmbeddedEntryModel) {
	}

	unregisterEmbe(embe: Embe) {
		if (embe.siStructure) {
			this.model.unregisterSiStructure(embe.siStructure);
		}

		if (embe.summarySiStructure) {
			this.model.unregisterSiStructure(embe.siStructure);
		}
	}

	initEmbe(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		const siStructure = new SiStructure(null, null, siEmbeddedEntry.comp);
		const summarySiStructure = (siEmbeddedEntry.summaryComp ? new SiStructure(null, null, siEmbeddedEntry.summaryComp) : null);

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
		embe.siStructure = siStructure;
		embe.summarySiStructure = summarySiStructure;

		this.model.registerSiStructure(siStructure);
		this.model.registerSiStructure(summarySiStructure);

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
	constructor(private inModel: EmbeddedEntryInModel) {
		super(inModel);
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

	fillWithPlaceholerEmbes() {
		if (!this.inModel.getAllowedSiTypes()) {
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

import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiEmbeddedEntry } from '../model/si-embedded-entry';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { skip } from 'rxjs/operators';

export class Embe {
	private _siEmbeddedEntry: SiEmbeddedEntry;
	uiStructureModel: UiStructureModel|null = null;
	summaryUiStructureModel: UiStructureModel|null = null;

	private _uiStructure: UiStructure|null = null;
	private _summaryUiStructure: UiStructure|null = null;

	constructor(siEmbeddedEntry: SiEmbeddedEntry|null = null, protected getUiStructure: () => UiStructure|null) {
		if (siEmbeddedEntry) {
			this.siEmbeddedEntry = siEmbeddedEntry;
		}
	}

	get siEmbeddedEntry(): SiEmbeddedEntry {
		return this._siEmbeddedEntry;
	}

	set siEmbeddedEntry(siEmbeddedEntry: SiEmbeddedEntry) {
		IllegalStateError.assertTrue(!this._siEmbeddedEntry);

		this._siEmbeddedEntry = siEmbeddedEntry;
		this.uiStructureModel = siEmbeddedEntry.comp.createUiStructureModel();
		if (siEmbeddedEntry.summaryComp) {
			this.summaryUiStructureModel = siEmbeddedEntry.summaryComp.createUiStructureModel();
		}
	}

	isPlaceholder(): boolean {
		return !this._siEmbeddedEntry;
	}

	get siEntry(): SiEntry {
		IllegalSiStateError.assertTrue(!!this._siEmbeddedEntry);
		return this._siEmbeddedEntry.entry;
	}

	get uiStructure(): UiStructure {
		if (this._uiStructure) {
			IllegalStateError.assertTrue(this._uiStructure.parent === this.getUiStructure());
			return this._uiStructure;
		}

		this._uiStructure = this.getUiStructure().createChild(null, null, this.uiStructureModel);

		this._uiStructure.disposed$.pipe(skip(1)).subscribe(() => {
			this._uiStructure = null;
		});

		return this._uiStructure;
	}

	get summaryUiStructure(): UiStructure {
		if (this._summaryUiStructure) {
			IllegalStateError.assertTrue(this._summaryUiStructure.parent === this.getUiStructure());
			return this._summaryUiStructure;
		}

		this._summaryUiStructure = this.getUiStructure().createChild(null, null, this.summaryUiStructureModel);

		this._summaryUiStructure.disposed$.pipe(skip(1)).subscribe(() => {
			this._summaryUiStructure = null;
		});

		return this._summaryUiStructure;
	}
}

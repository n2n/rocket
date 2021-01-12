import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { skip } from 'rxjs/operators';
import { SiEmbeddedEntry } from '../si-embedded-entry';

export class Embe {
	private _siEmbeddedEntry: SiEmbeddedEntry;
	public uiStructureModel: UiStructureModel|null = null;
	public summaryUiStructureModel: UiStructureModel|null = null;

	private _uiStructure: UiStructure|null = null;
	private _summaryUiStructure: UiStructure|null = null;

	constructor(siEmbeddedEntry: SiEmbeddedEntry|null = null, protected getUiStructure: () => UiStructure|null) {
		if (siEmbeddedEntry) {
			this.siEmbeddedEntry = siEmbeddedEntry;
		}
	}

	get siEmbeddedEntry(): SiEmbeddedEntry|null {
		return this._siEmbeddedEntry;
	}

	set siEmbeddedEntry(siEmbeddedEntry: SiEmbeddedEntry|null) {
		if (!siEmbeddedEntry) {
			this.clear();
			return;
		}
		IllegalStateError.assertTrue(!this._siEmbeddedEntry);

		this._siEmbeddedEntry = siEmbeddedEntry;
		this.uiStructureModel = siEmbeddedEntry.comp.createUiStructureModel();
		if (siEmbeddedEntry.summaryComp) {
			this.summaryUiStructureModel = siEmbeddedEntry.summaryComp.createUiStructureModel();
		}
	}

	clear() {
		this._siEmbeddedEntry = null;
		this.uiStructureModel = null;
		this.summaryUiStructureModel = null;

		if (this._uiStructure) {
			this._uiStructure.dispose();
			this._uiStructure = null;
		}

		if (this._summaryUiStructure) {
			this._summaryUiStructure.dispose();
			this._summaryUiStructure = null;
		}
	}

	isPlaceholder(): boolean {
		return !this._siEmbeddedEntry;
	}

	isTypeSelected(): boolean {
		return !!this._siEmbeddedEntry.entry.selectedTypeId;
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
		this._uiStructure.model = this.uiStructureModel;

		this._uiStructure.disposed$.pipe(skip(1)).subscribe(() => {
			this._uiStructure = null;
		});

		return this._uiStructure;
	}

	get summaryUiStructure(): UiStructure {
		IllegalStateError.assertTrue(!!this.summaryUiStructureModel);

		if (this._summaryUiStructure) {
			IllegalStateError.assertTrue(this._summaryUiStructure.parent === this.getUiStructure());
			return this._summaryUiStructure;
		}

		this._summaryUiStructure = this.getUiStructure().createChild();
		this._summaryUiStructure.model = this.summaryUiStructureModel;

		this._summaryUiStructure.disposed$.pipe(skip(1)).subscribe(() => {
			this._summaryUiStructure = null;
		});

		return this._summaryUiStructure;
	}
}

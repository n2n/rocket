import { SiEntry } from 'src/app/si/model/content/si-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { SiEmbeddedEntry } from '../si-embedded-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';

export class Embe {
	private _siEmbeddedEntry: SiEmbeddedEntry;
	public uiStructure = new UiStructure(UiStructureType.SIMPLE_GROUP);
	public summaryUiStructure = new UiStructure();

	constructor(siEmbeddedEntry: SiEmbeddedEntry|null = null) {
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
		this.uiStructure.model = siEmbeddedEntry.comp.createUiStructureModel();
		if (siEmbeddedEntry.summaryComp) {
			this.summaryUiStructure.model = siEmbeddedEntry.summaryComp.createUiStructureModel();
		}
	}

	clear() {
		this._siEmbeddedEntry = null;
		this.uiStructure.model = null;
		this.summaryUiStructure.model = null;
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

	// get uiStructure(): UiStructure {
	// 	if (this._uiStructure) {
	// 		IllegalStateError.assertTrue(this._uiStructure.parent === this.getUiStructure());
	// 		return this._uiStructure;
	// 	}

	// 	this._uiStructure = this.getUiStructure().createChild(null, null, this.uiStructureModel);
	// 	this._uiStructure.model = this.uiStructureModel;

	// 	this._uiStructure.disposed$.pipe(filter(d => d)).subscribe(() => {
	// 		this._uiStructure = null;
	// 	});

	// 	return this._uiStructure;
	// }

	// get summaryUiStructure(): UiStructure {
	// 	IllegalStateError.assertTrue(!!this.summaryUiStructureModel);

	// 	if (this._summaryUiStructure) {
	// 		IllegalStateError.assertTrue(this._summaryUiStructure.parent === this.getUiStructure());
	// 		return this._summaryUiStructure;
	// 	}

	// 	this._summaryUiStructure = this.getUiStructure().createChild();
	// 	this._summaryUiStructure.model = this.summaryUiStructureModel;
	// 	// this._summaryUiStructure.compact = true;

	// 	this._summaryUiStructure.disposed$.pipe(filter(d => d)).subscribe(() => {
	// 		this._summaryUiStructure = null;
	// 	});

	// 	return this._summaryUiStructure;
	// }
}

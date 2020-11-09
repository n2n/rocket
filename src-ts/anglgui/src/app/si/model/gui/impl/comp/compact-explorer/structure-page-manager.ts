import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { CompactExplorerComponent } from './compact-explorer.component';
import { SiEntry, SiEntryState } from 'src/app/si/model/content/si-entry';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { Subscription } from 'rxjs';
import { IllegalArgumentError } from 'src/app/si/util/illegal-argument-error';

export class StructurePage {
	private _structureEntries = new Array<StructureEntry>();

	constructor(readonly siPage: SiPage) {
	}

	get loaded(): boolean {
		return !!this.siPage.entries;
	}

	isEmpty(): boolean {
		return this._structureEntries.length === 0;
	}

	clear() {
		let structureEntry: StructureEntry;
		while (structureEntry = this._structureEntries.pop()) {
			structureEntry.clear();
		}
	}

	get structureEntries(): Array<StructureEntry> {
		return this._structureEntries;
	}

	appendStructureEntry(structureEntry: StructureEntry) {
		this._structureEntries.push(structureEntry);
	}

	insertStructureEntryAt(index: number, structureEntry: StructureEntry) {
		if (this._structureEntries.length < index) {
			throw new IllegalArgumentError('Index out of bounds: ' + index + '; current page size: ' + this._structureEntries.length);
		}

		if (this._structureEntries[index]) {
			this._structureEntries[index].clear();
		}

		this._structureEntries[index] = structureEntry;
	}

	replaceStructureEntry(structureEntry: StructureEntry, replacementStructureEntry: StructureEntry): boolean {
		const i = this._structureEntries.indexOf(structureEntry);
		if (i > -1) {
			this.insertStructureEntryAt(i, replacementStructureEntry);
			return true;
		}

		return false;
	}

	// putStructureEntry(siEntry: SiEntry, structureEntry: StructureEntry) {
	// 	this.structureEntriesMap.set(siEntry, structureEntry);
	// }

	// getStructureEntryOf(siEntry: SiEntry) {
	// 	if (this.structureEntriesMap.has(siEntry)) {
	// 		return this.structureEntriesMap.get(siEntry);
	// 	}

	// 	throw new IllegalStateError('No StructureEntry available for ' + siEntry.identifier.toString());
	// }
}

export class StructureEntry {
	private subscription: Subscription;

	constructor(readonly siEntry: SiEntry, public fieldUiStructures: Array<UiStructure>, public controlUiContents: Array<UiContent>,
			private replacementCallback: (replacementEntry: SiEntry) => any) {

		this.subscription = siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					this.replacementCallback(siEntry.replacementEntry);
					this.clear();
					break;
				case SiEntryState.REMOVED:
					this.clearControls();
					this.clearSubscription();
			}
		});
	}

	clear() {
		this.clearFields();
		this.clearControls();
		this.clearSubscription();
	}

	private clearFields() {
		for (const uiStructure of this.fieldUiStructures) {
			uiStructure.dispose();
		}
		this.fieldUiStructures = [];
	}

	private clearControls() {
		this.controlUiContents = [];
	}

	private clearSubscription() {
		if (!this.subscription) {
			return;
		}

		this.subscription.unsubscribe();
		this.subscription = null;
	}
}

export class StructurePageManager {
	private pagesMap = new Map<number, StructurePage>();

	constructor(private comp: CompactExplorerComponent) {

	}

	map(siPages: SiPage[]) {
		const structurePages = new Array<StructurePage>();

		for (const siPage of siPages) {
			let structurePage = this.getPage(siPage);
			if (!structurePage) {
				structurePage = this.createPage(siPage);
			}

			this.val(structurePage);
			structurePages.push(structurePage);
		}

		return structurePages;
	}

	clear() {
		for (const [, structurePage] of this.pagesMap) {
			structurePage.clear();
		}
		this.pagesMap.clear();
	}

	private getPage(siPage: SiPage): StructurePage|null {
		const structurePage = this.pagesMap.get(siPage.no);
		if (!structurePage || structurePage.siPage === siPage) {
			return structurePage;
		}

		this.pagesMap.delete(siPage.no);
		structurePage.clear();
		return null;
	}

	private createPage(siPage: SiPage): StructurePage {
		const sp = new StructurePage(siPage);
		this.pagesMap.set(siPage.no, sp);
		return sp;
	}

	private val(structurePage: StructurePage) {
		if (!structurePage.isEmpty() || !this.comp.siPageCollection.declaration
				|| !structurePage.siPage.entries) {
			return;
		}

		for (const siEntry of structurePage.siPage.entries) {
			this.applyNewStructureEntry(structurePage, siEntry, null, null);
		}
	}

	private applyNewStructureEntry(structurePage: StructurePage, siEntry: SiEntry, oldStructureEntry: StructureEntry|null,
			insertIndex: number|null) {
		const fieldUiStructures = this.createFieldUiStructures(siEntry);
		const controlUiContents = siEntry.selectedEntryBuildup.controls
				.map(siControl => siControl.createUiContent(this.comp.uiStructure.getZone()));

		const structureEntry = new StructureEntry(siEntry, fieldUiStructures, controlUiContents, (replacementEntry) => {
			this.applyNewStructureEntry(structurePage, replacementEntry, structureEntry, null);
		});

		if (oldStructureEntry) {
			if (!structurePage.replaceStructureEntry(oldStructureEntry, structureEntry)) {
				structureEntry.clear();
			}

			return;
		}

		if (insertIndex !== null && insertIndex !== undefined) {
			structurePage.insertStructureEntryAt(insertIndex, structureEntry);
			return;
		}

		structurePage.appendStructureEntry(structureEntry);
	}

	private createFieldUiStructures(siEntry: SiEntry): UiStructure[] {
		const uiStructures = new Array<UiStructure>();

		for (const siProp of this.comp.getSiProps()) {
			const uiStructure = this.comp.uiStructure.createChild();
			uiStructure.model = siEntry.selectedEntryBuildup.getFieldById(siProp.id).createUiStructureModel();
			uiStructures.push(uiStructure);
		}

		return uiStructures;
	}
}



// import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
// import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
// import { EmbeddedEntryPanelInModel } from '../embedded-entry-panels-in-model';
// import { SiPanel } from '../../model/si-panel';
// import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
// import { AddPasteObtainer } from '../add-paste-obtainer';
// import { EmbeInCollection } from '../../model/embe-collection';

// export class PanelEmbeddedEntryInModel implements EmbeddedEntriesInModel {

// 	constructor(private panel: SiPanel, private model: EmbeddedEntryPanelInModel) {
// 		this.embeInCol = new EmbeInCollection(panel.values);
// 	}

// 	isNonNewRemovable(): boolean {
// 		return true;
// 	}

// 	isSortable(): boolean {
// 		return this.panel.sortable;
// 	}

// 	isSummaryRequired(): boolean {
// 		return true;
// 	}

// 	getTypeCategory(): string {
// 		return this.panel.pasteCategory;
// 	}

// 	getAllowedSiTypeQualifiers(): SiTypeQualifier[] {
// 		return this.panel.allowedSiTypeQualifiers;
// 	}

// 	setValues(values: SiEmbeddedEntry[]): void {
// 		this.panel.values = values;
// 	}

// 	getValues(): SiEmbeddedEntry[] {
// 		return this.panel.values;
// 	}

// 	getMin(): number {
// 		return this.panel.min;
// 	}

// 	getMax(): number|null {
// 		return this.panel.max;
// 	}

// 	getAddPasteObtainer(): AddPasteObtainer {
// 		return this.model.getObtainer();
// 	}

// 	getEmbeInCollection(): EmbeInCollection {
// 		return this.model.g
// 	}
// 	open(embe: import("../../model/embe").Embe): void {
// 		throw new Error("Method not implemented.");
// 	}
// 	openAll(): void {
// 		throw new Error("Method not implemented.");
// 	}
// }

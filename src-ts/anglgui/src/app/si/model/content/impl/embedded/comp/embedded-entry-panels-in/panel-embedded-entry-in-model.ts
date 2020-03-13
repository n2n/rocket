import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { EmbeddedEntryPanelInModel } from '../embedded-entry-panels-in-model';
import { SiPanel } from '../../model/si-panel';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { AddPasteObtainer } from '../add-paste-obtainer';

export class PanelEmbeddedEntryInModel implements EmbeddedEntriesInModel {

	constructor(private panel: SiPanel, private model: EmbeddedEntryPanelInModel) {

	}

	isNonNewRemovable(): boolean {
		return true;
	}

	isSortable(): boolean {
		return this.panel.sortable;
	}

	isSummaryRequired(): boolean {
		return true;
	}

	getTypeCategory(): string {
		return this.panel.pasteCategory;
	}

	getAllowedSiTypeQualifiers(): SiTypeQualifier[] {
		return this.panel.allowedSiTypeQualifiers;
	}

	setValues(values: SiEmbeddedEntry[]): void {
		this.panel.values = values;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.panel.values;
	}

	getMin(): number {
		return this.panel.min;
	}

	getMax(): number|null {
		return this.panel.max;
	}

	getObtainer(): AddPasteObtainer {
		return this.model.getObtainer();
	}
}

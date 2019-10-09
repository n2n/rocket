import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { SiPanel } from '../../model/si-panel';
import { EmbeddedEntryPanelModel } from '../embedded-entry-panels-model';
import { SiType } from 'src/app/si/model/meta/si-type';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';

export class PanelEmbeddedEntryInModel implements EmbeddedEntriesInModel {

	constructor(private panel: SiPanel, private model: EmbeddedEntryPanelModel) {

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

	getPastCategory(): string {
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

	getApiUrl(): string {
		return this.model.getApiUrl();
	}

	getMin(): number {
		return this.panel.min;
	}

	getMax(): number|null {
		return this.panel.max;
	}
}

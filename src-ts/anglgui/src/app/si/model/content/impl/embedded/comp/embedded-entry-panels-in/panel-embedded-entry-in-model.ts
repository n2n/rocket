import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { SiType } from 'src/app/si/model/entity/si-type';
import { EmbeddedEntryPanelModel } from '../../embedded-entry-panels-model';
import { SiPanel } from 'src/app/si/model/entity/impl/embedded/si-panel';
import { EmbeddedEntriesInModel } from '../../embedded-entry-in-model';

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

	getAllowedSiTypes(): SiType[] {
		return this.panel.allowedSiTypes;
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

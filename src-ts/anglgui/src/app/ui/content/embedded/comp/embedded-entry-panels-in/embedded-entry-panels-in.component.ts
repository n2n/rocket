import { Component, OnInit, OnDestroy } from '@angular/core';
import { EmbeddedEntryPanelModel } from '../../embedded-entry-panels-model';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiPanel } from 'src/app/si/model/entity/impl/embedded/embedded-entry-panels-in-si-field';
import { EmbeddedEntriesInModel } from '../../embedded-entry-in-model';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/impl/embedded/si-embedded-entry';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { SimpleSiStructureModel } from 'src/app/si/model/structure/impl/simple-si-structure-model';
import { EmbeddedEntriesSummaryInComponent } from '../embedded-entries-summary-in/embedded-entries-summary-in.component';
import { SiStructureType } from 'src/app/si/model/entity/si-field-structure-declaration';
import { SiType } from 'src/app/si/model/entity/si-type';

@Component({
	selector: 'rocket-embedded-entry-panels-in',
	templateUrl: './embedded-entry-panels-in.component.html',
	styleUrls: ['./embedded-entry-panels-in.component.css']
})
export class EmbeddedEntryPanelsInComponent implements OnInit, OnDestroy {

	siStructure: SiStructure;
	model: EmbeddedEntryPanelModel;

	panelLayout: PanelLayout;
	panelDefs: Array<{ panel: SiPanel, structure: SiStructure }>;

	ngOnInit(): void {
		this.panelLayout = new PanelLayout();

		this.panelDefs = [];
		for (const panel of this.model.getPanels()) {
			this.panelLayout.registerPanel(panel);

			const panelModel = new PanelEmbeddedEntryInModel(panel, this.model);

			const structure = this.siStructure.createChild(SiStructureType.SIMPLE_GROUP, panel.label,
					new SimpleSiStructureModel(
							new TypeSiContent(EmbeddedEntriesSummaryInComponent, (ref, refStructure) => {
								ref.instance.model = panelModel;
								ref.instance.siStructure = refStructure;
							})));

			this.panelDefs.push({ panel, structure });
		}
	}

	ngOnDestroy() {
		for (const panelDef of this.panelDefs) {
			panelDef.structure.dispose();
		}
	}
}

class PanelEmbeddedEntryInModel implements EmbeddedEntriesInModel {

	constructor(private panel: SiPanel, private model: EmbeddedEntryPanelModel) {

	}

	isNonNewRemovable(): boolean {
		return true;
	}

	isSortable(): boolean {
		return this.model.isSortable();
	}

	isSummaryRequired(): boolean {
		return true;
	}

	getPastCategory(): string {
		return this.model.getPastCategory();
	}

	getAllowedSiTypes(): SiType[] {
		return this.panel.allowedSiTypes;
	}

	setValues(values: SiEmbeddedEntry[]): void {
		this.panel.embeddedEntries = values;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.panel.embeddedEntries;
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


export class PanelLayout {

	private numGridRows = 0;
	private numGridCols = 0;

	constructor() {
	}

	registerPanel(panel: SiPanel) {
		const gridPos = panel.gridPos;
		if (gridPos === null) {
			return;
		}

		const colEnd = gridPos.colEnd;
		if (this.numGridCols < colEnd) {
			this.numGridCols = colEnd;
		}

		const rowEnd = gridPos.rowEnd;
		if (this.numGridRows < rowEnd) {
			this.numGridRows = rowEnd;
		}
	}

	hasGrid(): boolean {
		return this.numGridRows > 0 || this.numGridCols > 0;
	}

	style(): string {
		if (!this.hasGrid()) {
			return null;
		}

		return 'display: grid; grid-template-columns: repeat(' + (this.numGridRows - 1) + ', 1fr';
	}

	styleOf(panel: SiPanel): string {
		if (!panel.gridPos) {
			return null;
		}

		return 'grid-column-start: ' + panel.gridPos.colStart
				+ '; grid-column-end: ' + panel.gridPos.colEnd
				+ '; grid-row-start: ' + panel.gridPos.rowStart
				+ '; grid-row-end: ' + panel.gridPos.rowEnd;
	}
}

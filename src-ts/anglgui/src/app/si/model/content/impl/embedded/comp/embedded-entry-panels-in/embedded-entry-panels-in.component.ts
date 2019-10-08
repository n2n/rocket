import { Component, OnInit, OnDestroy } from '@angular/core';
import { EmbeddedEntryPanelModel } from '../../embedded-entry-panels-model';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { TypeUiContent } from 'src/app/si/model/structure/impl/type-si-content';
import { SimpleUiStructureModel } from 'src/app/si/model/structure/impl/simple-ui-structure-model';
import { EmbeddedEntriesSummaryInComponent } from '../embedded-entries-summary-in/embedded-entries-summary-in.component';
import { UiStructureType } from 'src/app/si/model/content/si-field-structure-declaration';
import { SiPanel } from 'src/app/si/model/content/impl/embedded/si-panel';
import { PanelEmbeddedEntryInModel } from './panel-embedded-entry-in-model';
import { SafeStyle, DomSanitizer } from '@angular/platform-browser';

@Component({
	selector: 'rocket-embedded-entry-panels-in',
	templateUrl: './embedded-entry-panels-in.component.html',
	styleUrls: ['./embedded-entry-panels-in.component.css']
})
export class EmbeddedEntryPanelsInComponent implements OnInit, OnDestroy {

	uiStructure: UiStructure;
	model: EmbeddedEntryPanelModel;

	panelLayout: PanelLayout;
	panelDefs: Array<{ panel: SiPanel, structure: UiStructure }>;

	constructor(san: DomSanitizer) {
		this.panelLayout = new PanelLayout(san);
	}

	ngOnInit(): void {
		this.panelDefs = [];
		for (const panel of this.model.getPanels()) {
			this.panelLayout.registerPanel(panel);

			const panelModel = new PanelEmbeddedEntryInModel(panel, this.model);

			const structure = this.uiStructure.createChild(UiStructureType.SIMPLE_GROUP, panel.label,
					new SimpleUiStructureModel(
							new TypeUiContent(EmbeddedEntriesSummaryInComponent, (ref, refStructure) => {
								ref.instance.model = panelModel;
								ref.instance.uiStructure = refStructure;
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



export class PanelLayout {

	private numGridRows = 0;
	private numGridCols = 0;

	constructor(private san: DomSanitizer) {
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

	style(): SafeStyle {
		if (!this.hasGrid()) {
			return null;
		}

		return this.san.bypassSecurityTrustStyle('display: grid; grid-template-columns: repeat('
				+ (this.numGridRows - 1) + ', 1fr');
	}

	styleOf(panel: SiPanel): SafeStyle {
		if (!panel.gridPos) {
			return null;
		}

		return this.san.bypassSecurityTrustStyle('grid-column-start: ' + panel.gridPos.colStart
				+ '; grid-column-end: ' + panel.gridPos.colEnd
				+ '; grid-row-start: ' + panel.gridPos.rowStart
				+ '; grid-row-end: ' + panel.gridPos.rowEnd);
	}
}

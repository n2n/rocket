import { Component, OnInit, OnDestroy } from '@angular/core';
import { EmbeddedEntriesSummaryInComponent } from '../embedded-entries-summary-in/embedded-entries-summary-in.component';
import { PanelEmbeddedEntryInModel } from './panel-embedded-entry-in-model';
import { SafeStyle, DomSanitizer } from '@angular/platform-browser';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntryPanelModel } from '../embedded-entry-panels-model';
import { SiPanel } from '../../model/si-panel';
import { UiStructureType } from 'src/app/si/model/meta/si-structure-declaration';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { PanelLayout } from './panel-layout';

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

			const structure = this.uiStructure.createContentChild(UiStructureType.SIMPLE_GROUP, panel.label,
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


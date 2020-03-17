import { Component, OnInit } from '@angular/core';
import { DomSanitizer } from '@angular/platform-browser';
import { PanelLayout } from './panel-layout';
import { EmbeddedEntryPanelInModel, PanelDef } from '../embedded-entry-panels-in-model';

@Component({
	selector: 'rocket-embedded-entry-panels-in',
	templateUrl: './embedded-entry-panels-in.component.html',
	styleUrls: ['./embedded-entry-panels-in.component.css']
})
export class EmbeddedEntryPanelsInComponent implements OnInit {

	model: EmbeddedEntryPanelInModel;

	panelLayout: PanelLayout;
	panelDefs: Array<PanelDef>;

	constructor(san: DomSanitizer) {
		this.panelLayout = new PanelLayout(san);
	}

	ngOnInit(): void {
		this.panelDefs = this.model.getPanelDefs();
		for (const panelDef of this.panelDefs) {
			this.panelLayout.registerPanel(panelDef.siPanel);
		}
	}
}


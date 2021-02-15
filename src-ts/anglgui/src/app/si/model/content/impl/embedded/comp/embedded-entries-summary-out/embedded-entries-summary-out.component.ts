import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesOutModel } from '../embedded-entries-out-model';
import { EmbeStructure } from '../../model/embe/embe-structure';

@Component({
	selector: 'rocket-embedded-entries-summary-out',
	templateUrl: './embedded-entries-summary-out.component.html',
	styleUrls: ['./embedded-entries-summary-out.component.css']
})
export class EmbeddedEntriesSummaryOutComponent implements OnInit {
	model: EmbeddedEntriesOutModel;

	constructor() { }

	ngOnInit() {
	}

	get embeStructures(): EmbeStructure[] {
		return this.model.getEmbeStructures();
	}
}

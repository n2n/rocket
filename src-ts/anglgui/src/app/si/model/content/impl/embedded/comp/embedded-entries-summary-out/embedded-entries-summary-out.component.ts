import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesOutModel } from '../embedded-entries-out-model';
import { EmbeOutCollection } from '../../model/embe/embe-collection';
import { Embe } from '../../model/embe/embe';

@Component({
	selector: 'rocket-embedded-entries-summary-out',
	templateUrl: './embedded-entries-summary-out.component.html',
	styleUrls: ['./embedded-entries-summary-out.component.css']
})
export class EmbeddedEntriesSummaryOutComponent implements OnInit {
	private embeCol: EmbeOutCollection;
	model: EmbeddedEntriesOutModel;

	constructor() { }

	ngOnInit() {
		this.embeCol = this.model.getEmbeOutCollection();
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
	}

	open(embe: Embe) {
		this.model.open(embe);
	}

	openAll() {
		this.model.openAll();
	}
}

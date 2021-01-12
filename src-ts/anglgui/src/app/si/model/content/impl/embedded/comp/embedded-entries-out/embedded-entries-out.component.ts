import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesOutModel } from '../embedded-entries-out-model';
import { EmbeOutCollection } from '../../model/embe/embe-collection';
import { Embe } from '../../model/embe/embe';

@Component({
	selector: 'rocket-embedded-entries-out',
	templateUrl: './embedded-entries-out.component.html',
	styleUrls: ['./embedded-entries-out.component.css']
})
export class EmbeddedEntriesOutComponent implements OnInit {
	model: EmbeddedEntriesOutModel;
	private embeCol: EmbeOutCollection;

	constructor() { }

	ngOnInit() {
		this.embeCol = this.model.getEmbeOutCollection();
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
	}

}

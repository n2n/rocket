import { Component, OnInit } from '@angular/core';
import { EmbeddedEntriesInModel } from '../embedded-entries-in-model';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { EmbeInCollection } from '../../model/embe/embe-collection';
import { Embe } from '../../model/embe/embe';

@Component({
	selector: 'rocket-embedded-entries-in',
	templateUrl: './embedded-entries-in.component.html',
	styleUrls: ['./embedded-entries-in.component.css']
})
export class EmbeddedEntriesInComponent implements OnInit {
	model: EmbeddedEntriesInModel;
	private embeCol: EmbeInCollection;
	obtainer: AddPasteObtainer;

	constructor() { }

	ngOnInit() {
		this.obtainer = this.model.getAddPasteObtainer();
		this.embeCol = this.model.getEmbeInCollection();
	}

	get maxReached(): boolean {
		const max = this.model.getMax();

		return max && max >= this.embeCol.embes.length;
	}

	get toOne(): boolean {
		return this.model.getMax() === 1;
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
	}

	add(siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.createEmbe(siEmbeddedEntry);
		this.embeCol.writeEmbes();
	}

	addBefore(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.createEmbe(siEmbeddedEntry);
		this.embeCol.changeEmbePosition(this.embeCol.embes.length - 1, this.embeCol.embes.indexOf(embe));
		this.embeCol.writeEmbes();
	}

	up() {

	}

	down() {

	}
}

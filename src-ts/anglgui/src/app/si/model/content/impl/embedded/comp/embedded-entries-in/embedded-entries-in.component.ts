import { Component, OnInit, Injector } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { EmbedInCollection } from '../embe-collection';
import { Embe } from '../embe';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';

@Component({
	selector: 'rocket-embedded-entries-in',
	templateUrl: './embedded-entries-in.component.html',
	styleUrls: ['./embedded-entries-in.component.css']
})
export class EmbeddedEntriesInComponent implements OnInit {
	uiStructure: UiStructure;
	model: EmbeddedEntriesInModel;
	private embeCol: EmbedInCollection;
	obtainer: AddPasteObtainer;

	constructor() { }

	ngOnInit() {
		this.embeCol = new EmbedInCollection(this.uiStructure, this.model, false);
		this.obtainer = this.model.getObtainer();

		this.embeCol.readEmbes();
		this.embeCol.fillWithPlaceholderEmbes();
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
	}

	add(siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.initEmbe(this.embeCol.createEmbe(), siEmbeddedEntry);
		this.embeCol.writeEmbes();
	}

	addBefore(embe: Embe, siEmbeddedEntry: SiEmbeddedEntry) {
		this.embeCol.initEmbe(this.embeCol.createEmbe(), siEmbeddedEntry);
		this.embeCol.changeEmbePosition(this.embeCol.embes.length - 1, this.embeCol.embes.indexOf(embe));
		this.embeCol.writeEmbes();
	}

	up() {

	}

	down() {

	}
}

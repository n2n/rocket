import { Component, OnInit, Injector } from '@angular/core';
import { EmbeddedEntryInModel } from '../../embedded-entry-in-model';
import { EmbedInCollection, EmbeCollection } from '../../embe-collection';
import { EmbeddedAddPasteObtainer } from '../../embedded-add-paste-obtainer';
import { SiService } from 'src/app/si/model/si.service';
import { SiEmbeddedEntry } from 'src/app/si/model/entity/si-embedded-entry';
import { Embe } from '../../embe';
import { SiStructure } from "src/app/si/model/structure/si-structure";

@Component({
  selector: 'rocket-embedded-entries-in',
  templateUrl: './embedded-entries-in.component.html',
  styleUrls: ['./embedded-entries-in.component.css']
})
export class EmbeddedEntriesInComponent implements OnInit {
    siStructure: SiStructure;
	model: EmbeddedEntryInModel;
	private embeCol: EmbedInCollection;
	obtainer: EmbeddedAddPasteObtainer;

	constructor(private injector: Injector) { }

	ngOnInit() {
		this.embeCol = new EmbedInCollection(this.siStructure, this.model);
		this.obtainer = new EmbeddedAddPasteObtainer(this.injector.get(SiService), this.model.getApiUrl(),
				this.model.getSiZone(), this.model.isSummaryRequired());

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

	up(embe: Embe) {

	}

	down(embe: Embe) {

	}



}

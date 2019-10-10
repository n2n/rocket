import { Component, OnInit, Injector } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { EmbedInCollection } from '../embe-collection';
import { EmbeddedAddPasteObtainer } from '../embedded-add-paste-obtainer';
import { SiService } from 'src/app/si/manage/si.service';
import { Embe } from '../embe';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';

@Component({
	selector: 'rocket-embedded-entries-in',
	templateUrl: './embedded-entries-in.component.html',
	styleUrls: ['./embedded-entries-in.component.css']
})
export class EmbeddedEntriesInComponent implements OnInit {
	uiStructure: UiStructure;
	model: EmbeddedEntriesInModel;
	private embeCol: EmbedInCollection;
	obtainer: EmbeddedAddPasteObtainer;

	constructor(private injector: Injector) { }

	ngOnInit() {
		this.embeCol = new EmbedInCollection(this.uiStructure, this.model, false);
		this.obtainer = new EmbeddedAddPasteObtainer(this.injector.get(SiService), this.model.getApiUrl(),
				this.model.isSummaryRequired());

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

import { Component, OnInit, OnDestroy } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { Embe } from '../../model/embe';
import { EmbeInCollection } from '../../model/embe-collection';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';


@Component({
	selector: 'rocket-embedded-entries-summary-in',
	templateUrl: './embedded-entries-summary-in.component.html',
	styleUrls: ['./embedded-entries-summary-in.component.css']
})
export class EmbeddedEntriesSummaryInComponent implements OnInit, OnDestroy {
	model: EmbeddedEntriesInModel;

	private embeCol: EmbeInCollection;
	obtainer: AddPasteObtainer;

	embeUiStructures = new Array<{embe: Embe, uiStructure: UiStructure}>();

	constructor(private clipboard: ClipboardService) {
	}

	ngOnInit() {
		this.obtainer = this.model.getAddPasteObtainer();
		this.embeCol = this.model.getEmbeInCollection();
	}

	ngOnDestroy() {
	}

	maxReached(): boolean {
		const max = this.model.getMax();

		return max && max >= this.embeCol.embes.length;
	}

	toOne(): boolean {
		return this.model.getMax() === 1;
	}

	drop(event: CdkDragDrop<string[]>) {
		this.embeCol.changeEmbePosition(event.previousIndex, event.currentIndex);
		this.embeCol.writeEmbes();
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

	place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
		embe.siEmbeddedEntry = siEmbeddedEntry;
		this.embeCol.writeEmbes();
	}

	get embes(): Embe[] {
		return this.embeCol.embes;
	}

	copy(embe: Embe) {
		if (!embe.siEmbeddedEntry) {
			return;
		}
		this.clipboard.add(new SiGenericValue(embe.siEmbeddedEntry.copy()));
	}

	open(embe: Embe) {
		this.model.open(embe);
	}

	openAll() {
		this.model.openAll();
	}
}

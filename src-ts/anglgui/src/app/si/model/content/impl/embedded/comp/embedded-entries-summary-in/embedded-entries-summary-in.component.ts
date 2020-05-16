import { Component, OnInit, OnDestroy } from '@angular/core';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { Embe } from '../../model/embe';
import { EmbeInCollection } from '../../model/embe-collection';
import { EmbeddedEntriesInModel } from '../embedded-entry-in-model';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { CopyPool } from '../../model/embe-copy-pool';


@Component({
	selector: 'rocket-embedded-entries-summary-in',
	templateUrl: './embedded-entries-summary-in.component.html',
	styleUrls: ['./embedded-entries-summary-in.component.css']
})
export class EmbeddedEntriesSummaryInComponent implements OnInit, OnDestroy {
	model: EmbeddedEntriesInModel;

	copyPool: CopyPool;
	private embeCol: EmbeInCollection;
	obtainer: AddPasteObtainer;

	embeUiStructures = new Array<{embe: Embe, uiStructure: UiStructure}>();

	constructor(clipboard: ClipboardService) {
		this.copyPool = new CopyPool(clipboard);
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

	// place(siEmbeddedEntry: SiEmbeddedEntry, embe: Embe) {
	// 	embe.siEmbeddedEntry = siEmbeddedEntry;
	// 	this.embeCol.writeEmbes();
	// }

	remove(embe: Embe) {
		if (this.embeCol.embes.length > this.model.getMin()) {
			this.embeCol.removeEmbe(embe);
			return;
		}

		embe.siEmbeddedEntry = null;
		this.obtainer.obtainNew().then(siEmbeddedEntry => {
			embe.siEmbeddedEntry = siEmbeddedEntry;
		});
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
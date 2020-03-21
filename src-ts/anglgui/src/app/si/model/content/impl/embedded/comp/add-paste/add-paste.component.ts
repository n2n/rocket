import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { ClipboardService } from '../../../clipboard.service';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { AddPasteObtainer } from '../add-paste-obtainer';


export enum AddPasteType {
	REDUCED = 'reduced',
	BLOCK = 'block',
	TILES = 'tiles'
}

@Component({
	selector: 'rocket-si-add-past',
	templateUrl: './add-paste.component.html',
	styleUrls: ['./add-paste.component.css']
})
export class AddPasteComponent implements OnInit {

	@Input()
	obtainer: AddPasteObtainer;
	@Input()
	pasteCategory: string|null = null;
	@Input()
	allowedSiTypeQualifiers: SiTypeQualifier[]|null = null;
	@Input()
	type = AddPasteType.BLOCK;
	@Input()
	disabled = false;

	@Output()
	newEntry = new EventEmitter<SiEmbeddedEntry>();

	pastablesVisible = false;
	addLoading = false;
	addLoadingSiTypeQualifier: SiTypeQualifier|null = null;
	pasteLoadingSiEntryQualifier: SiEntryQualifier|null = null;
	addableSiEmbeddedEntry: SiEmbeddedEntry|null = null;
	addables: SiEntryQualifier[]|null = null;

	constructor(private clipboardService: ClipboardService) {
	}

	ngOnInit() {
	}

	get loading(): boolean {
		return this.addLoading || !!this.addLoadingSiTypeQualifier ||	!!this.pasteLoadingSiEntryQualifier;
	}

	add() {
		if (this.loading) {
			return;
		}

		this.addLoading = true;
		this.obtainer.obtain(null).subscribe((siEmbeddedEntry) => {
			this.addLoading = false;
			this.handleAddResponse(siEmbeddedEntry);
		});
	}

	addBySiType(siTypeQualifier: SiTypeQualifier) {
		if (this.addLoadingSiTypeQualifier) {
			return;
		}

		this.addLoadingSiTypeQualifier = siTypeQualifier;
		this.obtainer.obtain(null).subscribe((siEmbeddedEntry) => {
			this.addLoadingSiTypeQualifier = null;
			this.handleAddResponse(siEmbeddedEntry);
			this.choose(siTypeQualifier);
		});
	}

	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.addableSiEmbeddedEntry = siEmbeddedEntry;
		this.addables = [];

		for (const siEntryQualifier of siEmbeddedEntry.entry.entryQualifiers) {
			if (!this.isTypeAllowed(siEntryQualifier.typeQualifier)) {
				continue;
			}

			this.addables.push(siEntryQualifier);
		}

		if (this.addables.length === 0) {
			throw new Error('No allowed buildup types.');
		}

		if (this.addables.length === 1) {
			this.choose(this.addables[0].typeQualifier);
			return;
		}
	}

	choose(siTypeQualifier: SiTypeQualifier) {
		this.reset();
		this.addableSiEmbeddedEntry.comp.entry.selectedTypeId = siTypeQualifier.id;
		if (this.addableSiEmbeddedEntry.summaryComp) {
			this.addableSiEmbeddedEntry.summaryComp.entry.selectedTypeId = siTypeQualifier.id;
		}
		this.newEntry.emit(this.addableSiEmbeddedEntry);
		this.addableSiEmbeddedEntry = null;
	}

	reset() {
		this.addables = null;
		this.pastablesVisible = false;
	}

	paste(siQualifier: SiEntryQualifier) {
		if (this.loading) {
			return false;
		}

		this.pasteLoadingSiEntryQualifier = siQualifier;
		this.obtainer.obtain(siQualifier).subscribe((siEmbeddedEntry) => {
			this.pasteLoadingSiEntryQualifier = null;
			this.handlePasteResponse(siEmbeddedEntry);
		});
	}

	private handlePasteResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.reset();
		this.newEntry.emit(siEmbeddedEntry);
	}

	get addablesVisible(): boolean {
		return !!this.addables;
	}

	get pastablesAvailable(): boolean {
		return this.pasteCategory && this.clipboardService.containsCategory(this.pasteCategory);
	}

	get pastables(): SiEntryQualifier[] {
		return this.clipboardService.getByCategory(this.pasteCategory);
	}

	isTypeAllowed(siTypeQualifier: SiTypeQualifier) {
		return !this.allowedSiTypeQualifiers || !!this.allowedSiTypeQualifiers
				.find(allowedSiTypeQualifier => allowedSiTypeQualifier.id === siTypeQualifier.id);
	}

	isAddLoading(siTypeQualifier: SiTypeQualifier = null): boolean {
		return this.addLoading || (siTypeQualifier && this.addLoadingSiTypeQualifier
				&& this.addLoadingSiTypeQualifier.equals(siTypeQualifier));
	}

	isPasteLoading(siQualifier: SiEntryQualifier): boolean {
		return !!this.pasteLoadingSiEntryQualifier && this.pasteLoadingSiEntryQualifier.equals(siQualifier);
	}
}



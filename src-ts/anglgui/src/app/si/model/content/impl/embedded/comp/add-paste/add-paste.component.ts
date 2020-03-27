import { Component, OnInit, Input, EventEmitter, Output, HostListener } from '@angular/core';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { SiGenericEmbeddedEntry } from '../../model/generic-embedded';


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
	
	private _disabled = false;

	@Output()
	newEntry = new EventEmitter<SiEmbeddedEntry>();

	loading = false;

	popupOpen = false;
	siEmbeddedEntry: SiEmbeddedEntry|null = null;

	addables: SiMaskQualifier[] = [];
	pastables: SiEntryQualifier[] = [];
	illegalPastables: SiEntryQualifier[] = [];

	private siGenericEmbeddedEntries: SiGenericEmbeddedEntry[]|null = null;

	constructor(private clipboardService: ClipboardService) {
	}

	ngOnInit() {
	}

	@Input()
	set disabled(disabled: boolean) {
		this._disabled = disabled;

		if (!this.disabled) {
			this.closePopup();
		}
	}

	get disabled(): boolean {
		return this._disabled;
	}

	@HostListener('mouseenter')
	prepareObtainer() {
		this.obtainer.preloadNew();
	}

	closePopup() {
		this.popupOpen = false;
	}

	togglePopup() {
		this.popupOpen = !this.popupOpen;

		if (!this.popupOpen || this.loading) {
			return;
		}

		if (this.siEmbeddedEntry) {
			this.update();
			return;
		}

		this.loading = true;
		this.obtainer.obtainNew().then((siEmbeddedEntry) => {
			this.loading = false;
			this.handleAddResponse(siEmbeddedEntry);
		});
	}

	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.siEmbeddedEntry = siEmbeddedEntry;
		this.update();

		if (this.addables.length === 1 && this.pastables.length === 0 && this.illegalPastables.length === 0) {
			this.chooseAddable(this.addables[0]);
		}
	}

	private update() {
		this.addables = this.siEmbeddedEntry.maskQualifiers;

		this.pastables = [];
		this.illegalPastables = [];

		this.siGenericEmbeddedEntries = this.clipboardService.filter(SiGenericEmbeddedEntry);
		for (const siGenericEmbeddedEntry of this.siGenericEmbeddedEntries) {
			if (!siGenericEmbeddedEntry.selectedTypeId) {
				continue;
			}

			if (this.siEmbeddedEntry.containsTypeId(siGenericEmbeddedEntry.selectedTypeId)) {
				this.pastables.push(siGenericEmbeddedEntry.entryQualifier);
			} else {
				this.illegalPastables.push(siGenericEmbeddedEntry.entryQualifier);
			}
		}
	}

	get searchable(): boolean {
		return (this.addables.length + this.pastables.length + this.illegalPastables.length) > 10;
	}

	chooseAddable(siMaskQualifier: SiMaskQualifier) {
		this.popupOpen = false;
		this.siEmbeddedEntry.selectedTypeId = siMaskQualifier.identifier.typeId;
		this.newEntry.emit(this.siEmbeddedEntry);
		this.reset();
	}

	choosePastable(siEntryQualifier: SiEntryQualifier) {
		const siGenericEmbeddedEntry = this.clipboardService.filter(SiGenericEmbeddedEntry)
				.find((gene) => {
					return gene.entryQualifier.equals(siEntryQualifier);
				});

		if (!siGenericEmbeddedEntry) {
			return;
		}

		this.siEmbeddedEntry.paste(siGenericEmbeddedEntry);
		this.newEntry.emit(this.siEmbeddedEntry);
		this.reset();
	}


	// addBySiType(siMaskQualifier: SiMaskQualifier) {
	// 	if (this.addLoadingSiMaskQualifier) {
	// 		return;
	// 	}

	// 	this.addLoadingSiMaskQualifier = siMaskQualifier;
	// 	this.obtainer.obtain(null).subscribe((siEmbeddedEntry) => {
	// 		this.addLoadingSiMaskQualifier = null;
	// 		this.handleAddResponse(siEmbeddedEntry);
	// 		this.choose(siMaskQualifier);
	// 	});
	// }

	reset() {
		this.popupOpen = false;
		this.siEmbeddedEntry = null;
		this.addables = [];
		this.pastables = [];
		this.illegalPastables = [];
		this.siGenericEmbeddedEntries = [];
	}
}



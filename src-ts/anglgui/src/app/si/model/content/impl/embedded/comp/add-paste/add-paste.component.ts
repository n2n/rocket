import { Component, OnInit, Input, EventEmitter, Output, HostListener } from '@angular/core';
import { SiEmbeddedEntry } from '../../model/si-embedded-entry';
import { AddPasteObtainer } from '../add-paste-obtainer';
import { ClipboardService } from 'src/app/si/model/generic/clipboard.service';
import { ChoosePasteModel } from '../choose-paste/choose-paste-model';


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
	choosePasteModel: ChoosePasteModel;


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

		if (this.choosePasteModel) {
			this.choosePasteModel.update();
			return;
		}

		this.loading = true;
		this.obtainer.obtainNew().then((siEmbeddedEntry) => {
			this.loading = false;
			this.handleAddResponse(siEmbeddedEntry);
		});
	}

	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.choosePasteModel = new ChoosePasteModel(siEmbeddedEntry, this.clipboardService);

		if (siEmbeddedEntry.selectedTypeId && this.choosePasteModel.pastables.length === 0
				&& this.choosePasteModel.illegalPastables.length === 0) {
			// this.siEmbeddedEntry.selectedTypeId = siMaskQualifier.identifier.typeId;
			this.choose(siEmbeddedEntry);
			return;
		}

		this.choosePasteModel.done$.subscribe(() => {
			this.choose(siEmbeddedEntry);
		});
	}

	private choose(siEmbeddedEntry: SiEmbeddedEntry) {
		this.popupOpen = false;
		this.newEntry.emit(siEmbeddedEntry);
		this.reset();
	}

	reset() {
		this.popupOpen = false;
		this.choosePasteModel = null;
	}
}



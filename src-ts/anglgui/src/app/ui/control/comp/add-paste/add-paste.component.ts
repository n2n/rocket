import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { SiQualifier, SiIdentifier } from 'src/app/si/model/content/si-qualifier';
import { ClipboardService } from 'src/app/si/model/content/clipboard.service';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { Observable } from 'rxjs';

@Component({
  selector: 'rocket-ui-add-past',
  templateUrl: './add-paste.component.html',
  styleUrls: ['./add-paste.component.css']
})
export class AddPasteComponent implements OnInit {

	@Input()
	obtainer: AddPasteObtainer;
	@Input()
	pasteCategory: string|null = null;
	@Input()
	allowedTypeNames: string[]|null = null;
	@Output()
	newEntry = new EventEmitter<SiEmbeddedEntry>();
	@Input()
	reduced = false;

	pastablesVisible = false;
	addLoading = false;
	pasteLoadingSiQualifier: SiQualifier|null = null;
	addableSiEmbeddedEntry: SiEmbeddedEntry|null = null;
	addables: SiQualifier[]|null = null;

	constructor(private clipboardService: ClipboardService) {
	}

	ngOnInit() {
	}

	get loading(): boolean {
		return this.addLoading || !!this.pasteLoadingSiQualifier;
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

	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.addableSiEmbeddedEntry = siEmbeddedEntry;
		this.addables = [];

		for (const siQualifier of siEmbeddedEntry.entry.typeQualifiers) {
			if (!this.isAllowed(siQualifier)) {
				continue;
			}

			this.addables.push(siQualifier);
		}

		if (this.addables.length === 0) {
			throw new Error('No allowed buildup types.');
		}

		if (this.addables.length === 1) {
			this.choose(this.addables[0]);
			return;
		}
	}

	choose(siQualifier: SiQualifier) {
		this.reset();
		this.addableSiEmbeddedEntry.comp.entry.selectedTypeId = siQualifier.buildupId;
		if (this.addableSiEmbeddedEntry.summaryComp) {
			this.addableSiEmbeddedEntry.summaryComp.entry.selectedTypeId = siQualifier.buildupId;
		}
		this.newEntry.emit(this.addableSiEmbeddedEntry);
		this.addableSiEmbeddedEntry = null;
	}

	reset() {
		this.addables = null;
		this.pastablesVisible = false;
	}

	paste(siQualifier: SiQualifier) {
		if (this.loading) {
			return false;
		}

		this.pasteLoadingSiQualifier = siQualifier;
		this.obtainer.obtain(siQualifier).subscribe((siEmbeddedEntry) => {
			this.pasteLoadingSiQualifier = null;
			this.handlePasteResponse(siEmbeddedEntry);
		});
	}

	private handlePasteResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.reset();
		this.newEntry.emit(siEmbeddedEntry);
	}

	get addablesVisible() {
		return !!this.addables;
	}

	get pastablesAvailable(): boolean {
		return this.pasteCategory && this.clipboardService.containsCategory(this.pasteCategory);
	}

	get pastables(): SiQualifier[] {
		return this.clipboardService.getByCategory(this.pasteCategory);
	}

	isAllowed(siQualifier: SiQualifier): boolean {
		if (!!this.pasteCategory && siQualifier.category !== this.pasteCategory) {
			return false;
		}

		return !this.allowedTypeNames || -1 < this.allowedTypeNames.indexOf(siQualifier.typeName);
	}

	isAddLoading(): boolean {
		return this.addLoading;
	}

	isPasteLoading(siQualifier: SiQualifier): boolean {
		return !!this.pasteLoadingSiQualifier && this.pasteLoadingSiQualifier.equals(siQualifier);
	}


}

export interface AddPasteObtainer {

	obtain: (siIdentifier: SiIdentifier|null) => Observable<SiEmbeddedEntry>;
}

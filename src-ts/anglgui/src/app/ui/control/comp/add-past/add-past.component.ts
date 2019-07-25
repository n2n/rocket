import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiQualifier, SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { ClipboardService } from "src/app/si/model/content/clipboard.service";
import { SiService } from "src/app/si/model/si.service";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";
import { SiGetInstruction } from "src/app/si/model/api/si-get-instruction";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";
import { Observable } from "rxjs";

@Component({
  selector: 'rocket-ui-add-past',
  templateUrl: './add-past.component.html',
  styleUrls: ['./add-past.component.css']
})
export class AddPastComponent implements OnInit {

	@Input()
	optainer: AddPastOptainer;
	@Input()
	pasteCategory: string|null = null;
	@Input()
	allowedTypeNames: string[]|null = null;
	@Output()
	newEntry = new EventEmitter<SiEmbeddedEntry>();
	
	pastablesVisisble: boolean = false;
	addLoading = false;
	pasteLoadingSiQualifier: SiQualifier|null = null;;
	loadedSiEmbeddedEntry: SiEmbeddedEntry|null = null;
	addables: SiQualifier[]|null = null;
	
	constructor(private clipboardService: ClipboardService, private siService: SiService) {
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
		
		this.addLoading = true
		this.optainer.optain(null).subscribe((siEmbeddedEntry) => {
			this.addLoading = false;
			this.handleAddResponse(siEmbeddedEntry);
		});
	}
	
	private handleAddResponse(siEmbeddedEntry: SiEmbeddedEntry) {
		this.addables = [];
		for (const siQualifier of siEmbeddedEntry.entry.typeQualifiers) {
			if (!this.isAllowed(siQualifier)) {
				continue;
			}
			
			this.addables.push(siQualifier);
		}
		
		if (this.addables.length == 0) {
			throw new Error('No allowed buildup types.')
		}
		
		if (this.addables.length == 1) {
			this.choose(this.addables[0]);
		}
	}
	
	choose(siQualifier: SiQualifier) {
		this.reset();
		this.loadedSiEmbeddedEntry.comp.entry.selectedTypeId = siQualifier.buildupId;
		if (this.loadedSiEmbeddedEntry.summaryComp) {
			this.loadedSiEmbeddedEntry.summaryComp.entry.selectedTypeId = siQualifier.buildupId;
		}
		this.newEntry.emit(this.loadedSiEmbeddedEntry)
	}
	
	reset() {
		this.addables = null;
		this.pastablesVisisble = false;
	}
	
	past(siQualifier: SiQualifier) {
		if (this.loading) {
			return false;
		}
		
		this.pasteLoadingSiQualifier = siQualifier
		this.optainer.optain(siQualifier).subscribe((siEmbeddedEntry) => {
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
		return this.clipboardService.getByCategory(this.pasteCategory)
	}
	
	isAllowed(siQualifier: SiQualifier): boolean {
		if (siQualifier.category != this.pasteCategory) {
			return false;
		}
		
		return !this.allowedTypeNames || this.allowedTypeNames.indexOf(siQualifier.typeName);
	}
	
	isAddLoading(): boolean {
		return this.addLoading;
	}
	
	isPasteLoading(siQualifier: SiQualifier): boolean {
		return !!this.pasteLoadingSiQualifier && this.pasteLoadingSiQualifier.equals(siQualifier);
	}
	

}

export interface AddPastOptainer {
	
	optain: (siIdentifier: SiIdentifier|null) => Observable<SiEmbeddedEntry>;
}

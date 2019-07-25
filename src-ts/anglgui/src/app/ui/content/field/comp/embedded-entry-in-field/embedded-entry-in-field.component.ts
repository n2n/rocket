import { Component, OnInit } from '@angular/core';
import { EmbeddedEntryInModel } from 'src/app/ui/content/field/embedded-entry-in-model';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { PopupSiLayer } from 'src/app/si/model/structure/si-layer';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiButton } from 'src/app/si/model/control/si-button';
import { SimpleSiControl } from 'src/app/si/model/control/impl/simple-si-control';
import { AddPastOptainer } from 'src/app/ui/control/comp/add-past/add-past.component';
import { Observable } from 'rxjs';
import { SiIdentifier } from 'src/app/si/model/content/si-qualifier';
import { SiService } from 'src/app/si/model/si.service';
import { BulkyEntrySiComp } from 'src/app/si/model/structure/impl/bulky-entry-si-content';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { CompactEntrySiComp } from 'src/app/si/model/structure/impl/compact-entry-si-content';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';

@Component({
  selector: 'rocket-embedded-entry-in-field',
  templateUrl: './embedded-entry-in-field.component.html',
  styleUrls: ['./embedded-entry-in-field.component.css']
})
export class EmbeddedEntryInFieldComponent implements OnInit {

	model: EmbeddedEntryInModel;

	private popupSiLayer: PopupSiLayer|null = null;

	private embes: Embe[] = []

	constructor(private translationService: TranslationService) {}

	ngOnInit() {
	}
	
	get visibleEmbes(): Embe[] {
		const embes = [];
		for (const siEmbeddedEntry of this.model.getValues()) {
			embes.push(this.reqEmbe(siEmbeddedEntry));
		}
		return embes;
	}
	
	get reduced(): boolean {
		return this.model.isReduced();
	}
	
	private reqEmbe(siEmbeddedEntry: SiEmbeddedEntry): Embe {
		let embe = this.embes.find(embe => embe.siEmbeddedEntry == siEmbeddedEntry);
		if (embe) {
			embe.siStructure.model = siEmbeddedEntry.comp;
			if (this.reduced) {
				embe.summarySiStructure.model = siEmbeddedEntry.summaryComp;
			}
			return embe;
		}
		
		const siStructure = new SiStructure(null, null, siEmbeddedEntry.comp);
		const summarySiStructure = (this.reduced ? new SiStructure(null, null, siEmbeddedEntry.summaryComp) : null);
		
		if (this.reduced) {
			siEmbeddedEntry.comp.controls = [ 
				new SimpleSiControl(
						new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-save'),
						() => { this.apply(siEmbeddedEntry); }), 
				new SimpleSiControl(
						new SiButton(this.translationService.t('common_save_label'), 'btn btn-success', 'fas fa-trash-restore-alt'),
						() => { this.cancel(siEmbeddedEntry); }) 
			];
		}
		
		embe = new Embe(siEmbeddedEntry, siStructure, summarySiStructure)
		this.model.registerSiStructure(siStructure);
		this.model.registerSiStructure(summarySiStructure);
		this.embes.push(embe);
		return embe;
	}
	
	drop(event: CdkDragDrop<string[]>) {
		console.log(event);
//		moveItemInArray(this.movies, event.previousIndex, event.currentIndex);
	}
	
	open(embe: Embe) {
		if (this.popupSiLayer) {
			return;
		}
		
		const siZone = this.model.getSiZone();
		
		this.popupSiLayer = siZone.layer.container.createLayer();
		this.popupSiLayer.onDispose(() => {
			this.popupSiLayer = null;
		});
		
		this.popupSiLayer.pushZone(null).structure = embe.siStructure;
	}
	
	apply(siEmbeddedEntry: SiEmbeddedEntry) {
		if (this.popupSiLayer) {
			this.popupSiLayer.dispose();
		}
	}
	
	cancel(siEmbeddedEntry: SiEmbeddedEntry) {
		
	}
}


class Embe {
	constructor (public siEmbeddedEntry: SiEmbeddedEntry,
			public siStructure: SiStructure,
			public summarySiStructure: SiStructure|null) {
	}
	
	get siEntry(): SiEntry {
		return this.siEmbeddedEntry.entry;
	}
}

class EmbeddedAddPastOptainer implements AddPastOptainer {
	constructor(private siService: SiService, private apiUrl: string, private siZone: SiZone, private optainSummary: boolean) {
	}
	
	private createBulkyInstruction(comp: CompactEntrySiComp, siIdentifier: SiIdentifier|null): SiGetInstruction {
		if (siIdentifier) {
	    	return SiGetInstruction.entry(comp, true, true, siIdentifier.id);
    	}
		
    	return SiGetInstruction.newEntry(comp, true, false);
	}
	
	private createSummaryInstruction(comp: CompactEntrySiComp, siIdentifier: SiIdentifier|null): SiGetInstruction {
		if (siIdentifier) {
			return SiGetInstruction.entry(comp, false, true, siIdentifier.id);
		}
		
		return SiGetInstruction.newEntry(comp, false, false);
	}
	
    optain(siIdentifier: SiIdentifier|null): Observable<SiEmbeddedEntry> {
    	const request = new SiGetRequest();
    	
    	const comp = new BulkyEntrySiComp(undefined, undefined);
    	request.instructions[0] = this.createBulkyInstruction(comp, siIdentifier)
    	
    	let summaryComp: CompactEntrySiComp|null = null;
    	if (this.optainSummary) {
    		summaryComp = new CompactEntrySiComp(undefined, undefined);
    		request.instructions[1] = this.createSummaryInstruction(summaryComp, siIdentifier);
    	}
    	
    	this.siService.apiGet(this.apiUrl, request, this.siZone).subscribe((siGetResponse) => {
    		return this.handleResponse(siGetResponse, comp, summaryComp);
    	});
    }
    
    private handleResponse(response: SiGetResponse, comp: BulkyEntrySiComp, 
    		summaryComp: CompactEntrySiComp|null): SiEmbeddedEntry {
    			
    	comp.bulkyDeclaration = response.results[0].bulkyDeclaration;
    	comp.entry = response.results[0].entry;
    	
    	if (summaryComp) {
    		summaryComp.compactDeclaration = response.results[1].compactDeclaration;
    		summaryComp.entry = response.results[1].entry;
    	}
    	
    	return new SiEmbeddedEntry(comp, summaryComp);
    }
}
import { Injectable } from '@angular/core';
import { SiService } from "src/app/si/model/si.service";
import { PlatformLocation } from "@angular/common";
import { Router } from "@angular/router";
import { SiLayer } from "src/app/si/model/structure/si-layer";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiInput } from "src/app/si/model/input/si-input";
import { SiEntryInput } from "src/app/si/model/input/si-entry-input";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiResult } from "src/app/si/model/control/si-result";

@Injectable({
  providedIn: 'root'
})
export class SiCommanderService {

	constructor(private siService: SiService, private router: Router, private platformLocation: PlatformLocation) {
	}
  
	loadZone(zone: SiZone) {
		zone.removeContent();
		
		this.siService.lookupSiZoneContent(zone)
				.subscribe((siZoneContent) => {
					zone.content = siZoneContent;
				});
	}
	
	navigate(url: string, layer: SiLayer) {
		if (!layer.main) {
			throw new Error('not yet implemented');
		}
		
		const baseHref = this.platformLocation.getBaseHrefFromDOM();
		
		if (!url.startsWith(baseHref)) {
			throw new IllegalSiStateError('Ref url must start with base href: ' + url);
		}
		
		this.router.navigateByUrl(url.substring(baseHref.length));
	}
	
	execEntryControl(callId: object, zoneContent: SiZoneContent, entry: SiEntry, includeInput: boolean) {
		if (!entry.id) {
			throw new IllegalSiStateError('Entry control cannnot be executed on new entry.');
		}

		const entryInputs: SiEntryInput[] = [];
		if (includeInput) {
			entryInputs.push(entry.readInput());
		}

		this.siService.entryControlCall(zoneContent.getApiUrl(), callId, entry.id, entryInputs);
	}
	
	execSelectionControl(callId: object, zoneContent: SiZoneContent, entries: SiEntry[], includeInput: boolean) {
		const entryIds: string[] = [];
		const entryInputs: SiEntryInput[] = [];
	
		for (const entry of entries) {
			if (!entry.id) {
				throw new IllegalSiStateError('Selection control cannnot be executed on new entry.');
			}
			
			entryIds.push(entry.id);
			
			if (includeInput) {
				entryInputs.push(entry.readInput());
			}
		}
		
		this.siService.selectionControlCall(zoneContent.getApiUrl(), callId, entryIds, entryInputs);
	}
	
	execControl(callId: object, zoneContent: SiZoneContent, includeInput: boolean) {
		const input = new SiInput();

		if (!includeInput) {
			throw new Error('not yet implemented');
		}
		
		const entries: SiEntry[] = [];
		for (const entry of zoneContent.getEntries()) {
			if (!entry.inputAvailable) {
				continue;
			}
			
			entries.push(entry);
			input.entryInputs.push(entry.readInput());
		}
		
		this.siService.controlCall(zoneContent.getApiUrl(), callId, input)
				.subscribe((result) => {
					this.handleResult(result, entries);
				});
	}
	
	private handleResult(result: SiResult, inputEntries: SiEntry[]) {
		if (inputEntries.length > 0) {
			this.handleEntryErrors(result.entryErrors, inputEntries)
		}
	}
	
	private handleEntryErrors(entryErrors: Map<string, SiEntryError>, entries: SiEntry[]) {
		if (entries.length == 0) {
			return;
		}
		
		for (let entry of entries) {
			entry.resetError();
		}
		
		for (let [key, entryError] of entryErrors) {
			if (!entries[key]) {
				throw new IllegalSiStateError('Unknown entry key ' + key);
			}
			
			entries[0].handleError(entryError);
		}
	}
	
}


import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiService } from "src/app/si/model/si.service";
import { SiZone } from "src/app/si/model/structure/si-zone";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { IllegalSiStateError } from "src/app/si/model/illegal-si-state-error";
import { SiZoneContent } from "src/app/si/model/structure/si-zone-content";

export class ApiCallSiControl implements SiControl {
	
	inputSent = false;
	private entryBoundFlag: boolean
	
	constructor(public apiUrl: string, public apiCallId: object, public button: SiButton,
			public zoneContent: SiZoneContent, public entry: SiEntry|null = null) {	
	}
	
	getButton(): SiButton {
		return this.button;
	}
	
	set entryBound(entryBound: boolean) {
		if (this.entry && !entryBound) {
			throw new IllegalSiStateError('Control must be bound to entry.');
		}
		
		this.entryBoundFlag = entryBound;
	}
	
	get entryBound(): boolean {
		return !!this.entry || this.entryBoundFlag;
	}
	
	exec(commandService: SiCommanderService) {
		if (this.entry) {
			commandService.execEntryControl(this.apiUrl, this.apiCallId, this.entry, this.inputSent);
			return;
		}
		
		if (this.entryBound) {
			commandService.execSelectionControl(this.apiUrl, this.apiCallId, this.zoneContent, this.zoneContent.getSelectedEntries(), 
					this.inputSent);
			return;
		}
		
		commandService.execControl(this.apiUrl, this.apiCallId, this.zoneContent, this.inputSent);
	}
}
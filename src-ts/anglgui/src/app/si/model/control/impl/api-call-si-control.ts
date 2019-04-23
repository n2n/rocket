
import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiService } from "src/app/op/model/si.service";

export class ApiCallSiControl implements SiControl {
	
	constructor(public apiCallId: string, public siButton: SiButton) {
	}
	
	getButton(): SiButton {
		return this.siButton;
	}
	
	exec(siService: SiService) {
		
	}
}
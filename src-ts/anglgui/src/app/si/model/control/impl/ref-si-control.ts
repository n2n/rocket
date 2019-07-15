
import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { Router } from "@angular/router";
import { PlatformLocation } from "@angular/common";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { SiLayer } from "src/app/si/model/structure/si-layer";

export class RefSiControl implements SiControl {
	
	constructor(public url: string, public siButton: SiButton, public layer: SiLayer) {
	}
	
	isLoading(): boolean {
		return false;
	}
	
	getButton(): SiButton {
		return this.siButton;
	}
	
	exec(siCommanderService: SiCommanderService) {
		siCommanderService.navigate(this.url, this.layer);
	}
}
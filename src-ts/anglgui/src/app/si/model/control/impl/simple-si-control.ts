import { SiControl } from "src/app/si/model/control/si-control";
import { SiButton } from "src/app/si/model/control/si-button";
import { SiCommanderService } from "src/app/si/model/si-commander.service";
import { SiZone } from "src/app/si/model/structure/si-zone";

export class SimpleSiControl implements SiControl {
	
	constructor(public siButton: SiButton, public callback: () => any) {
	}
	
	getButton(): SiButton {
		return this.siButton;
	}
	
	isLoading(): boolean {
		return false;
	}
	
	exec(siZone: SiZone, siCommanderService: SiCommanderService) {
		this.callback();
	}
}
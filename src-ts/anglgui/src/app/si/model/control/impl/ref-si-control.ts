
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/si-button';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiZone } from 'src/app/si/model/structure/si-zone';

export class RefSiControl implements SiControl {

	constructor(public url: string, public siButton: SiButton) {
	}

	isLoading(): boolean {
		return false;
	}

	getButton(): SiButton {
		return this.siButton;
	}

	exec(siZone: SiZone, siCommanderService: SiCommanderService) {
		siCommanderService.navigate(this.url, siZone.layer);
	}
}

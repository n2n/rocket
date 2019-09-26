import { SiButton } from 'src/app/si/model/control/si-button';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiZone } from "src/app/si/model/structure/si-zone";

export interface SiControl {

	getButton(): SiButton;

	isLoading(): boolean;

	exec(zone: SiZone, siCommanderService: SiCommanderService);
}

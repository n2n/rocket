import { SiButton } from 'src/app/si/model/control/si-button';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';

export interface SiControl {

	getButton(): SiButton;

	isLoading(): boolean;

	exec(siCommanderService: SiCommanderService);
}

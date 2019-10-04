import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { SiButton } from '../model/si-button';
import { UiZone } from '../../../structure/ui-zone';

export interface ButtonControlModel {
	getSiButton(): SiButton;
	isLoading(): boolean;
	exec(siUiService: SiUiService, uiZone: UiZone): void;
}

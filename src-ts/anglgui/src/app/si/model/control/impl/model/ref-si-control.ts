
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { UiZone } from 'src/app/si/model/structure/ui-zone';
import { ButtonControlModel } from '../comp/button-control-model';
import { UiContent } from '../../../structure/ui-content';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';

export class RefSiControl implements SiControl, ButtonControlModel {

	constructor(public url: string, public siButton: SiButton) {
	}

	isLoading(): boolean {
		return false;
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	exec(siUiService: SiUiService, uiZone: UiZone) {
		siUiService.navigate(this.url, uiZone.layer);
	}

	createUiContent(): UiContent {
		return new ButtonControlUiContent(this);
	}
}

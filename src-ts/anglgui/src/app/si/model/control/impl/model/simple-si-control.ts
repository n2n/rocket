import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { ButtonControlModel } from '../comp/button-control-model';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';

export class SimpleSiControl implements SiControl, ButtonControlModel {

	constructor(public siButton: SiButton, public callback: () => any) {
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	isLoading(): boolean {
		return false;
	}

	exec(siUiService: SiUiService, uiZone: UiZone) {
		this.callback();
	}

	createUiContent(): UiContent {
		return new ButtonControlUiContent(this);
	}
}

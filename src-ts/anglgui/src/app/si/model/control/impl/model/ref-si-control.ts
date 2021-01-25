
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlModel } from '../comp/button-control-model';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundry } from '../../si-control-bountry';

export class RefSiControl implements SiControl, ButtonControlModel {

	constructor(public siUiService: SiUiService, public url: string, public siButton: SiButton,
			public controlBoundry: SiControlBoundry) {
	}

	isLoading(): boolean {
		return false;
	}

	isDisabled(): boolean {
		return !!this.controlBoundry.getControlledEntries().find(siEntry => siEntry.isClaimed());
	}

	getSiButton(): SiButton {
		return this.siButton;
	}

	exec(uiZone: UiZone, _subKey: string|null) {
		this.siUiService.navigateByUrl(this.url, uiZone.layer);
	}

	createUiContent(uiZone: UiZone): UiContent {
		return new ButtonControlUiContent(this, uiZone);
	}
}

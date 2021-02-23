
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundry } from '../../si-control-bountry';

export class RefSiControl implements SiControl {

	constructor(public siUiService: SiUiService, public url: string, public siButton: SiButton,
			public controlBoundry: SiControlBoundry) {
	}


	isDisabled(): boolean {
		return !!this.controlBoundry.getControlledEntries().find(siEntry => siEntry.isClaimed());
	}

	exec(uiZone: UiZone) {
		this.siUiService.navigateByUrl(this.url, uiZone.layer);
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		return new ButtonControlUiContent({
			getUiZone,
			getSiButton: () => this.siButton,
			isDisabled: () => this.isDisabled(),
			isLoading: () => false,
			exec: () => this.exec(getUiZone())
		});
	}
}

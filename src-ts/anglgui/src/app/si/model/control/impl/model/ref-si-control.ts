import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundary } from '../../si-control-boundary';

export class RefSiControl implements SiControl {

	constructor(public siUiService: SiUiService, public url: string, public newWindow: boolean, public siButton: SiButton,
			public controlBoundary: SiControlBoundary) {
	}


	isDisabled(): boolean {
		return !!this.controlBoundary.getBoundValueBoundaries().find(siValueBoundary => siValueBoundary.isClaimed());
	}

	exec(uiZone: UiZone): void {
		if (!this.newWindow){
			this.siUiService.navigateByUrl(this.url, uiZone.layer);
			return;
		}

		this.siUiService.loadZone(uiZone.layer.container.createLayer().pushRoute(null, this.url).zone, true);
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		if (!!this.newWindow && !!this.siButton.href) {
			this.siButton.target = '_blank';
		}

		return new ButtonControlUiContent({
			getUiZone,
			getSiButton: () => this.siButton,
			isDisabled: () => this.isDisabled(),
			isLoading: () => false,
			exec: () => this.exec(getUiZone())
		});
	}
}

import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundry } from '../../si-control-bountry';
import {Aconfig} from '../comp/button-control-model';

export class RefSiControl implements SiControl {
  public href = false;
  public newWindow = false;

	constructor(public siUiService: SiUiService, public url: string, public siButton: SiButton, public controlBoundry: SiControlBoundry) {
	}


	isDisabled(): boolean {
		return !!this.controlBoundry.getControlledEntries().find(siEntry => siEntry.isClaimed());
	}

	exec(uiZone: UiZone) {
    if (this.href) {
      return false;
    }

    if (this.newWindow) {
      const popUpZone = uiZone.layer.container.createLayer().pushRoute(null, this.url).zone;
      this.siUiService.loadZone(popUpZone, false);
      return true;
    }

		if (!uiZone.layer.main){
			this.siUiService.navigateByUrl(this.url, uiZone.layer);
			return true;
		}

    return false;
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
	 	return new ButtonControlUiContent({
			getUiZone,
			getSiButton: () => this.siButton,
			isDisabled: () => this.isDisabled(),
      getAconfig: () => this.createAconfig(),
			isLoading: () => false,
			exec: () => this.exec(getUiZone())
    });
	}

	private createAconfig(): Aconfig {
    return {newWindow: this.newWindow, routerLinked: !this.href, url: this.url};
  }
}


import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { Observable } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiUiService as SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlModel } from '../comp/button-control-model';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiControlBoundry } from '../../si-control-bountry';

export class ApiCallSiControl implements SiControl, ButtonControlModel {

	inputSent = false;
	private loading = false;
	private entryBoundFlag: boolean;

	constructor(public siUiService: SiUiService, public apiUrl: string, public apiCallId: object,
			public button: SiButton, public controlBoundry: SiControlBoundry, public entry: SiEntry|null = null) {
	}

	getSiButton(): SiButton {
		return this.button;
	}

	isLoading(): boolean {
		return this.loading;
	}

	set entryBound(entryBound: boolean) {
		if (this.entry && !entryBound) {
			throw new IllegalSiStateError('Control must be bound to entry.');
		}

		this.entryBoundFlag = entryBound;
	}

	get entryBound(): boolean {
		return !!this.entry || this.entryBoundFlag;
	}

	exec(uiZone: UiZone) {
		let obs: Observable<void>;

		if (this.entry) {
			obs = this.siUiService.execEntryControl(this.apiUrl, this.apiCallId, this.entry, this.inputSent);
		} else if (this.entryBound) {
			obs = this.siUiService.execSelectionControl(this.apiUrl, this.apiCallId, this.controlBoundry, this.controlBoundry.getSelectedEntries(),
					this.inputSent);
		} else {
			obs = this.siUiService.execControl(this.apiUrl, this.apiCallId, this.controlBoundry, this.inputSent, uiZone.layer);
		}

		this.loading = true;
		obs.subscribe(() => {
			this.loading = false;
		});
	}

	createUiContent(uiZone: UiZone): UiContent {
		return new ButtonControlUiContent(this, uiZone);
	}
}

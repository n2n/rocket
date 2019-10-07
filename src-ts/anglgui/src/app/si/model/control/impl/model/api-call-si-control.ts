
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { UiZone } from 'src/app/si/model/structure/ui-zone';
import { SiComp } from 'src/app/si/model/content/si-comp';
import { Observable } from 'rxjs';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiUiService as SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlModel } from '../comp/button-control-model';
import { UiContent } from '../../../structure/ui-content';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';

export class ApiCallSiControl implements SiControl, ButtonControlModel {

	inputSent = false;
	private loading = false;
	private entryBoundFlag: boolean;

	constructor(public apiUrl: string, public apiCallId: object, public button: SiButton,
			public comp: SiComp, public entry: SiEntry|null = null) {
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

	exec(commandService: SiUiService) {
		let obs: Observable<void>;

		if (this.entry) {
			obs = commandService.execEntryControl(this.apiUrl, this.apiCallId, this.entry, this.inputSent);
		} else if (this.entryBound) {
			obs = commandService.execSelectionControl(this.apiUrl, this.apiCallId, this.comp, this.comp.getSelectedEntries(),
					this.inputSent);
		} else {
			obs = commandService.execControl(this.apiUrl, this.apiCallId, this.comp, this.inputSent);
		}

		this.loading = true;
		obs.subscribe(() => {
			this.loading = false;
		});
	}

	createUiContent(): UiContent {
		return new ButtonControlUiContent(this);
	}
}

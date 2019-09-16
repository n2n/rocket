
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/si-button';
import { Router } from '@angular/router';
import { PlatformLocation } from '@angular/common';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiService } from 'src/app/si/model/si.service';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';
import { SiComp } from 'src/app/si/model/entity/si-comp';
import { Observable } from 'rxjs';

export class ApiCallSiControl implements SiControl {

	inputSent = false;
	private loading = false;
	private entryBoundFlag: boolean;

	constructor(public apiUrl: string, public apiCallId: object, public button: SiButton,
			public comp: SiComp, public entry: SiEntry|null = null) {
	}

	getButton(): SiButton {
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

	exec(commandService: SiCommanderService) {
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
}

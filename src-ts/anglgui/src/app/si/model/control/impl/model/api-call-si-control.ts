
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiUiService as SiUiService } from 'src/app/si/manage/si-ui.service';
import { ButtonControlUiContent } from '../comp/button-control-ui-content';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiControlBoundary } from '../../si-control-boundary';

export class ApiCallSiControl implements SiControl {

	inputSent = false;
	private loading = false;
	// private entryBoundFlag: boolean;

	constructor(public siUiService: SiUiService, public maskId: string|null, public entryId: string|null,
			public controlName: string, public button: SiButton, public controlBoundary: SiControlBoundary) {
	}

	getSiButton(): SiButton {
		return this.button;
	}

	isLoading(): boolean {
		return this.loading;
	}

	isDisabled(): boolean {
		return !!this.controlBoundary.getBoundValueBoundaries().find(siValueBoundary => siValueBoundary.isClaimed())
				|| !this.controlBoundary.getBoundApiUrl();
	}

	// set entryBound(entryBound: boolean) {
	// 	if (this.entry && !entryBound) {
	// 		throw new IllegalSiStateError('Control must be bound to entry.');
	// 	}

	// 	this.entryBoundFlag = entryBound;
	// }

	// get entryBound(): boolean {
	// 	return !!this.entry || this.entryBoundFlag;
	// }

	exec(uiZone: UiZone): void {
		const locks = this.controlBoundary.getBoundValueBoundaries().map(entry => entry.createLock());

		const obs = this.siUiService.execControl(this.maskId, this.entryId, this.controlName,
				this.controlBoundary, this.inputSent, uiZone.layer);
		this.loading = true;
		obs.subscribe(() => {
			locks.forEach((lock) => { lock.release(); });

			this.loading = false;
		});
	}

	createUiContent(getUiZone: () => UiZone): UiContent {
		return new ButtonControlUiContent({
			exec: (/*subKey: string|null*/) => this.exec(getUiZone()),
			getUiZone,
			isDisabled: () => this.isDisabled(),
			isLoading: () => this.isLoading(),
			getSiButton: () => this.getSiButton()
		});
	}
}

import { Component, OnInit, Input } from '@angular/core';
import { ButtonControlModel } from '../button-control-model';
import { SiButton } from '../../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { filter } from 'rxjs/operators';

@Component({
	selector: 'rocket-button-control',
	templateUrl: './button-control.component.html',
	styleUrls: ['./button-control.component.css']
})
export class ButtonControlComponent implements OnInit {

	@Input()
	model: ButtonControlModel;
	@Input()
	uiZone: UiZone;

	private _subVisible = false;

	constructor() {
	}

	ngOnInit() {
	}

	get siButton(): SiButton {
		return this.model.getSiButton();
	}

	get loading(): boolean {
		return this.model.isLoading();
	}

	get disabled() {
		return this.model.isDisabled() || this.loading;
	}

	hasSubSiButtons() {
		return !!this.model.getSubSiButtonMap && this.model.getSubSiButtonMap().size > 0;
	}

	get subSiButtonMap() {
		return this.model.getSubSiButtonMap();
	}

	get subVisible(): boolean {
		return this._subVisible && !this.disabled && this.hasSubSiButtons();
	}

	exec() {
		if (this.hasSubSiButtons()) {
			this._subVisible = !this._subVisible;
			return;
		}

		const siConfirm = this.model.getSiButton().confirm;

		if (!siConfirm) {
			this.model.exec(this.uiZone, null);
			return;
		}

		this.uiZone.createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel)
				.confirmed$.pipe(filter(confirmed => confirmed)).subscribe(() => {
					this.model.exec(this.uiZone, null);
				});
	}

	subExec(key: string) {
		this._subVisible = false;

		const siConfirm = this.model.getSubSiButtonMap().get(key).confirm;

		if (!siConfirm) {
			this.model.exec(this.uiZone, key);
			return;
		}

		this.uiZone.createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel)
				.confirmed$.pipe(filter(confirmed => confirmed)).subscribe((confirmed) => {
					this.model.exec(this.uiZone, key);
				});
	}


}

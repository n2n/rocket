import { Component, OnInit, Input } from '@angular/core';
import { ButtonControlModel } from '../button-control-model';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { SiButton } from '../../model/si-button';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

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

	get loading() {
		return this.model.isLoading();
	}

	hasSubSiButtons() {
		return !!this.model.getSubSiButtonMap && this.model.getSubSiButtonMap().size > 0;
	}

	get subSiButtonMap() {
		return this.model.getSubSiButtonMap();
	}

	get subVisible(): boolean {
		return this._subVisible && !this.loading && this.hasSubSiButtons();
	}

	exec() {
		if (this.hasSubSiButtons()) {
			this._subVisible = !this._subVisible;
			return;
		}

		this.model.exec(this.uiZone, null);
	}

	subExec(key: string) {
		console.log('subex');
		this._subVisible = false;
		this.model.exec(this.uiZone, key);
	}
}

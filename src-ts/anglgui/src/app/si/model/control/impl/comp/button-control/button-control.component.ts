import { Component, OnInit, Input } from '@angular/core';
import { ButtonControlModel } from '../button-control-model';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';
import { SiButton } from '../../model/si-button';

@Component({
	selector: 'rocket-button-control',
	templateUrl: './button-control.component.html',
	styleUrls: ['./button-control.component.css']
})
export class ButtonControlComponent implements OnInit {

	@Input()
	model: ButtonControlModel;
	@Input()
	uiStructue: UiStructure;

	constructor(private siUiService: SiUiService) {

	}

	ngOnInit() {
	}

	get siButton(): SiButton {
		return this.model.getSiButton();
	}

	get loading() {
		return this.model.isLoading();
	}

	exec() {
		this.model.exec(this.siUiService, this.uiStructue.getZone());
	}

}

import { Component, OnInit, DoCheck, Input, ComponentFactoryResolver, ElementRef, OnDestroy } from '@angular/core';
import { UiZone, UiZoneModel } from '../../model/ui-zone';
import { UiStructure } from '../../model/ui-structure';
import { UiZoneError } from '../../model/ui-zone-error';
import { UiContent } from '../../model/ui-content';

@Component({
	selector: 'rocket-ui-zone',
	templateUrl: './zone.component.html',
	styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck, OnDestroy {

	@Input() uiZone: UiZone;

	uiStructure: UiStructure;
	uiZoneErrors: UiZoneError[] = [];

	asideCommandUiContents: UiContent[] = [];

	constructor(private elemRef: ElementRef) {
	}

	ngOnInit() {
		this.uiStructure = new UiStructure(null, this.uiZone, null);
	}

	ngOnDestroy() {
		this.uiStructure.dispose();
	}

	ngDoCheck() {
		this.uiZoneErrors = this.uiStructure.getZoneErrors();

		if (this.hasUiZoneErrors()) {
			this.elemRef.nativeElement.classList.add('rocket-contains-additional');
		} else {
			this.elemRef.nativeElement.classList.remove('rocket-contains-additional');
		}

		if (this.uiZone.model) {
			this.uiStructure.model = this.uiZone.model.structureModel;
			this.asideCommandUiContents = this.uiZone.model.structureModel.getAsideContents();
		} else {
			this.uiStructure.model = null;
			this.asideCommandUiContents = [];
		}
	}

	get uiZoneModel(): UiZoneModel|null {
		return this.uiZone.model;
	}

	hasUiZoneErrors() {
		return this.uiZoneErrors.length > 0;
	}

	get partialCommandUiContents(): UiContent[] {
		return this.uiZone.model.partialCommandContents || [];
	}

	get mainCommandUiContents(): UiContent[] {
		return this.uiZone.model.mainCommandContents || [];
	}


}

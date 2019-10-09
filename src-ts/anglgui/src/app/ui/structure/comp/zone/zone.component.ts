import { Component, OnInit, DoCheck, Input, ComponentFactoryResolver, ElementRef, OnDestroy } from '@angular/core';
import { UiZone } from '../../model/ui-zone';
import { UiStructure } from '../../model/ui-structure';
import { UiZoneError } from '../../model/ui-zone-error';

@Component({
	selector: 'rocket-ui-zone',
	templateUrl: './zone.component.html',
	styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck, OnDestroy {

	@Input() uiZone: UiZone;

	uiStructure: UiStructure;
	uiZoneErrors: UiZoneError[] = [];

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
		} else {
			this.uiStructure.model = null;
		}
	}

	hasUiZoneErrors() {
		return this.uiZoneErrors.length > 0;
	}
}

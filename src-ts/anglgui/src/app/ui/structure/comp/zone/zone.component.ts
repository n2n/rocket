import { Component, OnInit, DoCheck, Input, ComponentFactoryResolver, ElementRef, OnDestroy } from '@angular/core';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { UiStructure } from 'src/app/si/model/structure/si-structure';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';

@Component({
	selector: 'rocket-ui-zone',
	templateUrl: './zone.component.html',
	styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck, OnDestroy {

	@Input() siZone: SiZone;

	uiStructure: UiStructure;
	siZoneErrors: SiZoneError[] = [];

	constructor(private elemRef: ElementRef) {
	}

	ngOnInit() {
		this.uiStructure = new UiStructure(null, this.siZone, null);
	}

	ngOnDestroy() {
		this.uiStructure.dispose();
	}

	ngDoCheck() {
		this.siZoneErrors = this.uiStructure.getZoneErrors();

		if (this.hasSiZoneErrors()) {
			this.elemRef.nativeElement.classList.add('rocket-contains-additional');
		} else {
			this.elemRef.nativeElement.classList.remove('rocket-contains-additional');
		}

		if (this.siZone.model) {
			this.uiStructure.model = this.siZone.model.structureModel;
		} else {
			this.uiStructure.model = null;
		}
	}

	hasSiZoneErrors() {
		return this.siZoneErrors.length > 0;
	}
}



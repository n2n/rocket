import { Component, OnInit, DoCheck, Input, ComponentFactoryResolver, ElementRef } from '@angular/core';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';

@Component({
  selector: 'rocket-ui-zone',
  templateUrl: './zone.component.html',
  styleUrls: ['./zone.component.css']
})
export class ZoneComponent implements OnInit, DoCheck {

	@Input() siZone: SiZone;

	siStructure: SiStructure;
	siZoneErrors: SiZoneError[] = [];

	constructor(private elemRef: ElementRef) {
		this.siStructure = new SiStructure(null, this.siZone, null, this.siZone.model.title,
				this.siZone.model.structureModel);
	}

	ngOnInit() {
	}

	ngDoCheck() {
		this.siZoneErrors = this.siStructure.getZoneErrors();

		if (this.hasSiZoneErrors()) {
			this.elemRef.nativeElement.classList.add('rocket-contains-additional');
		} else {
			this.elemRef.nativeElement.classList.remove('rocket-contains-additional');
		}
	}

	hasSiZoneErrors() {
		return this.siZoneErrors.length > 0;
	}
}



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

	siZoneErrors: SiZoneError[] = [];

	constructor(private elemRef: ElementRef) {

	}

	ngOnInit() {
	}

	ngDoCheck() {
		this.siZoneErrors = this.siZone.structure.getZoneErrors();

		if (this.hasSiZoneErrors()) {
			this.elemRef.nativeElement.classList.add('rocket-contains-additional');
		} else {
			this.elemRef.nativeElement.classList.remove('rocket-contains-additional');
		}

	}

	hasSiZoneErrors() {
		return this.siZoneErrors.length > 0;
	}

	get siStructure(): SiStructure {
		return this.siZone.structure;
	}
}



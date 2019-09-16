import { Component, OnInit } from '@angular/core';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiControl } from 'src/app/si/model/control/si-control';
import { BulkyEntrySiComp } from 'src/app/si/model/entity/impl/basic/bulky-entry-si-comp';

@Component({
  selector: 'rocket-bulky-entry',
  templateUrl: './bulky-entry.component.html'
})
export class BulkyEntryComponent implements OnInit {

	public siContent: BulkyEntrySiComp;

	constructor() { }

	ngOnInit() {
	}

	get siEntry(): SiEntry {
		return this.siContent.entry;
	}

	get siControlMap(): SiControl[] {
		return this.siContent.controls;
	}

}

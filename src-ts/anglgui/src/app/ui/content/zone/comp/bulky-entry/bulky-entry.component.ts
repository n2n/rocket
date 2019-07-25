import { Component, OnInit } from '@angular/core';
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { BulkyEntrySiComp } from "src/app/si/model/structure/impl/bulky-entry-si-content";

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

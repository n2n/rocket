import { Component, OnInit } from '@angular/core';
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";

@Component({
  selector: 'rocket-dl-zone-content',
  templateUrl: './dl-zone-content.component.html'
})
export class DlZoneContentComponent implements OnInit {

	public bulkyEntrySiContent: BulkyEntrySiContent;

	constructor() { }

	ngOnInit() {
	}
	
	get siEntry(): SiEntry {
		return this.bulkyEntrySiContent.entry;
	}
	
	get siControlMap(): Map<string, SiControl> {
		return this.bulkyEntrySiContent.controlMap;
	}

}

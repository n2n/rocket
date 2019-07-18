import { Component, OnInit } from '@angular/core';
import { DlSiContent } from "src/app/si/model/structure/impl/dl-si-zone-content";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructureContent } from "src/app/si/model/structure/si-structure-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";

@Component({
  selector: 'rocket-dl-zone-content',
  templateUrl: './dl-zone-content.component.html'
})
export class DlZoneContentComponent implements OnInit {

	public dlSiContent: DlSiContent;

	constructor() { }

	ngOnInit() {
	}
	
	get siEntries(): SiEntry[] {
		return this.dlSiContent.entries;
	}
	
	get siControlMap(): Map<string, SiControl> {
		return this.dlSiContent.controlMap;
	}

}

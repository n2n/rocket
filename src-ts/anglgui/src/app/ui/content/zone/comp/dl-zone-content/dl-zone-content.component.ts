import { Component, OnInit } from '@angular/core';
import { DlSiZoneContent } from "src/app/si/model/structure/impl/dl-si-zone-content";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";

@Component({
  selector: 'rocket-dl-zone-content',
  templateUrl: './dl-zone-content.component.html',
  styleUrls: ['./dl-zone-content.component.css']
})
export class DlZoneContentComponent implements OnInit {

	public dlSiZoneContent: DlSiZoneContent;

	constructor() { }

	ngOnInit() {
	}
	
	get siEntries(): SiEntry[] {
		return this.dlSiZoneContent.entries;
	}
	
	get siFieldStructureDeclarations(): SiFieldStructureDeclaration[] {
		return this.dlSiZoneContent.bulkyDeclaration.fieldStructureDeclaration;
	}
	
	get siControlMap(): Map<string, SiControl> {
		return this.dlSiZoneContent.bulkyDeclaration.controlMap;
	}

}

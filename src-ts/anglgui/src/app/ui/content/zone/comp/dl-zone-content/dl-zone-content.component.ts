import { Component, OnInit } from '@angular/core';
import { DlSiZoneContent } from "src/app/si/model/structure/impl/dl-si-zone-content";
import { SiFieldStructureDeclaration } from "src/app/si/model/structure/si-field-structure-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiControl } from "src/app/si/model/control/si-control";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { FieldSiStructure } from "src/app/si/model/structure/impl/field-si-structure";

@Component({
  selector: 'rocket-dl-zone-content',
  templateUrl: './dl-zone-content.component.html'
})
export class DlZoneContentComponent implements OnInit {

	public dlSiZoneContent: DlSiZoneContent;
	
	constructor() { }

	ngOnInit() {
	}
	
	get fieldSiStructures(): FieldSiStructure[] {
		let structures: FieldSiStructure[] = [];
			
		for (let siEntry of this.siEntries) {
			structures.push(new FieldSiStructure(siEntry, this.getFieldStructureDeclaration(siEntry)));
		}
		
		return structures;
	}
	
	get siEntries(): SiEntry[] {
		return this.dlSiZoneContent.entries;
	}
	
	getFieldStructureDeclaration(entry: SiEntry): SiFieldStructureDeclaration {
		return this.dlSiZoneContent.bulkyDeclaration.getFieldStructureDeclarationByBuildupId(entry.selectedBuildupId);
	}
	
	get siControlMap(): Map<string, SiControl> {
		return this.dlSiZoneContent.bulkyDeclaration.controlMap;
	}

}

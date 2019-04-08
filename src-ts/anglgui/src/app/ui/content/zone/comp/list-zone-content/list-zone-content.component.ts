import { Component, OnInit, Input } from '@angular/core';
import { ListSiZone } from "src/app/si/model/structure/impl/list-si-zone";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	listSiZone: ListSiZone;
	
	constructor() { }

	ngOnInit() {
	}
	
	pickSiFields(siEntry: SiEntry): SiField[] {
		const siFields: Array<SiField> = [];
		for (const siFieldDeclaration of this.listSiZone.fieldDeclarations) {
			const siField = siEntry.getSiFieldById(siFieldDeclaration.siFieldId)
			if (siField) {
				siFields.push(siField);				
			}
		}
		
		return siFields;
	}
}

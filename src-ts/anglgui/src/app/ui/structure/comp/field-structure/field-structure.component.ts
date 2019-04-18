import { Component, OnInit, Input, ElementRef } from '@angular/core';
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration, SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";

@Component({
  selector: 'rocket-ui-field-structure',
  templateUrl: './field-structure.component.html',
  styleUrls: ['./field-structure.component.css']
})
export class FieldStructureComponent implements OnInit {

	@Input()
	siEntry: SiEntry;
	@Input()
	siFieldStructureDeclaration: SiFieldStructureDeclaration;
	
	constructor(private elRef: ElementRef) { 
		
	}

	ngOnInit() {
		const classList = this.elRef.nativeElement.classList
		
		switch (this.siFieldStructureDeclaration.type) {
			case SiStructureType.ITEM:
				classList.add('rocket-item');
				break;
			case SiStructureType.SIMPLE_GROUP:
			case SiStructureType.AUTONOMIC_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-simple-group');
				break;
			case SiStructureType.MAIN_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-main-group');
				break;
			case SiStructureType.LIGHT_GROUP:
				classList.add('rocket-group');
				classList.add('rocket-light-group');
				break;
			case SiStructureType.PANEL:
			default:
				classList.add('rocket-panel');
				break;
		}
	}
	
	get label() {
		return this.siFieldStructureDeclaration.fieldDeclaration.label;
	}

}

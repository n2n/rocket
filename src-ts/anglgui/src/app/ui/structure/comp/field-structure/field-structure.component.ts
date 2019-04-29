import { Component, OnInit, Input, ElementRef } from '@angular/core';
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiFieldStructureDeclaration, SiStructureType } from "src/app/si/model/structure/si-field-structure-declaration";
import { FieldSiStructure } from "src/app/si/model/structure/impl/field-si-structure";

@Component({
  selector: 'rocket-field-structure',
  templateUrl: './field-structure.component.html'
})
export class FieldStructureComponent implements OnInit {
	static $i = 0;
	
	public fieldSiStructure: FieldSiStructure;
	
	constructor() {}

    ngOnInit() {
    	console.log('init--');
    	console.log(this.fieldSiStructure.fieldStructureDeclaration.children);
    }
	
    get siEntry(): SiEntry {
    	return this.fieldSiStructure.entry;
    }
  
    get siFieldStructureDeclaration(): SiFieldStructureDeclaration {
    	return this.fieldSiStructure.fieldStructureDeclaration;
    }
//    
    get children(): FieldSiStructure[] {
    	return this.fieldSiStructure.children;
    }
}

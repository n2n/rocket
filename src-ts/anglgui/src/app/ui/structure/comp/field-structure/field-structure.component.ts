import { Component, OnInit, ElementRef } from '@angular/core';
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';

@Component({
  selector: 'rocket-field-structure',
  templateUrl: './field-structure.component.html'
})
export class FieldStructureComponent implements OnInit {

	public fieldSiStructureContent: any;

	constructor() {
// 		elemRef.nativeElement.classList.add('rocket-control');
	}

	ngOnInit() {
	}

	get siEntry(): SiEntry {
		return this.fieldSiStructureContent.entry;
	}

	get siFieldDeclaration(): SiFieldDeclaration {
		return this.fieldSiStructureContent.fieldDeclaration;
	}
//
//    get children(): SiStructure[] {
//    	return this.fieldSiStructureContent.children;
//    }
}

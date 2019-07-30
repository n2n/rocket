import { Component, OnInit, ElementRef } from '@angular/core';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiFieldDeclaration } from 'src/app/si/model/structure/si-field-declaration';
import { FieldSiStructureContent } from 'src/app/si/model/structure/impl/field-si-structure-content';

@Component({
  selector: 'rocket-field-structure',
  templateUrl: './field-structure.component.html'
})
export class FieldStructureComponent implements OnInit {

	public fieldSiStructureContent: FieldSiStructureContent;

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

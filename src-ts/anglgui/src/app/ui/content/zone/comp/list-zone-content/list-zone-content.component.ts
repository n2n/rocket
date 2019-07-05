import { Component, OnInit, Input } from '@angular/core';
import { ListSiZoneContent } from "src/app/si/model/structure/impl/list-si-zone-content";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiService } from "src/app/si/model/si.service";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";
import { SiGetInstruction } from "src/app/si/model/api/si-get-instruction";
import { Router } from "@angular/router";

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	siService: SiService
	listSiZone: ListSiZoneContent;
	
	private fieldDeclarations: Array<SiFieldDeclaration>|null = null;

	constructor(private router: Router) { 
		
	}

	ngOnInit() {
		if (this.listSiZone.compactDeclaration) {
			return;
		}
		
		this.siService.apiGet(this.listSiZone.getApiUrl(),
				new SiGetRequest(SiGetInstruction.partialContent(false, true, 0, this.listSiZone.pageSize)));
	}
	
	getFieldDeclarations(): Array<SiFieldDeclaration>|null {
		if (this.fieldDeclarations) {
			return this.fieldDeclarations
		}
		
		if (this.listSiZone.compactDeclaration) {
			this.fieldDeclarations = this.listSiZone.compactDeclaration.getBasicFieldDeclarations(); 
		}
		
		return this.fieldDeclarations;
	}
}

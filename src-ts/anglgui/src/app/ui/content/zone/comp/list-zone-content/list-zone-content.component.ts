import { Component, OnInit, Input, Inject, Injector } from '@angular/core';
import { ListSiZoneContent } from "src/app/si/model/structure/impl/list-si-zone-content";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiField } from "src/app/si/model/content/si-field";
import { SiFieldDeclaration } from "src/app/si/model/structure/si-field-declaration";
import { SiService } from "src/app/si/model/si.service";
import { SiGetRequest } from "src/app/si/model/api/si-get-request";
import { SiGetInstruction } from "src/app/si/model/api/si-get-instruction";
import { Router } from "@angular/router";
import { SiGetResponse } from "src/app/si/model/api/si-get-response";
import { SiGetResult } from "src/app/si/model/api/si-get-result";
import { SiPartialContent } from "src/app/si/model/content/si-partial-content";
import { SiPage } from "src/app/si/model/structure/impl/si-page";

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	model: any;
	siService: SiService;
	
	private fieldDeclarations: Array<SiFieldDeclaration>|null = null;

	constructor() { 
		
	}

	ngOnInit() {
		if (this.model.setup) {
			return;
		}
		
		this.loadPage(1);
	}
	
	private loadPage(pageNo: number) {
		const getRequest = new SiGetRequest(SiGetInstruction.partialContent(false, true, 
				(pageNo - 1) * this.model.pageSize, pageNo * this.model.pageSize));
		this.siService.apiGet(this.model.getApiUrl(), getRequest, this.model.getZone(), this.model)
				.subscribe((getResponse: SiGetResponse) => {
					const result = getResponse.results[0];
					
					if (result.compactDeclaration) {
						this.model.compactDeclaration = result.compactDeclaration;
					}
					
					this.initPage(pageNo, <SiPartialContent> getResponse.results[0].partialContent);
				});
	}
	
	private initPage(pageNo: number, partialContent: SiPartialContent) {
		this.model.size = partialContent.count;
		this.model.putPage(new SiPage(pageNo, partialContent.entries));
	}
	
	getFieldDeclarations(): Array<SiFieldDeclaration>|null {
		if (this.fieldDeclarations) {
			return this.fieldDeclarations
		}
		
		if (this.model.compactDeclaration) {
			this.fieldDeclarations = this.model.compactDeclaration.getBasicFieldDeclarations(); 
		}
		
		return this.fieldDeclarations;
	}
}

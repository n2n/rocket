import { Component, OnInit, Input, Inject, Injector, ElementRef } from '@angular/core';
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
import { fromEvent, Subscription } from "rxjs";

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit {

	model: ListSiZoneContent;
	siService: SiService;
	
	private subscription: Subscription;
	private fieldDeclarations: Array<SiFieldDeclaration>|null = null;

	constructor(private elemRef: ElementRef) {
	}

	ngOnInit() {
		this.subscription = fromEvent<MouseEvent>(window, 'scroll').subscribe((event: MouseEvent) => {
			this.handleScroll(event.pageY);
		});
		
		if (this.model.setup) {
			return;
		}
		
		this.loadPage(1, true);
	}
	
	ngOnDestroy() {
		this.subscription.unsubscribe();
	}
	
	private loadPage(pageNo: number, requestDeclaration: boolean) {
		let siPage: SiPage;
		if (this.model.containsPageNo(pageNo)) {
			siPage = this.model.getPageByNo(pageNo);
		} else {
			siPage = new SiPage(pageNo, null, null);
			this.model.putPage(siPage);
		}
		
		const instruction = SiGetInstruction.partialContent(false, true, 
						(pageNo - 1) * this.model.pageSize, pageNo * this.model.pageSize)
				.setDeclarationRequested(requestDeclaration)
		const getRequest = new SiGetRequest(instruction);
		
		this.siService.apiGet(this.model.getApiUrl(), getRequest, this.model.getZone(), this.model)
				.subscribe((getResponse: SiGetResponse) => {
					this.applyResult(getResponse.results[0], siPage)
				});
	}
	
	private applyResult(result: SiGetResult, siPage: SiPage) {
		if (result.compactDeclaration) {
			this.model.compactDeclaration = result.compactDeclaration;
		}
		
		this.model.size = result.partialContent.count;
		siPage.entries = result.partialContent.entries;
	}
	
	private handleScroll(pageY: number) {
		if ((window.scrollY + window.innerHeight) < document.body.offsetHeight) {
			return;
		}
		
		
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

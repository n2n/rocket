import { Component, OnInit, ElementRef, OnDestroy } from '@angular/core';
import { SiFieldDeclaration } from 'src/app/si/model/entity/si-field-declaration';
import { SiService } from 'src/app/si/model/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { SiPage } from 'src/app/si/model/entity/impl/basic/si-page';
import { fromEvent, Subscription } from 'rxjs';
import { SiQualifier } from 'src/app/si/model/entity/si-qualifier';
import { EntriesListSiContent } from 'src/app/si/model/entity/impl/basic/entries-list-si-content';

@Component({
  selector: 'rocket-ui-list-zone-content',
  templateUrl: './list-zone-content.component.html',
  styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit, OnDestroy {

	model: EntriesListSiContent;
	siService: SiService;

	private subscription: Subscription;
	private fieldDeclarations: Array<SiFieldDeclaration>|null = null;

	constructor() {
	}

	ngOnInit() {
		this.subscription = fromEvent<MouseEvent>(window, 'scroll').subscribe(() => {
			this.updateVisiblePages();
		});

		if (!this.model.setup) {
			const loadedPage = this.loadPage(1);
			loadedPage.offsetHeight = 0;
			this.model.currentPageNo = 1;
			return;
		}

		const page = this.model.currentPage;
		if (!page.visible) {
			page.offsetHeight = 0;
		}
	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
	}

	get loading(): boolean {
		return !this.model.getLastVisiblePage().loaded;
	}

	private loadPage(pageNo: number): SiPage {
		let siPage: SiPage;
		if (this.model.containsPageNo(pageNo)) {
			siPage = this.model.getPageByNo(pageNo);
		} else {
			siPage = new SiPage(pageNo, null, null);
			this.model.putPage(siPage);
		}

		const instruction = SiGetInstruction.partialContent(this.model, false, true,
						(pageNo - 1) * this.model.pageSize, this.model.pageSize)
				.setDeclarationRequested(!this.model.entryDeclaration)
				.setControlsIncluded(true);
		const getRequest = new SiGetRequest(instruction);

		this.siService.apiGet(this.model.getApiUrl(), getRequest, this.model.getZone())
				.subscribe((getResponse: SiGetResponse) => {
					this.applyResult(getResponse.results[0], siPage);
				});

		return siPage;
	}

	private applyResult(result: SiGetResult, siPage: SiPage) {
		if (result.entryDeclaration) {
			this.model.entryDeclaration = result.entryDeclaration;
		}

		this.model.size = result.partialContent.count;
		siPage.entries = result.partialContent.entries;

		this.updateCurrentPage();
	}

	private updateCurrentPage() {
		let page = this.model.getBestPageByOffsetHeight(window.scrollY);
		if (page) {
			this.model.currentPageNo = page.num;
			return;
		}

		if (this.model.containsPageNo(this.model.currentPageNo)) {
			page = this.model.getPageByNo(this.model.currentPageNo);
		} else {
			page = this.loadPage(this.model.currentPageNo);
		}
		page.offsetHeight = 0;
	}

	private updateVisiblePages() {
		this.updateCurrentPage();

		if ((window.scrollY + window.innerHeight) < document.body.offsetHeight) {
			return;
		}

		const lastVisiblePage = this.model.getLastVisiblePage();
		if (!lastVisiblePage.loaded) {
			return;
		}

		const newPageNo = lastVisiblePage.num + 1;
		if (newPageNo > this.model.pagesNum) {
			return;
		}

		let newSiPage: SiPage;
		if (this.model.containsPageNo(newPageNo)) {
			newSiPage = this.model.getPageByNo(newPageNo);
		} else {
			newSiPage = this.loadPage(newPageNo);
		}
		this.updateOffsetHeight(newSiPage);
	}

	private updateOffsetHeight(siPage: SiPage) {
		siPage.offsetHeight = window.scrollY + window.innerHeight; /* document.body.offsetHeight - window.innerHeight;*/
// 		console.log(siPage.number + ' ' + document.body.offsetHeight + ' ' + (window.scrollY + window.innerHeight));
	}

	getFieldDeclarations(): Array<SiFieldDeclaration>|null {
		if (this.fieldDeclarations) {
			return this.fieldDeclarations;
		}

		if (this.model.entryDeclaration) {
			this.fieldDeclarations = this.model.entryDeclaration.getBasicFieldDeclarations();
		}

		return this.fieldDeclarations;
	}

	get currentPageNo(): number {
		return this.model.currentPageNo;
	}

	set currentPageNo(currentPageNo: number) {
		if (this.model.pagesNum < currentPageNo || 0 > currentPageNo) {
			return;
		}

		if (!this.model.containsPageNo(currentPageNo)) {
			this.model.hideAllPages();
			this.loadPage(currentPageNo).offsetHeight = 0;
			this.model.currentPageNo = currentPageNo;
			return;
		}

		const page = this.model.getPageByNo(currentPageNo);
		if (page.visible) {
			window.scrollTo(window.scrollX, page.offsetHeight);
// 			this.model.currentPageNo = currentPageNo
			return;
		}

		this.model.hideAllPages();
		page.offsetHeight = 0;
		this.model.currentPageNo = currentPageNo;
	}

	get selectable(): boolean {
		return !!this.model.qualifierSelection;
	}

	get singleSelect(): boolean {
		return this.model.qualifierSelection.max == 1;
	}

	toggleSelection(qualifier: SiQualifier) {
		if (this.singleSelect) {
			this.model.qualifierSelection.selectedQualfiers = [qualifier];
			return;
		}

		const i = this.model.qualifierSelection.selectedQualfiers.findIndex((selectedQualifier) => {
			return qualifier.equals(selectedQualifier);
		});

		if (i != -1) {
			this.model.qualifierSelection.selectedQualfiers.splice(i, 1);
			return;
		}

		if (this.areMoreSelectable()) {
			this.model.qualifierSelection.selectedQualfiers.push(qualifier);
		}
	}

	isSelected(qualifier: SiQualifier) {
		return undefined !== this.model.qualifierSelection.selectedQualfiers.find((selectedQualifier) => {
			return qualifier.equals(selectedQualifier);
		});
	}

	areMoreSelectable(): boolean {
		return this.model.qualifierSelection.max === null
				|| this.model.qualifierSelection.selectedQualfiers.length < this.model.qualifierSelection.max;
	}

	saveSelection() {
		this.model.qualifierSelection.done();
	}

	cancelSelection() {
		this.model.qualifierSelection.cancel();
	}

// 	static radioNameIndex = 0;
//
// 	private _radioName: string
//
// 	get radioName(): string {
// 		if (!this._radioName) {
// 			this._radioName = 'list-si-select-' + (ListZoneContentComponent.radioNameIndex++);
// 		}
//
// 		return this._radioName;
// 	}
}

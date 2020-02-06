import { Component, OnInit, OnDestroy } from '@angular/core';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { fromEvent, Subscription } from 'rxjs';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { EntriesListModel } from '../entries-list-model';
import { SiPageCollection } from '../../model/si-page-collection';
import { StructurePage, StructurePageManager } from './structure-page-manager';
import { SiService } from 'src/app/si/manage/si.service';
import { skip } from 'rxjs/operators';

@Component({
	selector: 'rocket-ui-list-zone-content',
	templateUrl: './list-zone-content.component.html',
	styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit, OnDestroy {

	uiStructure: UiStructure;
	model: EntriesListModel;

	public spm: StructurePageManager;
	private subscription = new Subscription();
	siPageCollection: SiPageCollection;

	constructor(private siService: SiService) {
	}

	ngOnInit() {
		this.siPageCollection = this.model.getSiPageCollection();
		this.spm = new StructurePageManager(this);

		this.subscription.add(fromEvent<MouseEvent>(window, 'scroll').subscribe(() => {
			this.updateVisiblePages();
		}));

		const pc = this.model.getSiPageCollection();

		this.subscription.add(this.siPageCollection.currentPageNo$.pipe(skip(1)).subscribe((currentPageNo) => {
			this.valCurrentPageNo(currentPageNo);
		}));

		if (!pc.setup) {
			const loadedPage = this.loadPage(1);
			loadedPage.offsetHeight = 0;
			this.siPageCollection.currentPageNo = 1;
			return;
		}

		const page = this.siPageCollection.currentPage;
		if (!page.visible) {
			page.offsetHeight = 0;
		}
	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
		this.spm.clear();
	}

	get declared(): boolean {
		return !!this.siPageCollection.declaration;
	}

	get loading(): boolean {
		return !this.siPageCollection.getLastVisiblePage().loaded;
	}

	getVisibleStructurePages(): StructurePage[] {
		return this.spm.map(this.siPageCollection.getVisiblePages());
	}

	private loadPage(pageNo: number): SiPage {
		let siPage: SiPage;
		if (this.siPageCollection.containsPageNo(pageNo)) {
			siPage = this.siPageCollection.getPageByNo(pageNo);
		} else {
			siPage = new SiPage(pageNo, null, null);
			this.siPageCollection.putPage(siPage);
		}

		const instruction = SiGetInstruction.partialContent(this.model.getSiComp(), false, true,
						(pageNo - 1) * this.siPageCollection.pageSize, this.siPageCollection.pageSize)
				.setDeclaration(this.siPageCollection.declaration)
				.setControlsIncluded(true);
		const getRequest = new SiGetRequest(instruction);

		this.siService.apiGet(this.model.getApiUrl(), getRequest)
				.subscribe((getResponse: SiGetResponse) => {
					this.applyResult(getResponse.results[0], siPage);
				});

		return siPage;
	}

	private applyResult(result: SiGetResult, siPage: SiPage) {
		if (result.declaration) {
			this.siPageCollection.declaration = result.declaration;
		}

		this.siPageCollection.size = result.partialContent.count;
		siPage.entries = result.partialContent.entries;

		this.updateCurrentPage();
	}

	private updateCurrentPage() {
		let page = this.siPageCollection.getBestPageByOffsetHeight(window.scrollY);
		if (page) {
			this.siPageCollection.currentPageNo = page.num;
			return;
		}

		if (this.siPageCollection.containsPageNo(this.siPageCollection.currentPageNo)) {
			page = this.siPageCollection.getPageByNo(this.siPageCollection.currentPageNo);
		} else {
			page = this.loadPage(this.siPageCollection.currentPageNo);
		}
		page.offsetHeight = 0;
	}

	private updateVisiblePages() {
		this.updateCurrentPage();

		if ((window.scrollY + window.innerHeight) < document.body.offsetHeight) {
			return;
		}

		const lastVisiblePage = this.siPageCollection.getLastVisiblePage();
		if (!lastVisiblePage.loaded) {
			return;
		}

		const newPageNo = lastVisiblePage.num + 1;
		if (newPageNo > this.siPageCollection.pagesNum) {
			return;
		}

		let newSiPage: SiPage;
		if (this.siPageCollection.containsPageNo(newPageNo)) {
			newSiPage = this.siPageCollection.getPageByNo(newPageNo);
		} else {
			newSiPage = this.loadPage(newPageNo);
		}
		this.updateOffsetHeight(newSiPage);
	}

	private updateOffsetHeight(siPage: SiPage) {
		siPage.offsetHeight = window.scrollY + window.innerHeight; /* document.body.offsetHeight - window.innerHeight;*/
	// 		console.log(siPage.number + ' ' + document.body.offsetHeight + ' ' + (window.scrollY + window.innerHeight));
	}

	getSiProps(): Array<SiProp>|null {
		return this.siPageCollection.declaration.getBasicTypeDeclaration().getSiProps();
	}

	private valCurrentPageNo(currentPageNo: number) {
		if (!this.siPageCollection.currentPageExists) {
			this.siPageCollection.hideAllPages();
			this.loadPage(currentPageNo).offsetHeight = 0;
			this.siPageCollection.currentPageNo = currentPageNo;
			return;
		}

		const page = this.siPageCollection.getPageByNo(currentPageNo);
		if (page.visible) {
			window.scrollTo(window.scrollX, page.offsetHeight);
	// 			this.model.currentPageNo = currentPageNo
			return;
		}

		this.siPageCollection.hideAllPages();
		page.offsetHeight = 0;
		this.siPageCollection.currentPageNo = currentPageNo;
	}

	get selectable(): boolean {
		return !!this.model.getSiEntryQualifierSelection();
	}

	get singleSelect(): boolean {
		return this.model.getSiEntryQualifierSelection().max === 1;
	}

	toggleSelection(qualifier: SiEntryQualifier) {
		if (!this.selectable) {
			return;
		}

		if (this.singleSelect) {
			this.model.getSiEntryQualifierSelection().selectedQualfiers = [qualifier];
			return;
		}

		const i = this.model.getSiEntryQualifierSelection().selectedQualfiers.findIndex((selectedQualifier) => {
			return qualifier.equals(selectedQualifier);
		});

		if (i !== -1) {
			this.model.getSiEntryQualifierSelection().selectedQualfiers.splice(i, 1);
			return;
		}

		if (this.areMoreSelectable()) {
			this.model.getSiEntryQualifierSelection().selectedQualfiers.push(qualifier);
		}
	}

	isSelected(qualifier: SiEntryQualifier) {
		return undefined !== this.model.getSiEntryQualifierSelection().selectedQualfiers.find((selectedQualifier) => {
			return qualifier.equals(selectedQualifier);
		});
	}

	areMoreSelectable(): boolean {
		return this.model.getSiEntryQualifierSelection().max === null
				|| this.model.getSiEntryQualifierSelection().selectedQualfiers.length < this.model.getSiEntryQualifierSelection().max;
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

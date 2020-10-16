import { Component, OnInit, OnDestroy, Host, Inject } from '@angular/core';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { fromEvent, Subscription, Subject } from 'rxjs';
import { SiEntryQualifier } from 'src/app/si/model/content/si-entry-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { EntriesListModel } from '../entries-list-model';
import { SiPageCollection } from '../../model/si-page-collection';
import { StructurePage, StructurePageManager } from './structure-page-manager';
import { SiService } from 'src/app/si/manage/si.service';
import { skip, debounceTime, tap } from 'rxjs/operators';
import { LayerComponent } from 'src/app/ui/structure/comp/layer/layer.component';

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
	private quickSearchSubject = new Subject<string>();
	private quickSearching = false;
	private weakPageNoChange = false;

	constructor(private siService: SiService, @Inject(LayerComponent)  private parent: LayerComponent) {
	}

	ngOnInit() {
		this.siPageCollection = this.model.getSiPageCollection();
		this.spm = new StructurePageManager(this);

		this.subscription.add(fromEvent<MouseEvent>(this.parent.nativeElement, 'scroll').subscribe(() => {
			if (this.quickSearching) {
				return;
			}

			this.updateVisiblePages();
		}));

		this.subscription.add(this.siPageCollection.currentPageNo$.pipe(skip(1)).subscribe(() => {
			if (this.weakPageNoChange) {
				this.weakPageNoChange = false;
				return;
			}

			if (this.quickSearching) {
				return;
			}

			this.valCurrentPageNo();
		}));

		this.quickSearchSubject
				.pipe(tap(() => {
					this.quickSearching = true;
					this.siPageCollection.clear();
				}))
				.pipe(debounceTime(300))
				.subscribe((str: string) => {
					this.quickSearching = false;
					if (this.siPageCollection.quickSearchStr === str) {
						this.visibileLoadCurrentPage();
					}
				});

		if (!this.siPageCollection.currentPageExists) {
			this.visibileLoadCurrentPage();
			return;
		}

		const page = this.siPageCollection.currentPage;
		if (!page.visible) {
			page.offsetHeight = 0;
		}
	}

	private visibileLoadCurrentPage() {
		if (this.siPageCollection.size !== 0) {
			throw new Error('SiPageCollection filled.');
		}

		const loadedPage = this.loadPage(this.siPageCollection.currentPageNo);
		loadedPage.offsetHeight = 0;
	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
		this.spm.clear();
	}

	get declared(): boolean {
		return !!this.siPageCollection.declaration;
	}

	get loading(): boolean {
		if (this.quickSearching) {
			return true;
		}

		const lastVisiblePage = this.siPageCollection.getLastVisiblePage();
		if (lastVisiblePage && !lastVisiblePage.loaded) {
			return true;
		}

		return false;
	}

	getVisibleStructurePages(): StructurePage[] {
		return this.spm.map(this.siPageCollection.getVisiblePages());
	}

	get quickSearchStr(): string {
		return this.siPageCollection.quickSearchStr;
	}

	set quickSearchStr(quickSearchStr: string) {
		if (quickSearchStr === '') {
			quickSearchStr = null;
		}

		if (this.quickSearchStr === quickSearchStr) {
			return;
		}

		this.siPageCollection.quickSearchStr = quickSearchStr;
		this.quickSearchSubject.next(quickSearchStr);
	}

	private loadPage(pageNo: number): SiPage {
		let siPage: SiPage;
		if (this.siPageCollection.containsPageNo(pageNo)) {
			siPage = this.siPageCollection.getPageByNo(pageNo);
			siPage.entries = null;
		} else {
			siPage = new SiPage(pageNo, null, null);
			this.siPageCollection.putPage(siPage);
		}

		const instruction = SiGetInstruction.partialContent(false, true,
						(pageNo - 1) * this.siPageCollection.pageSize, this.siPageCollection.pageSize,
						this.quickSearchStr)
				.setDeclaration(this.siPageCollection.declaration)
				.setGeneralControlsIncluded(!this.model.areGeneralControlsInitialized())
				.setEntryControlsIncluded(true);
		const getRequest = new SiGetRequest(instruction);

		this.siService.apiGet(this.model.getApiUrl(), getRequest, this.model.getSiControlBoundry())
				.subscribe((getResponse: SiGetResponse) => {
					this.applyResult(getResponse.results[0], siPage);
				});

		return siPage;
	}

	private applyResult(result: SiGetResult, siPage: SiPage) {
		if (result.declaration) {
			this.siPageCollection.declaration = result.declaration;
		}

		if (result.generalControls) {
			this.model.applyGeneralControls(result.generalControls);
		}

		this.siPageCollection.size = result.partialContent.count;
		siPage.entries = result.partialContent.entries;

		this.updateCurrentPage();
	}

	private changePageNoWeakly(pageNo: number) {
		if (this.siPageCollection.currentPageNo === pageNo) {
			return;
		}

		this.weakPageNoChange = true;
		this.siPageCollection.currentPageNo = pageNo;
	}

	private updateCurrentPage() {
		let page = this.siPageCollection.getBestPageByOffsetHeight(this.parent.nativeElement.scrollTop);
		if (page) {
			this.changePageNoWeakly(page.no);
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

		if ((this.parent.nativeElement.scrollTop + this.parent.nativeElement.offsetHeight)
				< this.parent.nativeElement.scrollHeight) {
			return;
		}

		const lastVisiblePage = this.siPageCollection.getLastVisiblePage();
		if (!lastVisiblePage.loaded) {
			return;
		}

		const newPageNo = lastVisiblePage.no + 1;
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
		siPage.offsetHeight = this.parent.nativeElement.scrollTop
				+ this.parent.nativeElement.offsetHeight; /* document.body.offsetHeight - window.innerHeight;*/
	// 		console.log(siPage.number + ' ' + document.body.offsetHeight + ' ' + (window.scrollY + window.innerHeight));
	}

	getSiProps(): Array<SiProp>|null {
		return this.siPageCollection.declaration.getBasicTypeDeclaration().getSiProps();
	}

	private valCurrentPageNo() {
		if (!this.siPageCollection.currentPageExists) {
			this.siPageCollection.hideAllPages();
			this.loadPage(this.siPageCollection.currentPageNo).offsetHeight = 0;
			return;
		}

		const page = this.siPageCollection.getPageByNo(this.siPageCollection.currentPageNo);
		if (page.visible) {
			this.parent.nativeElement.scrollTo({ top: page.offsetHeight, behavior: 'smooth' });
				// this.parent.nativeElement.scrollLeft, page.offsetHeight);
	// 			this.model.currentPageNo = currentPageNo
			return;
		}

		this.siPageCollection.hideAllPages();
		page.offsetHeight = 0;
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

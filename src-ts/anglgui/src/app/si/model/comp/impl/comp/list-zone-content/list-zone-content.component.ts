import { Component, OnInit, OnDestroy } from '@angular/core';
import { SiFieldDeclaration } from 'src/app/si/model/content/si-field-declaration';
import { SiService } from 'src/app/si/model/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { SiGetResult } from 'src/app/si/model/api/si-get-result';
import { fromEvent, Subscription } from 'rxjs';
import { SiEntryQualifier } from 'src/app/si/model/content/si-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiPage } from '../../model/si-page';
import { SiTypeDeclaration } from 'src/app/si/model/meta/si-type-declaration';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { EntriesListSiComp } from '../../model/entries-list-si-content';
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiUiStructureModelFactory } from '../../model/si-ui-structure-model-factory';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { EntriesListModel } from '../entries-list-model';

@Component({
	selector: 'rocket-ui-list-zone-content',
	templateUrl: './list-zone-content.component.html',
	styleUrls: ['./list-zone-content.component.css']
})
export class ListZoneContentComponent implements OnInit, OnDestroy {

	uiStructure: UiStructure;
	model: EntriesListModel;

	public spm: StructurePageManager;
	private subscription: Subscription;
	private fieldDeclarations: Array<SiFieldDeclaration>|null = null;

	constructor(private siService: SiService) {
	}

	ngOnInit() {
		this.spm = new StructurePageManager(this);

		this.subscription = fromEvent<MouseEvent>(window, 'scroll').subscribe(() => {
			this.updateVisiblePages();
		});

		if (!this.spm.setup) {
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

	getVisibleStructurePages(): StructurePage[] {

	}

	private loadPage(pageNo: number): StructurePage {
		let siPage: SiPage;
		if (this.spm.containsPageNo(pageNo)) {
			siPage = this.model.getPageByNo(pageNo);
		} else {
			siPage = new SiPage(pageNo, null, null);
			this.model.putPage(siPage);
		}

		const instruction = SiGetInstruction.partialContent(this.model, false, true,
						(pageNo - 1) * this.model.pageSize, this.model.pageSize)
				.setDeclaration(this.model.declaration)
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
			this.model.declaration = result.declaration;
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

		if (this.model.declaration) {
			this.fieldDeclarations = this.model.declaration.getBasicFieldDeclarations();
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
		return this.model.qualifierSelection.max === 1;
	}

	toggleSelection(qualifier: SiEntryQualifier) {
		if (this.singleSelect) {
			this.model.qualifierSelection.selectedQualfiers = [qualifier];
			return;
		}

		const i = this.model.qualifierSelection.selectedQualfiers.findIndex((selectedQualifier) => {
			return qualifier.equals(selectedQualifier);
		});

		if (i !== -1) {
			this.model.qualifierSelection.selectedQualfiers.splice(i, 1);
			return;
		}

		if (this.areMoreSelectable()) {
			this.model.qualifierSelection.selectedQualfiers.push(qualifier);
		}
	}

	isSelected(qualifier: SiEntryQualifier) {
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

class StructurePageManager {
	private	siTypeDeclaration: SiTypeDeclaration;
	private pagesMap = new Map<number, StructurePage>();

	constructor(private comp: ListZoneContentComponent) {
		this.siTypeDeclaration = comp.model.getSiDeclaration().getBasicSiTypeDeclaration()

		for (const siPage of comp.model.getSiPages()) {
			this.setupUiStructures(this.createPage(siPage.num), siPage);
		}
	}

	get setup(): boolean {
		return !!(this.model.getDeclaration() && this.pagesMap.size > 0);
	}

	getSiProps(): SiProp[] {
		return this.siTypeDeclaration.getSiProps();
	}

	private ensureNoFree(no: number) {
		if (this.pagesMap.has(no)) {
			throw new IllegalSiStateError('Page no alread exists: ' no);
		}
	}

	containsPageNo(pageNo: number): boolean {
		return this.pagesMap.has(pageNo);
	}

	createPage(no: number): StructurePage {
		this.ensureNoFree(no);
		const sp = new StructurePage(no, null, null);
		this.pagesMap.set(no, sp);
		return sp;
	}

	initPage(structurePage: StructurePage, entries: SiEntry[]) {
		const siPage = new SiPage(structurePage.no, entries);
		this.comp.model.addSiPage(siPage);
		this.setupUiStructures(structurePage, siPage);
	}

	private setupUiStructures(structurePage: StructurePage, siPage: SiPage) {
		if (structurePage.uiStructures) {
			throw new IllegalSiStateError('Page already contains structures: ' + structurePage.siPage.num);
		}

		structurePage.siPage = siPage;

		const structures = new Array<Array<UiStructure>>();
		for (const siEntry of siPage.entries) {
			structures.push(this.createFieldUiStructures(siEntry));
		}
		structurePage.uiStructures = structures;
	}

	private createFieldUiStructures(siEntry: SiEntry): UiStructure[] {
		const uiStructures = new Array<UiStructure>();

		for (const siProp of this.siTypeDeclaration.getSiProps()) {
			const uiStructure = this.comp.uiStructure.createChild();
			uiStructure.model = SiUiStructureModelFactory.createCompactField(siEntry.selectedEntryBuildup.getFieldById(siProp.id));
			uiStructures.push(uiStructure);
		}

		return uiStructures;
	}

	getVisiblePages(): SiPage[] {
		return this.pagesMap.filter((page: StructurePage) => {
			return page.offsetHeight !== null;
		});
	}

	getLastVisiblePage(): SiPage|null {
		let lastPage: SiPage|null = null;
		for (const page of this.pagesMap.values()) {
			if (page.offsetHeight !== null && (lastPage === null || page.num > lastPage.num)) {
				lastPage = page;
			}
		}
		return lastPage;
	}

	getBestPageByOffsetHeight(offsetHeight: number): SiPage|null {
		let prevPage: SiPage|null = null;

		for (const page of this.getVisiblePages()) {
			if (prevPage === null || (prevPage.offsetHeight < offsetHeight
					&& prevPage.offsetHeight <= page.offsetHeight)) {
				prevPage = page;
				continue;
			}

			const bestPageDelta = offsetHeight - prevPage.offsetHeight;
			const pageDelta = page.offsetHeight - offsetHeight;

			if (bestPageDelta < pageDelta) {
				return prevPage;
			} else {
				return page;
			}
		}

		return prevPage;
	}

	hideAllPages() {
		for (const page of this.pagesMap.values()) {
			page.offsetHeight = null;
		}
	}
}

class StructurePage {
	public uiStructures: Array<Array<UiStructure>>|null = null;

	constructor(readonly no: number, public offsetHeight: number|null) {

	}

	get loaded(): boolean {
		return !!this.siPage.entries;
	}

	get visible(): boolean {
		return this.offsetHeight !== null;
	}

}

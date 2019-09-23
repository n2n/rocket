
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiComp } from 'src/app/si/model/entity/si-comp';
import { ViewContainerRef, ComponentFactoryResolver, ComponentRef } from '@angular/core';
import { ListZoneContentComponent } from 'src/app/ui/content/zone/comp/list-zone-content/list-zone-content.component';
import { IllegalSiStateError } from 'src/app/si/model/illegal-si-state-error';
import { SiEntryDeclaration } from 'src/app/si/model/entity/si-entry-declaration';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiZoneError } from 'src/app/si/model/structure/si-zone-error';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiPage } from 'src/app/si/model/entity/impl/basic/si-page';
import { SiQualifier } from 'src/app/si/model/entity/si-qualifier';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';
import { SiStructure } from "src/app/si/model/structure/si-structure";

export class EntriesListSiContent implements SiComp, SiContent {

	private pagesMap = new Map<number, SiPage>();
	private _size = 0;
	private _currentPageNo = 1;
	public entryDeclaration: SiEntryDeclaration|null = null;
	public qualifierSelection: SiQualifierSelection|null = null;

	constructor(public apiUrl: string, public pageSize: number) {
// 		this.qualifierSelection = {
// 			min: 0,
// 			max: 1,
// 			selectedQualfiers: [],
//
// 			done: () => { },
//
// 			cancel: () => { }
// 		}
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getEntries(): SiEntry[] {
		const entries = [];
		for (const [, page] of this.pagesMap) {
			entries.push(...page.entries);
		}
		return entries;
	}

	getSelectedEntries(): SiEntry[] {
		throw new Error('Method not implemented.');
	}

	getZoneErrors(): SiZoneError[] {
		const zoneErrors: SiZoneError[] = [];

		for (const entry of this.getEntries()) {
			zoneErrors.push(...entry.getZoneErrors());
		}

		return zoneErrors;
	}

	get pages(): SiPage[] {
		return Array.from(this.pagesMap.values());
	}

	get currentPage(): SiPage {
		return this.getPageByNo(this._currentPageNo);
	}

	get currentPageNo(): number {
		this.ensureSetup();
		return this._currentPageNo;
	}

	set currentPageNo(currentPageNo: number) {
		if (currentPageNo > this.pagesNum) {
			throw new IllegalSiStateError('CurrentPageNo too large: ' + currentPageNo);
		}

		if (!this.getPageByNo(currentPageNo).visible) {
			throw new IllegalSiStateError('Page not visible: ' + currentPageNo);
		}

		this._currentPageNo = currentPageNo;
	}

	get size(): number {
		return this._size as number;
	}

	set size(size: number) {
		this._size = size;

		if (!this.setup) {
			return;
		}

		const pagesNum = this.pagesNum;

		if (this._currentPageNo > pagesNum) {
			this._currentPageNo = pagesNum;
		}

		for (const pageNo of this.pagesMap.keys()) {
			if (pageNo > pagesNum) {
				this.pagesMap.delete(pageNo);
			}
		}
	}

	get setup(): boolean {
		return !!(this.entryDeclaration && this.pagesMap.size > 0);
	}

	private ensureSetup() {
		if (this.setup) { return; }

		throw new IllegalSiStateError('ListSiZone not set up.');
	}

	putPage(page: SiPage) {
		if (page.num > this.pagesNum) {
			throw new IllegalSiStateError('Page num to high.');
		}

		this.pagesMap.set(page.num, page);
	}

	getVisiblePages(): SiPage[] {
		return this.pages.filter((page: SiPage) => {
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

	containsPageNo(number: number): boolean {
		return this.pagesMap.has(number);
	}

	getPageByNo(no: number): SiPage {
		if (this.containsPageNo(no)) {
			return this.pagesMap.get(no) as SiPage;
		}

		throw new IllegalSiStateError('Unknown page with no: ' + no);
	}

	get pagesNum(): number {
		return Math.ceil(this.size as number / this.pageSize) || 1;
	}

	getStructureModel(): SiStructureModel {
		return this;
	}

	reload() {
	}

	initComponent(viewContainerRef: ViewContainerRef,
			componentFactoryResolver: ComponentFactoryResolver,
			siStructure: SiStructure): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(ListZoneContentComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		componentRef.instance.model = this;
		componentRef.instance.siStructure = siStructure;

		return componentRef;
	}

	getContent() {
		return this;
	}

	getChildren() {
		return [];
	}

	getControls() {
		return [];
	}
}

interface SiQualifierSelection {
	min: number;
	max: number|null;
	selectedQualfiers: SiQualifier[];

	done: () => any;

	cancel: () => any;
}


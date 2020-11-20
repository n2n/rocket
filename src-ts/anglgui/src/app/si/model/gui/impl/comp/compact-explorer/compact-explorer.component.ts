import { Component, OnInit, OnDestroy, Inject, NgZone } from '@angular/core';
import { Subscription, Subject } from 'rxjs';
import { SiEntryQualifier } from 'src/app/si/model/content/si-entry-qualifier';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { CompactExplorerModel } from '../compact-explorer-model';
import { StructurePage, StructurePageManager } from './structure-page-manager';
import { debounceTime, tap } from 'rxjs/operators';
import { LayerComponent } from 'src/app/ui/structure/comp/layer/layer.component';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { NgSafeScrollListener } from 'src/app/util/zone/ng-safe-scroll-listener';

@Component({
	selector: 'rocket-ui-compact-explorer',
	templateUrl: './compact-explorer.component.html',
	styleUrls: ['./compact-explorer.component.css']
})
export class CompactExplorerComponent implements OnInit, OnDestroy {

	uiStructure: UiStructure;
	model: CompactExplorerModel;

	public spm: StructurePageManager;
	private subscription = new Subscription();

	private quickSearchSubject = new Subject<string>();
	private quickSearching = false;

	private _currentPageNo = 1;


	constructor(@Inject(LayerComponent) private parent: LayerComponent, private ngZone: NgZone) {
	}

	ngOnInit() {
		this.spm = new StructurePageManager(this.uiStructure, this.model.getSiPageCollection());

		new NgSafeScrollListener(this.parent.nativeElement, this.ngZone).trottled$(100).subscribe(() => {
			if (this.quickSearching) {
				return;
			}

			this.updateVisiblePages();
		});

		// this.subscription.add(fromEvent<MouseEvent>(this.parent.nativeElement, 'scroll').subscribe(() => {
		// 	if (this.quickSearching) {
		// 		return;
		// 	}

		// 	this.updateVisiblePages();
		// }));

		this.quickSearchSubject
				.pipe(tap(() => {
					this.quickSearching = true;
				}))
				.pipe(debounceTime(300))
				.subscribe((str: string) => {
					if (this.spm.quickSearchStr === str) {
						this.quickSearching = false;
						this.ensureLoaded();
					}
				});

		this.ensureLoaded();
	}

	ngOnDestroy() {
		this.subscription.unsubscribe();
		this.spm.clear();
	}

	private ensureLoaded() {
		if (this.spm.loadingRequired) {
			this.spm.loadSingle(this.currentPageNo, 0);
		}
	}

	get loading(): boolean {
		if (this.quickSearching) {
			return true;
		}

		return this.spm.lastPage && !this.spm.lastPage.loaded;
	}

	getVisibleStructurePages(): StructurePage[] {
		return this.spm.pages;
	}

	get quickSearchStr(): string {
		return this.spm.quickSearchStr;
	}

	set quickSearchStr(quickSearchStr: string) {
		if (quickSearchStr === '') {
			quickSearchStr = null;
		}

		if (this.quickSearchStr === quickSearchStr) {
			return;
		}

		this.spm.updateFilter(quickSearchStr);
		this.quickSearchSubject.next(quickSearchStr);
	}

	get currentPageNo(): number {
		return this._currentPageNo;
	}

	set currentPageNo(currentPageNo: number) {
		if (currentPageNo === this._currentPageNo) {
			return;
		}

		if (currentPageNo > this.pagesNum || currentPageNo < 1) {
			throw new IllegalStateError('CurrentPageNo too large or too small: ' + currentPageNo);
		}

		this._currentPageNo = currentPageNo;

		if (this.spm.containsPageNo(currentPageNo)) {
			this.parent.nativeElement.scrollTo({
				top: this.spm.getPageByNo(currentPageNo).offsetHeight,
				behavior: 'smooth'
			});
			return;
		}

		this.spm.loadSingle(currentPageNo, 0);
	}

	get pagesNum(): number {
		if (this.spm.declarationRequired) {
			return 1;
		}

		return this.spm.possiablePagesNum;
	}

	private updateCurrentPage() {
		const structurePage = this.spm.getBestPageByOffsetHeight(this.parent.nativeElement.scrollTop);
		if (structurePage) {
			this._currentPageNo = structurePage.siPage.no;
			return;
		}
	}

	private updateVisiblePages() {
		if ((this.parent.nativeElement.scrollTop + this.parent.nativeElement.offsetHeight)
				< this.parent.nativeElement.scrollHeight) {
			this.updateCurrentPage();
			return;
		}

		const lastPage = this.spm.lastPage;
		if (lastPage && !lastPage.loaded) {
			return;
		}

		this._currentPageNo = this.spm.loadNext(this.parent.nativeElement.scrollTop
				+ this.parent.nativeElement.offsetHeight).siPage.no - 1;
	}

	get declared(): boolean {
		return !this.spm.declarationRequired;
	}

	getSiProps(): Array<SiProp> {
		return this.spm.getSiProps();
	}

	// private valCurrentPageNo() {
	// 	if (!this.siPageCollection.currentPageExists) {
	// 		this.siPageCollection.hideAllPages();
	// 		this.siPageCollection.loadPage(this.siPageCollection.currentPageNo).offsetHeight = 0;
	// 		return;
	// 	}

	// 	const page = this.siPageCollection.getPageByNo(this.siPageCollection.currentPageNo);
	// 	if (page.visible) {
	// 		this.parent.nativeElement.scrollTo({ top: page.offsetHeight, behavior: 'smooth' });
	// 			// this.parent.nativeElement.scrollLeft, page.offsetHeight);
	// // 			this.model.currentPageNo = currentPageNo
	// 		return;
	// 	}

	// 	this.siPageCollection.hideAllPages();
	// 	page.offsetHeight = 0;
	// }

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
// 			this._radioName = 'list-si-select-' + (CompactExplorerComponent.radioNameIndex++);
// 		}
//
// 		return this._radioName;
// 	}
}

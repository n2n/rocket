import { Component, Inject, NgZone, OnDestroy, OnInit } from '@angular/core';
import { Subject, Subscription } from 'rxjs';
import { SiEntryIdentifier, SiEntryQualifier } from 'src/app/si/model/content/si-entry-qualifier';
import { SiProp } from 'src/app/si/model/meta/si-prop';
import { CompactExplorerModel } from '../compact-explorer-model';
import { StructurePage, StructurePageManager } from './structure-page-manager';
import { debounceTime, tap } from 'rxjs/operators';
import { LayerComponent } from 'src/app/ui/structure/comp/layer/layer.component';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { NgSafeScrollListener } from 'src/app/util/zone/ng-safe-scroll-listener';
import { CdkDragDrop } from '@angular/cdk/drag-drop';
import { SiValueBoundary } from 'src/app/si/model/content/si-value-boundary';

@Component({
	selector: 'rocket-ui-compact-explorer',
	templateUrl: './compact-explorer.component.html'
})
export class CompactExplorerComponent implements OnInit, OnDestroy {


	constructor(@Inject(LayerComponent) private parent: LayerComponent, private ngZone: NgZone) {
	}

	get loading(): boolean {
		if (this.quickSearching) {
			return true;
		}

		return !!this.spm.lastPage && !this.spm.lastPage.loaded;
	}

	get sortable(): boolean {
		return this.spm.sortable;
	}

	get quickSearchStr(): string|null {
		return this.spm.quickSearchStr;
	}

	set quickSearchStr(quickSearchStr: string|null) {
		if (quickSearchStr === '') {
			quickSearchStr = null;
		}

		if (this.quickSearchStr === quickSearchStr) {
			return;
		}

		this.pCurrentPageNo = 1;
		this.spm.updateFilter(quickSearchStr);
		this.quickSearchSubject.next(quickSearchStr);
	}

	get currentPageNo(): number {
		return this.pCurrentPageNo;
	}

	set currentPageNo(currentPageNo: number) {
		if (currentPageNo === this.pCurrentPageNo) {
			return;
		}

		if (currentPageNo > this.pagesNum || currentPageNo < 1) {
			throw new IllegalStateError('CurrentPageNo too large or too small: ' + currentPageNo);
		}

		this.pCurrentPageNo = currentPageNo;

		if (this.spm.containsPageNo(currentPageNo)) {
			this.parent.nativeElement.scrollTo({
				top: this.spm.getPageByNo(currentPageNo).offsetHeight!,
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

	get declared(): boolean {
		return !this.spm.declarationRequired;
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

	model!: CompactExplorerModel;

	private subscription = new Subscription();

	private quickSearchSubject = new Subject<string|null>();
	private quickSearching = false;

	private pCurrentPageNo = 1;

	private sortModeEnabled = false;

	private sortSelectedMap = new Map<string, { identifier: SiEntryIdentifier, decendantIdStrs: string[] }>();

	get spm(): StructurePageManager {
		return this.model.getStructurePageManager();
	}

	ngOnInit(): void {

		new NgSafeScrollListener(this.parent.nativeElement, this.ngZone).trottled$(500).subscribe(() => {
			if (this.quickSearching) {
				return;
			}

			this.updateVisiblePages();
		});


		this.subscription.add(this.model.getCurrentPageNo$().subscribe((pageNo) => {
			this.currentPageNo = pageNo;
		}));

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
				.subscribe((str: string|null) => {
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

	getVisibleStructurePages(): StructurePage[] {
		return this.spm.pages;
	}

	private updateCurrentPage() {
		const structurePage = this.spm.getBestPageByOffsetHeight(this.parent.nativeElement.scrollTop);
		if (structurePage) {
			this.pCurrentPageNo = structurePage.siPage.no;
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

		if (lastPage && lastPage.siPage.no === this.pagesNum) {
			return;
		}

		this.pCurrentPageNo = this.spm.loadNext(this.parent.nativeElement.scrollTop
				+ this.parent.nativeElement.offsetHeight).siPage.no - 1;
	}

	getSiProps(): Array<SiProp> {
		return this.spm.getSiProps();
	}

	toggleSelection(qualifier: SiEntryQualifier): void {
		if (!this.selectable) {
			return;
		}

		const selection = this.model.getSiEntryQualifierSelection();

		if (this.singleSelect) {
			selection.setSelectedQualifiers([qualifier]);
			return;
		}

		if (selection.isQualifierSelected(qualifier)) {
			this.model.getSiEntryQualifierSelection().unselectedQualifier(qualifier);
			return;
		}

		if (this.areMoreSelectable()) {
			this.model.getSiEntryQualifierSelection().selectedQualifier(qualifier);
		}
	}

	isSelected(qualifier: SiEntryQualifier): boolean {
		return this.model.getSiEntryQualifierSelection().isQualifierSelected(qualifier);
	}

	areMoreSelectable(): boolean {
		return this.model.getSiEntryQualifierSelection().max === null
				|| this.model.getSiEntryQualifierSelection().selectionSize < this.model.getSiEntryQualifierSelection().max!;
	}

	drop(event: CdkDragDrop<string[]>): void {
		// this.embeCol.changeEmbePosition(event.previousIndex, event.currentIndex);
		// this.embeCol.writeEmbes();

		this.spm.moveByIndex(event.previousIndex, event.currentIndex);
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

	isTree(): boolean {
		return this.spm.isTree();
	}

	switchToSortMode(): void {
		this.sortModeEnabled = true;
	}

	switchToEntryControlMode(): void {
		this.sortModeEnabled = false;
	}

	isSortModeEnabled(): boolean {
		return this.sortModeEnabled;
	}

	isEntryControModeEnabled(): boolean {
		return !this.sortModeEnabled;
	}

	isSiEntrySortSelected(siValueBoundary: SiValueBoundary): boolean {
		return this.sortSelectedMap.has(siValueBoundary.identifier.toString());
	}

	isSiEntrySortDecendant(siValueBoundary: SiValueBoundary): boolean {
		if (this.isSiEntrySortSelected(siValueBoundary)) {
			return false;
		}

		const idStr = siValueBoundary.identifier.toString();
		for (const [, v] of this.sortSelectedMap) {
			if (-1 < v.decendantIdStrs.indexOf(idStr)) {
				return true;
			}
		}

		return false;
	}

	setSiEntrySortSelected(siValueBoundary: SiValueBoundary, value: boolean): void {
		if (!value) {
			this.sortSelectedMap.delete(siValueBoundary.identifier.toString());
			return;
		}

		this.sortSelectedMap.set(siValueBoundary.identifier.toString(), {
			identifier: siValueBoundary.identifier,
			decendantIdStrs: this.spm.determineDecendantSiEntries(siValueBoundary).map(e => e.identifier.toString())
		});
	}

	bestNameOf(siValueBoundary: SiValueBoundary): string|null {
		return siValueBoundary.selectedEntry.entryQualifier.idName
				?? this.model.getStructurePageManager().declaration?.getMaskById(siValueBoundary.selectedTypeId!).qualifier.name
				?? null;
	}

	hasSiEntrySortSelections(): boolean {
		return this.sortSelectedMap.size > 0;
	}

	moveBefore(siValueBoundary: SiValueBoundary): void {
		this.spm.moveBefore(Array.from(this.sortSelectedMap.values()).map(v => v.identifier), siValueBoundary.identifier);
		this.sortSelectedMap.clear();
	}

	moveAfter(siValueBoundary: SiValueBoundary): void {
		this.spm.moveAfter(Array.from(this.sortSelectedMap.values()).map(v => v.identifier), siValueBoundary.identifier);
		this.sortSelectedMap.clear();
	}

	moveToParent(siValueBoundary: SiValueBoundary): void {
		this.spm.moveToParent(Array.from(this.sortSelectedMap.values()).map(v => v.identifier), siValueBoundary.identifier);
		this.sortSelectedMap.clear();
	}
}

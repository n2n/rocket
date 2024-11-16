import { SiPage } from './si-page';
import { SiDeclaration } from '../../../meta/si-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiValueBoundary } from '../../../content/si-value-boundary';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { SiModEvent, SiModStateService } from '../../../mod/model/si-mod-state.service';
import { SiService } from 'src/app/si/manage/si.service';
import { SiControl } from '../../../control/si-control';
import { SiGetInstruction } from '../../../api/si-get-instruction';
import { SiGetRequest } from '../../../api/si-get-request';
import { SiGetResponse } from '../../../api/si-get-response';
import { SiGetResult } from '../../../api/si-get-result';
import { SiControlBoundry } from '../../../control/si-control-boundry';
import { SiFrame } from '../../../meta/si-frame';
import { SiEntryIdentifier } from '../../../content/si-entry-qualifier';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { Subscription } from 'rxjs';

export class SiPageCollection implements SiControlBoundry {
	public declaration: SiDeclaration|null = null;
	// public controls: SiControl[]|null = null;

	private pagesMap = new Map<number, SiPage>();
	private pSize: number|null = null;
	private pQuickSearchStr: string|null = null;

	private modSubscription: Subscription|null = null;

	constructor(readonly pageSize: number, readonly siFrame: SiFrame, private siService: SiService,
			private siModState: SiModStateService, quickSearchstr: string|null = null) {
		this.pQuickSearchStr = quickSearchstr;
	}

	getEntries(): SiValueBoundary[] {
		const entries = [];
		for (const page of this.pages) {
			if (page.entries) {
				entries.push(...page.entries);
			}
		}
		return entries;
	}

	get controls(): SiControl[] {
		return this.declaration?.getBasicMask().controls ?? [];
	}

	getBoundValueBoundaries(): SiValueBoundary[] {
		return [];
	}

	getBoundDeclaration(): SiDeclaration {
		this.ensureDeclared();
		return this.declaration!;
	}

	getBoundApiUrl(): string|null {
		return this.siFrame.apiUrl;
	}

	get pages(): SiPage[] {
		return Array.from(this.pagesMap.values()).sort((aPage, bPage) => {
			return aPage.no - bPage.no;
		});
	}

	get quickSearchStr(): string|null {
		return this.pQuickSearchStr;
	}

	updateFilter(quickSearchStr: string|null) {
		if (this.quickSearchStr === quickSearchStr) {
			return;
		}

		this.pQuickSearchStr = quickSearchStr;
		this.clear();
		this.pSize = null;
	}

	clear() {
		for (const page of this.pages) {
			page.dipose();
		}

		IllegalSiStateError.assertTrue(this.pagesMap.size === 0);
	}

	private validateSubscription(): void {
		if (this.pSize) {
			if (this.modSubscription) {
				return;
			}

			this.modSubscription = this.siModState.modEvent$.subscribe((modEvent: SiModEvent|null) => {
				if (modEvent!.containsAddedTypeId(this.declaration!.getBasicMask().qualifier.maskIdentifier.typeId)) {
					this.clear();
				}
			});

			return;
		}

		if (this.modSubscription) {
			this.modSubscription.unsubscribe();
			this.modSubscription = null;
		}
	}

	get declared(): boolean {
		return this.pSize !== null && !!this.declaration;
	}

	private ensureDeclared() {
		if (this.declared) {
			return;
		}

		throw new IllegalSiStateError('SiPageCollection net yet declared.');
	}

	set size(size: number) {
		this.pSize = size;
	}

	get size(): number {
		this.ensureDeclared();
		return this.pSize!;
	}

	get ghostSize(): number {
		this.ensureDeclared();
		let ghostSize = 0;
		for (const page of this.pages) {
			if (page.loaded) {
				ghostSize += page.ghostSize;
			}
		}
		return ghostSize;
	}

	get pagesNum(): number {
		return Math.ceil((this.pSize! + this.ghostSize) / this.pageSize);
	}

	get loadedPagesNum(): number {
		return this.pagesMap.size;
	}

	private recalcPagesOffset() {
		let lastPage: SiPage|null = null;
		for (const page of this.pages) {
			if (lastPage) {
				page.offset = lastPage.offset + lastPage.size;
			}

			lastPage = page;
		}
	}

	// set size(size: number) {
	// 	this._size = size;

	// 	const pagesNum = this.pagesNum;

	// 	if (this.currentPageNo > pagesNum) {
	// 		this.currentPageNo = pagesNum;
	// 	}

	// 	for (const pageNo of this.pagesMap.keys()) {
	// 		if (pageNo > pagesNum) {
	// 			this.pagesMap.get(pageNo).dipose();
	// 			this.pagesMap.delete(pageNo);
	// 		}
	// 	}
	// }

	createPage(no: number, entries: SiValueBoundary[]|null): SiPage {
		if (no < 1 || (this.declared && no > this.pagesNum)) {
			throw new IllegalSiStateError('Page num to high: ' + no);
		}

		let offset = (no - 1) * this.pageSize;
		if (!this.pagesMap.has(no - 1) || !this.pagesMap.get(no - 1)!.loaded) {
			this.clear();
		} else {
			const prevPage = this.pagesMap.get(no - 1);
			offset = prevPage!.offset + prevPage!.size;
		}

		// let num = this.pageSize;
		// if (this.pagesMap.has(no + 1)) {
		// 	num = this.pagesMap.get(no + 1)!.offset - offset;
		// }

		const entryMonitory = new SiEntryMonitor(this.siFrame.apiUrl, this.siService, this.siModState, true);
		const page = new SiPage(entryMonitory, no, offset, entries);
		this.pagesMap.set(no, page);

		page.disposed$.subscribe(() => {
			IllegalSiStateError.assertTrue(this.pagesMap.get(page.no) === page,
					'SiPage no already retaken: ' + page.no);
			this.pagesMap.delete(no);
			this.validateSubscription();
		});

		page.entryRemoved$.subscribe(() => {
			this.pSize!--;
			this.recalcPagesOffset();
		});

		this.validateSubscription();

		return page;
	}

	containsPageNo(no: number): boolean {
		return this.pagesMap.has(no);
	}

	getPageByNo(no: number): SiPage {
		if (this.containsPageNo(no)) {
			return this.pagesMap.get(no) as SiPage;
		}

		throw new IllegalSiStateError('Unknown page with no: ' + no);
	}

	loadPage(pageNo: number): SiPage {
		let siPage: SiPage;
		if (this.containsPageNo(pageNo)) {
			siPage = this.getPageByNo(pageNo);
			siPage.entries = null;
		} else {
			siPage = this.createPage(pageNo, null);
		}

		const instruction = SiGetInstruction.partialContent(
						this.declaration!.getBasicMask().qualifier.maskIdentifier.id, siPage.offset, this.pageSize,
						this.quickSearchStr)
				.setDeclaration(this.declaration!)
				.setGeneralControlsIncluded(!this.controls)
				.setGeneralControlsBoundry(this)
				.setEntryControlsIncluded(true);
		const getRequest = new SiGetRequest(instruction);

		this.siService.apiGet(this.siFrame.apiUrl, getRequest)
				.subscribe((getResponse: SiGetResponse) => {
					this.applyResult(getResponse.results[0], siPage);
				});

		return siPage;
	}

	private applyResult(result: SiGetResult, siPage: SiPage): void {
		if (result.declaration) {
			this.declaration = result.declaration;
		}

		// if (result.generalControls) {
		// 	this.controls = result.generalControls;
		// }

		this.pSize = result.partialContent!.count;

		if (siPage.disposed) {
			return;
		}

		siPage.entries = result.partialContent!.valueBoundaries;

		if (result.partialContent!.valueBoundaries.length === 0) {
			siPage.dipose();
		}

		this.validateSubscription();
	}

	private findEntryPositionByIndex(index: number): SiEntryPosition|null {
		const globalIndex = index;
		for (const page of this.pages) {
			if (!page.loaded) {
				continue;
			}

			const realSize = page.size + page.ghostSize;
			if (realSize <= index) {
				index -= realSize;
				continue;
			}

			const entry = page.entries![index];

			return {
				entry, page,
				pageRelativIndex: index,
				childEntryPositions: this.findChildEntryPositions(globalIndex, entry.treeLevel!)
			};
		}

		return null;
	}

	getEntryByIdentifier(identifier: SiEntryIdentifier): SiValueBoundary|null {
		for (const page of this.pages) {
			const entry = page.entries!.find(e => e.identifier.equals(identifier));
			if (entry) {
				return entry;
			}
		}

		return null;
	}

	moveByIndex(previousIndex: number, nextIndex: number): boolean {
		this.ensureSortable();

		const previousResult = this.findEntryPositionByIndex(previousIndex);

		if (!previousResult || !previousResult.entry.isClean()) {
			return false;
		}

		const nextResult = this.findEntryPositionByIndex(nextIndex);
		if (!nextResult) {
			return false;
		}

		const after = nextIndex > previousIndex;

		if (!this.apiSortByIndex([previousResult.entry], nextIndex, nextResult, after)) {
			return false;
		}

		this.moveByPositions([previousResult], nextResult, after, nextResult.entry.treeLevel!);

		this.recalcPagesOffset();
		return true;
	}

	moveAfter(entries: SiValueBoundary[], afterEntry: SiValueBoundary) {
		this.ensureSortable();

		this.apiSortAfter(entries, afterEntry);

		this.moveByEntries(entries, afterEntry, true, afterEntry.treeLevel!);

		this.recalcPagesOffset();
	}

	moveBefore(entries: SiValueBoundary[], beforeEntry: SiValueBoundary) {
		this.ensureSortable();

		this.apiSortBefore(entries, beforeEntry);

		this.moveByEntries(entries, beforeEntry, false, beforeEntry.treeLevel!);

		this.recalcPagesOffset();
	}

	moveToParent(entries: SiValueBoundary[], parentEntry: SiValueBoundary) {
		this.ensureSortable();

		this.apiSortParent(entries, parentEntry);

		this.moveByEntries(entries, parentEntry, true, parentEntry.treeLevel! + 1);

		this.recalcPagesOffset();
	}

	private apiSortByIndex(entries: SiValueBoundary[], nextIndex: number, nextResult_: SiEntryPosition, after: boolean): boolean {
		if (nextResult_.entry.isAlive()) {
			if (!nextResult_.entry.isClean()) {
				return false;
			}

			if (after) {
				this.apiSortAfter(entries, nextResult_.entry);
			} else {
				this.apiSortBefore(entries, nextResult_.entry);
			}
			return true;
		}

		let nextResult: SiEntryPosition|null = nextResult_;

		for (let i = nextIndex + 1; true; i++) {
			nextResult = this.findEntryPositionByIndex(i);

			if (!nextResult) {
				break;
			}

			if (!nextResult.entry.isAlive()) {
				continue;
			}

			if (!nextResult.entry.isClean()) {
				return false;
			}

			this.apiSortBefore(entries, nextResult.entry);
			return true;
		}

		for (let i = nextIndex - 1; true; i--) {
			nextResult = this.findEntryPositionByIndex(i);

			if (!nextResult) {
				break;
			}

			if (!nextResult.entry.isAlive()) {
				continue;
			}

			if (!nextResult.entry.isClean()) {
				return false;
			}

			this.apiSortAfter(entries, nextResult.entry);
			return true;
		}

		return false;
	}

	private apiSortAfter(entries: SiValueBoundary[], afterEntry: SiValueBoundary) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(afterEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					maskId: this.declaration!.getBasicMask().qualifier.maskIdentifier.id,
					entryIds: entries.map(entry => entry.identifier.id!),
					afterEntryId: afterEntry.identifier.id!
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
					this.updateOrder();
				});
	}

	private apiSortBefore(entries: SiValueBoundary[], beforeEntry: SiValueBoundary) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(beforeEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					maskId: this.declaration!.getBasicMask().qualifier.maskIdentifier.id,
					entryIds: entries.map(entry => entry.identifier.id!),
					beforeEntryId: beforeEntry.identifier.id || undefined
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
					this.updateOrder();
				});
	}

	private apiSortParent(entries: SiValueBoundary[], parentEntry: SiValueBoundary) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(parentEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					maskId: this.declaration!.getBasicMask().qualifier.maskIdentifier.id,
					entryIds: entries.map(entry => entry.identifier.id!),
					parentEntryId: parentEntry.identifier.id || undefined
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
					this.updateOrder();
				});
	}

	getSiEntryPosition(entry: SiValueBoundary): SiEntryPosition {
		return this.deterPositionOfEntry(entry);
	}

	private deterPositionOfEntry(entry: SiValueBoundary): SiEntryPosition {
		let globalIndex = 0;
		for (const page of this.pages) {
			if (!page.loaded) {
				continue;
			}

			const i = page.entries!.indexOf(entry);
			if (0 > i) {
				globalIndex += page.size + page.ghostSize;
				continue;
			}

			globalIndex += i;

			return {
				entry, page,
				pageRelativIndex: i,
				childEntryPositions: this.findChildEntryPositions(globalIndex, entry.treeLevel!)
			};
		}

		throw new IllegalStateError('Entry not found: ' + entry.identifier.toString());
	}

	private findChildEntryPositions(parentIndex: number, parentTreeLevel: number): SiEntryPosition[] {
		const childEntryPositions = new Array<SiEntryPosition>();
		for (let i = parentIndex + 1; ; i++) {
			const childEntryPosition = this.findEntryPositionByIndex(i);
			if (!childEntryPosition || childEntryPosition.entry.treeLevel! <= parentTreeLevel) {
				break;
			}

			if (childEntryPosition.entry.treeLevel === parentTreeLevel + 1) {
				childEntryPositions.push(childEntryPosition);
			}
		}
		return childEntryPositions;
	}


	private moveByEntries(entries: SiValueBoundary[], targetEntry: SiValueBoundary, after: boolean, treeLevel: number) {
		let targetPosition = this.deterPositionOfEntry(targetEntry);

		if (after) {
			targetPosition = this.lastDecendantPosition(targetPosition);
		}

		const positions = entries.map((entry) => this.deterPositionOfEntry(entry));

		this.moveByPositions(positions, targetPosition, after, treeLevel);
	}

	private lastDecendantPosition(position: SiEntryPosition): SiEntryPosition {
		if (position.childEntryPositions.length === 0) {
			return position;
		}

		return this.lastDecendantPosition(position.childEntryPositions[position.childEntryPositions.length - 1]);
	}

	private moveByPositions(positions: SiEntryPosition[], targetPosition: SiEntryPosition, after: boolean, treeLevel: number) {
		for (const position of positions) {
			position.page.removeEntry(position.entry);

			const targetIndex = targetPosition.page.entries!.indexOf(targetPosition.entry) + (after ? 1 : 0);

			targetPosition.page.insertEntry(targetIndex, position.entry);

			position.entry.treeLevel = treeLevel;

			this.moveByPositions(position.childEntryPositions, {
				entry: position.entry,
				page: targetPosition.page,
				pageRelativIndex: targetIndex,
				childEntryPositions: position.childEntryPositions
			}, true, treeLevel + 1);
		}
	}

	private ensureSortable() {
		if (this.sortable) {
			return;
		}

		throw new IllegalSiStateError('SiPageCollection is not sortable.');
	}

	get sortable(): boolean {
		return this.siFrame.sortable;
	}

	private updateOrder() {

	}

	isTree(): boolean {
		return this.siFrame.treeMode;
	}
}

// class MovingProcess {
//
// }

export interface SiEntryPosition {
	 entry: SiValueBoundary;
	 page: SiPage;
	 pageRelativIndex: number;
	 childEntryPositions: SiEntryPosition[];
}

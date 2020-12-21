import { SiPage } from './si-page';
import { SiDeclaration } from '../../../meta/si-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiEntry } from '../../../content/si-entry';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { SiService } from 'src/app/si/manage/si.service';
import { SiControl } from '../../../control/si-control';
import { SiGetInstruction } from '../../../api/si-get-instruction';
import { SiGetRequest } from '../../../api/si-get-request';
import { SiGetResponse } from '../../../api/si-get-response';
import { SiGetResult } from '../../../api/si-get-result';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { SiFrame } from '../../../meta/si-frame';

export class SiPageCollection implements SiControlBoundry {
	public declaration: SiDeclaration|null = null;
	public controls: SiControl[]|null = null;

	private pagesMap = new Map<number, SiPage>();
	private _size: number|null = null;
	private _quickSearchStr: string|null = null;

	// private modSubscription: Subscription|null = null;

	constructor(readonly pageSize: number, private siFrame: SiFrame, private siService: SiService,
			private siModState: SiModStateService, quickSearchstr: string|null = null) {
		this._quickSearchStr = quickSearchstr;
	}

	getEntries(): SiEntry[] {
		const entries = [];
		for (const page of this.pages) {
			if (page.entries) {
				entries.push(...page.entries);
			}
		}
		return entries;
	}


	getControlledEntries(): SiEntry[] {
		return [];
	}

	// getSelectedEntries(): SiEntry[] {
	// 	throw new Error('Method not implemented.');
	// }

	get pages(): SiPage[] {
		return Array.from(this.pagesMap.values()).sort((aPage, bPage) => {
			return aPage.no - bPage.no;
		});
	}

	get quickSearchStr(): string|null {
		return this._quickSearchStr;
	}

	updateFilter(quickSearchStr: string|null) {
		if (this.quickSearchStr === quickSearchStr) {
			return;
		}

		this._quickSearchStr = quickSearchStr;
		this.clear();
	}

	clear() {
		for (const page of this.pages) {
			page.dipose();
		}

		IllegalSiStateError.assertTrue(this.pagesMap.size === 0);
	}

	// private validateSubscription() {
	// 	if (this.size > 0) {
	// 		if (!this.modSubscription) {
	// 			return;
	// 		}

	// 		this.modSubscription.unsubscribe();
	// 		this.modSubscription = null;
	// 		return;
	// 	}

	// 	this.siModState.modEvent$.subscribe((modEvent: SiModEvent) => {
	// 		if (modEvent.containsAddedTypeId(this.siFrame.typeContext.typeId)) {
	// 			this.clear();
	// 		}
	// 	});
	// }

	get declared(): boolean {
		return this._size !== null && !!this.declaration && !!this.controls;
	}

	private ensureDeclared() {
		if (this.declared) {
			return;
		}

		throw new IllegalSiStateError('SiPageCollection net yet declared.');
	}

	set size(size: number) {
		this._size = size;
	}

	get size(): number {
		this.ensureDeclared();
		return this._size;
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
		return Math.ceil((this._size + this.ghostSize) / this.pageSize);
	}

	get loadedPagesNum(): number {
		return this.pagesMap.size;
	}

	private recalcPagesOffset() {
		let lastPage: SiPage = null;
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

	createPage(no: number, entries: SiEntry[]|null): SiPage {
		if (no < 1 || (this.declared && no > this.pagesNum)) {
			throw new IllegalSiStateError('Page num to high.');
		}

		let offset = (no - 1) * this.pageSize;
		if (!this.pagesMap.has(no - 1) || !this.pagesMap.get(no - 1).loaded) {
			this.clear();
		} else {
			const prevPage = this.pagesMap.get(no - 1);
			offset = prevPage.offset + prevPage.size;
		}

		let num = this.pageSize;
		if (this.pagesMap.has(no + 1)) {
			num = this.pagesMap.get(no + 1).offset - offset;
		}

		const entryMonitory = new SiEntryMonitor(this.siFrame.apiUrl, this.siService, this.siModState, true);
		const page = new SiPage(entryMonitory, no, offset, entries);
		this.pagesMap.set(no, page);

		page.disposed$.subscribe(() => {
			IllegalSiStateError.assertTrue(this.pagesMap.get(page.no) === page,
					'SiPage no already retaken: ' + page.no);
			this.pagesMap.delete(no);
		});

		page.entryRemoved$.subscribe(() => {
			this._size--;
			this.recalcPagesOffset();
		});

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

		const instruction = SiGetInstruction.partialContent(false, true,
						siPage.offset, this.pageSize,
						this.quickSearchStr)
				.setDeclaration(this.declaration)
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

	private applyResult(result: SiGetResult, siPage: SiPage) {
		if (result.declaration) {
			this.declaration = result.declaration;
		}

		if (result.generalControls) {
			this.controls = result.generalControls;
		}

		this._size = result.partialContent.count;

		if (siPage.disposed) {
			return;
		}

		siPage.entries = result.partialContent.entries;

		if (result.partialContent.entries.length === 0) {
			siPage.dipose();
		}
	}

	private findEntryPositionByIndex(index: number): SiEntryPosition {
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

			const entry = page.entries[index];

			return {
				entry, page,
				pageRelativIndex: index,
				childEntryPositions: this.findChildEntryPositions(globalIndex, entry.treeLevel)
			};
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

		this.moveByPositions([previousResult], nextResult, after, nextResult.entry.treeLevel);

		this.recalcPagesOffset();
		this.updateOrder();
	}

	moveAfter(entries: SiEntry[], afterEntry: SiEntry) {
		this.ensureSortable();

		this.apiSortAfter(entries, afterEntry);

		this.moveByEntries(entries, afterEntry, true, afterEntry.treeLevel);

		this.recalcPagesOffset();
		this.updateOrder();
	}

	moveBefore(entries: SiEntry[], beforeEntry: SiEntry) {
		this.ensureSortable();

		this.apiSortBefore(entries, beforeEntry);

		this.moveByEntries(entries, beforeEntry, false, beforeEntry.treeLevel);

		this.recalcPagesOffset();
		this.updateOrder();
	}

	moveToParent(entries: SiEntry[], parentEntry: SiEntry) {
		this.ensureSortable();

		this.apiSortParent(entries, parentEntry);

		this.moveByEntries(entries, parentEntry, true, parentEntry.treeLevel + 1);

		this.recalcPagesOffset();
		this.updateOrder();
	}

	private apiSortByIndex(entries: SiEntry[], nextIndex: number, nextResult: SiEntryPosition, after: boolean): boolean {
		if (nextResult.entry.isAlive()) {
			if (!nextResult.entry.isClean()) {
				return false;
			}

			if (after) {
				this.apiSortAfter(entries, nextResult.entry);
			} else {
				this.apiSortBefore(entries, nextResult.entry);
			}
			return true;
		}

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
			return false;
		}

		return null;
	}

	private apiSortAfter(entries: SiEntry[], afterEntry: SiEntry) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(afterEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					ids: entries.map(entry => entry.identifier.id),
					afterId: afterEntry.identifier.id
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
				});
	}

	private apiSortBefore(entries: SiEntry[], beforeEntry: SiEntry) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(beforeEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					ids: entries.map(entry => entry.identifier.id),
					beforeId: beforeEntry.identifier.id
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
				});
	}

	private apiSortParent(entries: SiEntry[], parentEntry: SiEntry) {
		const locks = entries.map(entry => entry.createLock());
		locks.push(parentEntry.createLock());

		this.siService.apiSort(this.siFrame.apiUrl,
				{
					ids: entries.map(entry => entry.identifier.id),
					parentId: parentEntry.identifier.id
				}).subscribe(() => {
					locks.forEach((lock) => { lock.release(); });
				});
	}

	private findEntryPositionByEntry(entry: SiEntry): SiEntryPosition {
		let globalIndex = 0;
		for (const page of this.pages) {
			if (!page.loaded) {
				continue;
			}

			const i = page.entries.indexOf(entry);
			if (0 > i) {
				globalIndex += page.size + page.ghostSize;
				continue;
			}

			return {
				entry, page,
				pageRelativIndex: i,
				childEntryPositions: this.findChildEntryPositions(globalIndex, entry.treeLevel)
			};
		}

		return null;
	}

	private findChildEntryPositions(parentIndex: number, parentTreeLevel: number): SiEntryPosition[] {
		const childEntryPositions = new Array<SiEntryPosition>();
		for (let i = parentIndex + 1; ; i++) {
			const childEntryPosition = this.findEntryPositionByIndex(i);
			if (!childEntryPosition || childEntryPosition.entry.treeLevel <= parentTreeLevel) {
				break;
			}

			if (childEntryPosition.entry.treeLevel === parentTreeLevel + 1) {
				childEntryPositions.push(childEntryPosition);
			}
		}
		return childEntryPositions;
	}


	private moveByEntries(entries: SiEntry[], targetEntry: SiEntry, after: boolean, treeLevel: number) {
		const afterPosition = this.findEntryPositionByEntry(targetEntry);

		if (!afterPosition) {
			return;
		}

		const positions = entries.map((entry) => this.findEntryPositionByEntry(entry))
				.filter((position) => {
					return !!position && position.entry.isClean();
				});

		this.moveByPositions(positions, afterPosition, after, treeLevel);
	}

	private moveByPositions(positions: SiEntryPosition[], targetPosition: SiEntryPosition, after: boolean, treeLevel: number) {
		for (const position of positions) {
			position.page.removeEntry(position.entry);

			const targetIndex = targetPosition.page.entries.indexOf(targetPosition.entry) + (after ? 1 : 0);
			console.log(targetPosition.page.entries.indexOf(targetPosition.entry));
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
}

class MovingProcess {

}

interface SiEntryPosition {
	 entry: SiEntry;
	 page: SiPage;
	 pageRelativIndex: number;
	 childEntryPositions: SiEntryPosition[];
}

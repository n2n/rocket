import { SiPage } from './si-page';
import { SiDeclaration } from '../../../meta/si-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { BehaviorSubject, Observable } from 'rxjs';
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

export class SiPageCollection implements SiControlBoundry {
	public declaration: SiDeclaration|null = null;
	public controls: SiControl[]|null = null;

	private pagesMap = new Map<number, SiPage>();
	private _size = 0;
	private _currentPageNo$ = new BehaviorSubject<number>(1);
	public quickSearchStr: string|null = null;

	constructor(readonly pageSize: number, private apiUrl: string, private siService: SiService,
			private siModState: SiModStateService) {
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

	getSelectedEntries(): SiEntry[] {
		throw new Error('Method not implemented.');
	}

	get pages(): SiPage[] {
		return Array.from(this.pagesMap.values());
	}

	clear() {
		this.size = 0;
	}

	get currentPageExists(): boolean {
		return this.containsPageNo(this.currentPageNo);
	}

	get currentPage(): SiPage {
		return this.getPageByNo(this.currentPageNo);
	}

	get currentPageNo$(): Observable<number> {
		return this._currentPageNo$;
	}

	get currentPageNo(): number {
		return this._currentPageNo$.getValue();
	}

	set currentPageNo(currentPageNo: number) {
		if (currentPageNo === this.currentPageNo) {
			return;
		}

		if (currentPageNo > this.pagesNum || currentPageNo < 1) {
			throw new IllegalSiStateError('CurrentPageNo too large or too small: ' + currentPageNo);
		}

		// if (!this.getPageByNo(currentPageNo).visible) {
		// 	throw new IllegalSiStateError('Page not visible: ' + currentPageNo);
		// }

		this._currentPageNo$.next(currentPageNo);
	}

	get size(): number {
		return this._size;
	}

	set size(size: number) {
		this._size = size;

		const pagesNum = this.pagesNum;

		if (this.currentPageNo > pagesNum) {
			this.currentPageNo = pagesNum;
		}

		for (const pageNo of this.pagesMap.keys()) {
			if (pageNo > pagesNum) {
				this.pagesMap.get(pageNo).dipose();
				this.pagesMap.delete(pageNo);
			}
		}
	}

	createPage(no: number, entries: SiEntry[]|null): SiPage {
		if (no > this.pagesNum) {
			throw new IllegalSiStateError('Page num to high.');
		}

		const entryMonitory = new SiEntryMonitor(this.apiUrl, this.siService, this.siModState);
		const page = new SiPage(entryMonitory, no, entries, null);
		this.pagesMap.set(no, page);
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

	get pagesNum(): number {
		return Math.ceil(this.size as number / this.pageSize) || 1;
	}

	getVisiblePages(): SiPage[] {
		return Array.from(this.pagesMap.values()).filter((page: SiPage) => {
			return page.offsetHeight !== null;
		});
	}

	getLastVisiblePage(): SiPage|null {
		let lastPage: SiPage|null = null;
		for (const page of this.pagesMap.values()) {
			if (page.offsetHeight !== null && (lastPage === null || page.no > lastPage.no)) {
				lastPage = page;
			}
		}
		return lastPage;
	}

	getBestPageByOffsetHeight(offsetHeight: number): SiPage|null {
		let prevPage: SiPage|null = null;

		for (const page of this.getVisiblePages()) {
			if (prevPage === null || (page.offsetHeight < offsetHeight
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

	loadPage(pageNo: number): SiPage {
		let siPage: SiPage;
		if (this.containsPageNo(pageNo)) {
			siPage = this.getPageByNo(pageNo);
			siPage.entries = null;
		} else {
			siPage = this.createPage(pageNo, null);
		}

		const instruction = SiGetInstruction.partialContent(false, true,
						(pageNo - 1) * this.pageSize, this.pageSize,
						this.quickSearchStr)
				.setDeclaration(this.declaration)
				.setGeneralControlsIncluded(!this.controls)
				.setGeneralControlsBoundry(this)
				.setEntryControlsIncluded(true);
		const getRequest = new SiGetRequest(instruction);

		this.siService.apiGet(this.apiUrl, getRequest)
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

		this.size = result.partialContent.count;
		siPage.entries = result.partialContent.entries;
	}
}

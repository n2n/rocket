import { SiPage } from './si-page';
import { SiDeclaration } from '../../../meta/si-declaration';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { BehaviorSubject, Observable } from 'rxjs';

export class SiPageCollection {
	public declaration: SiDeclaration|null = null;

	private pagesMap = new Map<number, SiPage>();
	private _size = 0;
	private _currentPageNo$ = new BehaviorSubject<number>(1);
	public quickSearchStr: string|null = null;

	constructor(readonly pageSize: number) {
	}

	get pages(): SiPage[] {
		return Array.from(this.pagesMap.values());
	}

	clear() {
		this.pagesMap.clear();
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
				this.pagesMap.delete(pageNo);
			}
		}
	}

	putPage(page: SiPage) {
		if (page.num > this.pagesNum) {
			throw new IllegalSiStateError('Page num to high.');
		}

		this.pagesMap.set(page.num, page);
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

	getVisiblePages(): SiPage[] {
		return Array.from(this.pagesMap.values()).filter((page: SiPage) => {
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
}

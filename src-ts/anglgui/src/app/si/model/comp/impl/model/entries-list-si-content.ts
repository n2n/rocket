
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Message } from 'src/app/util/i18n/message';
import { SimpleUiStructureModel } from 'src/app/ui/structure/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/impl/type-si-content';
import { SiComp } from '../../si-comp';
import { SiPage } from './si-page';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry } from '../../../content/si-entry';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { ListZoneContentComponent } from '../comp/list-zone-content/list-zone-content.component';

export class EntriesListSiComp implements SiComp {

	private _size = 0;
	private _currentPageNo = 1;
	public declaration: SiDeclaration|null = null;
	public qualifierSelection: SiEntryQualifierSelection|null = null;

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

	private ensureSetup() {
		if (this.setup) { return; }

		throw new IllegalSiStateError('ListUiZone not set up.');
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

	createUiStructureModel(): UiStructureModel {
		const uiStrucuterModel = new SimpleUiStructureModel(new TypeUiContent(ListZoneContentComponent, (ref, uiStructure) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		}));
		uiStrucuterModel.messagesCallback = () => this.getMessages();
		return uiStrucuterModel;
	}

	private getMessages(): Message[] {
		const messages: Message[] = [];

		for (const entry of this.getEntries()) {
			messages.push(...entry.getMessages());
		}

		return messages;
	}
}

interface SiEntryQualifierSelection {
	min: number;
	max: number|null;
	selectedQualfiers: SiEntryQualifier[];
	done: () => any;
	cancel: () => any;
}


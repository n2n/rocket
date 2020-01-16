import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { of, Observable } from 'rxjs';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiComp } from 'src/app/si/model/comp/si-comp';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { map } from 'rxjs/operators';
import { SiField } from '../../../si-field';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntryBuildup } from '../../../si-entry-buildup';



export abstract class SplitContextSiField extends SiFieldAdapter {
	public style: SplitStyle = { iconClass: null, tooltip: null };
	protected splitContentMap = new Map<string, SplitContent>();

	constructor(public propIds: string[]) {
		super();
	}

	putSplitContent(splitContent: SplitContent) {
		this.splitContentMap.set(splitContent.key, splitContent);
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.splitContentMap.values());
	}

	getEntry$(key: string): Observable<SiEntry> {
		if (this.splitContentMap.has(key)) {
			return this.splitContentMap.get(key).getSiEntry$();
		}

		throw new Error('Unknown key.');
	}

	abstract hasInput(): boolean;

	abstract readInput(): object;

	abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	protected abstract createUiContent(): UiContent;
}

export class SplitContent implements SplitOption {
	private entry$: Observable<SiEntry>|null = null;
	private lazyDef: LazyDef|null = null;
	private loadedEntry: SiEntry|null = null;

	constructor(readonly key: string, public label: string, public shortLabel: string) {
	}

	static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = of(null);
		return splitContent;
	}

	static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.lazyDef = lazyDef;
		return splitContent;
	}

	static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = of(entry);
		splitContent.loadedEntry = entry;
		return splitContent;
	}

	getLoadedSiEntry(): SiEntry|null {
		return this.loadedEntry;
	}

	getSiEntry$(): Observable<SiEntry|null> {
		if (this.entry$) {
			return this.entry$;
		}

		return this.entry$ = this.lazyDef.siService.apiGet(this.lazyDef.apiUrl, new SiGetRequest(SiGetInstruction.entry(
						this.lazyDef.siComp, this.lazyDef.bulky, this.lazyDef.readOnly, this.lazyDef.entryId)))
				.pipe(map((response: SiGetResponse) => {
					return this.loadedEntry = response.results[0].entry;
				}));
	}
}

export interface LazyDef {
	apiUrl: string;
	entryId: string|null;
	bulky: boolean;
	readOnly: boolean;
	siService: SiService;
	siComp: SiComp;
}

export interface SplitStyle {
	iconClass: string|null;
	tooltip: string|null;
}

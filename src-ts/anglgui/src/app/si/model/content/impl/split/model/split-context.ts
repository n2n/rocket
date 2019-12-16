import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { of, Observable } from 'rxjs';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiComp } from 'src/app/si/model/comp/si-comp';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { map } from 'rxjs/operators';

export interface SplitContext {
	getEntry$(key: string): Observable<SiEntry>;

	getSplitOptions(): SplitOption[];
}

export class SplitContextAdapter implements SplitContext {
	private splitContentMap: Map<string, SplitContent>;

	putSplitContent(splitContent: SplitContent) {
		this.splitContentMap.set(splitContent.key, splitContent);
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.splitContentMap.values());
	}

	getEntry$(key: string): Observable<SiEntry> {
		if (this.splitContentMap.has(key)) {
			return this.splitContentMap.get(key).getEntry();
		}

		throw new Error('Unknown key.');
	}
}

export class SplitContent implements SplitOption {
	private entry$: Observable<SiEntry>|null = null;
	private lazyDef: LazyDef|null = null;

	constructor(readonly key: string, public label: string, public shortLabel: string) {
	}

	static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
		return new SplitContent(key, label, shortLabel);
	}

	static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.lazyDef = lazyDef;
		return splitContent;
	}

	static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = of(entry);
		return splitContent;
	}

	getEntry(): Observable<SiEntry> {
		if (this.entry$) {
			return this.entry$;
		}

		if (!this.lazyDef) {
			throw new Error('SplitContent unavailable.');
		}

		this.entry$ = this.lazyDef.siService.apiGet(this.lazyDef.apiUrl, new SiGetRequest(SiGetInstruction.entry(
						this.lazyDef.siComp, this.lazyDef.bulky, this.lazyDef.readOnly, this.lazyDef.entryId)))
				.pipe(map((response: SiGetResponse) => {
					return response.results[0].entry;
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

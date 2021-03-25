import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { map } from 'rxjs/operators';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundry } from 'src/app/si/model/control/si-control-bountry';
import { SimpleSiFieldAdapter } from '../../common/model/simple-si-field-adapter';
import { Observable } from 'rxjs';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiStyle } from 'src/app/si/model/meta/si-view-mode';

export abstract class SplitContextSiField extends SimpleSiFieldAdapter {
	public style: SplitStyle = { iconClass: null, tooltip: null };
	protected splitContentMap = new Map<string, SplitContent>();

	constructor() {
		super();
	}

	putSplitContent(splitContent: SplitContent) {
		this.splitContentMap.set(splitContent.key, splitContent);
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.splitContentMap.values());
	}

	getEntry$(key: string): Promise<SiEntry> {
		if (this.splitContentMap.has(key)) {
			return this.splitContentMap.get(key).getSiEntry$();
		}

		throw new Error('Unknown key.');
	}

	abstract isKeyActive(key: string): boolean;

	abstract activateKey(key: string): void;

	abstract deactivateKey(key: string): void;

	abstract get activeKeys$(): Observable<string[]>;

	abstract hasInput(): boolean;

	abstract readInput(): object;

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	protected abstract createUiContent(): UiContent;

	copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(SplitContextCopy.fromMap(this.splitContentMap));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		genericValue.readInstance(SplitContextCopy).applyToMap(this.splitContentMap);
		return Promise.resolve();
	}

	createResetPoint(): SiGenericValue {
		return new SiGenericValue(SplitContextResetPoint.create(this.splitContentMap, this));
	}

	resetToPoint(genericValue: SiGenericValue): void {
		genericValue.readInstance(SplitContextResetPoint).apply(this.splitContentMap, this);
	}
}

class SplitContextCopy {
	private genericMap = new Map<string, SiGenericEntry>();

	static fromMap(map: Map<string, SplitContent>): SplitContextCopy {
		const gsc = new SplitContextCopy();

		for (const [key, value] of map) {
			const entry = value.getLoadedSiEntry();
			if (entry) {
				gsc.genericMap.set(key, entry.copy());
			}
		}

		return gsc;
	}

	applyToMap(splitContentMap: Map<string, SplitContent>): void {
		for (const [key, genericEntry] of this.genericMap) {
			const siEntry = splitContentMap.get(key)?.getLoadedSiEntry();
			if (siEntry) {
				siEntry.paste(genericEntry);
			}
		}
	}
}

class SplitContextResetPoint {
	private activeKeys = new Array<string>();
	private genericEntryMap = new Map<string, SiGenericEntry>();

	static create(map: Map<string, SplitContent>, splitContext: SplitContextSiField): SplitContextResetPoint {
		const scrp = new SplitContextResetPoint();

		for (const [key, splitContent] of map) {
			if (splitContext.isKeyActive(key)) {
				continue;
			}

			scrp.activeKeys.push(key);

			const entry = splitContent.getLoadedSiEntry();
			if (entry) {
				scrp.genericEntryMap.set(key, entry.createInputResetPoint());
			}
		}

		return scrp;
	}

	private containsActiveKey(key: string): boolean {
		return -1 !== this.activeKeys.indexOf(key);
	}

	apply(splitContentMap: Map<string, SplitContent>, splitContext: SplitContextSiField): void {
		for (const [key, splitContent] of splitContentMap) {
			if (this.containsActiveKey(key)) {
				splitContext.activateKey(key);
			} else {
				splitContext.deactivateKey(key);
			}

			if (this.genericEntryMap.has(key)) {
				splitContent.getLoadedSiEntry().resetToPoint(this.genericEntryMap.get(key));
			} else {
				splitContent.resetLazyLoad();
			}
		}
	}
}

export class SplitContent implements SplitOption {
	private entry$: Promise<SiEntry>|null = null;
	private lazyDef: LazyDef|null = null;
	private loadedEntry: SiEntry|null = null;

	constructor(readonly key: string, public label: string, public shortLabel: string) {
	}

	static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = Promise.resolve(null);
		return splitContent;
	}

	static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.lazyDef = lazyDef;
		return splitContent;
	}

	static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry$ = Promise.resolve(entry);
		splitContent.loadedEntry = entry;
		return splitContent;
	}

	resetLazyLoad(): void {

		// todo
		// if (this.lazyDef) {
		// 	this.entry$ = null;
		// 	this.loadedEntry = null;
		// 	return;
		// }

		// throw new IllegalSiStateError('SplitContent was not lazy loaded!');
	}

	getLoadedSiEntry(): SiEntry|null {
		return this.loadedEntry;
	}

	getSiEntry$(): Promise<SiEntry|null> {
		if (this.entry$) {
			return this.entry$;
		}

		let instruction: SiGetInstruction|null = null;
		if (this.lazyDef.entryId) {
			instruction = SiGetInstruction.entry(this.lazyDef.style, this.lazyDef.entryId);
		} else {
			instruction = SiGetInstruction.newEntry(this.lazyDef.style);
		}
		instruction.setPropIds(this.lazyDef.propIds);

		return this.entry$ = this.lazyDef.siService.apiGet(this.lazyDef.apiGetUrl, new SiGetRequest(instruction))
				.pipe(map((response: SiGetResponse) => {
					return this.loadedEntry = response.results[0].entry;
				}))
				.toPromise();
	}
}

export interface LazyDef {
	apiGetUrl: string;
	entryId: string|null;
	propIds: string[]|null;
	style: SiStyle;
	siService: SiService;
	siControlBoundy: SiControlBoundry;
}

export interface SplitStyle {
	iconClass: string|null;
	tooltip: string|null;
}

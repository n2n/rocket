import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';

export interface SplitContext {
	getEntry(key: string): SiEntry;

	getSplitOptions(): SplitOption[];
}

export class SplitContextAdapter {
	private splitContentMap: Map<string, SplitContent>;

	putSplitContent(splitContent: SplitContent) {
		this.splitContentMap.set(splitContent.key, splitContent);
	}

	getSplitOptions(): SplitOption[] {
		return Array.from(this.splitContentMap.values());
	}
}

export class SplitContent implements SplitOption {
	private entry: SiEntry|null = null;
	private lazy: LazyDef|null = null;

	constructor(readonly key: string, public label: string, public shortLabel: string) {
	}

	static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
		return new SplitContent(key, label, shortLabel);
	}

	static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.lazy = lazyDef;
		return splitContent;
	}

	static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
		const splitContent = new SplitContent(key, label, shortLabel);
		splitContent.entry = entry;
		return splitContent;
	}
}

export interface LazyDef {
	apiUrl: string;
	entryId: string|null;
	bulky: boolean;
}

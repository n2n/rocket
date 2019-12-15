import { SplitOption } from '../model/split-option';

export interface SplitViewMenuModel {

	getSplitOptions(): SplitOption[];

	getIconClass(): string|null;

	getTooltip(): string|null;

	getVisibleKeys(): string[];

	setVisibleKeys(visibleKeys: string[]): void;
}

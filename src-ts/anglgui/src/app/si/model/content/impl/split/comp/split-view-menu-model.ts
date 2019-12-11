export interface SplitViewMenuModel {

	getOptionMap(): Map<string, string>;

	getIconClass(): string;

	getTooltip(): string;

	getVisibleKeys(): string[];

	setVisibleKeys(visibleKeys: string[]): void;
}


export interface EnumInModel {

	getValue(): string;

	setValue(value: string): void;

	getOptions(): Map<string, string>;
}

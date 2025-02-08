import { SiEntryQualifier } from '../../../content/si-entry-qualifier';
import { SiObjectQualifier } from '../../../content/si-object-qualifier';
import { SiEntry } from '../../../content/si-entry';

export interface SiEntryQualifierSelection {
	readonly min: number;
	readonly max: number|null;
	readonly selectionSize: number;
	setSelectedQualifiers(selectedQualifiers: SiEntryQualifier[]): void;
	isQualifierSelected(qualifier: SiEntryQualifier): boolean;
	unselectedQualifier(qualifier: SiEntryQualifier): void;
	selectedQualifier(qualifier: SiEntryQualifier): void;
}

export class SimpleSiEntryQualifierSelection implements SiEntryQualifierSelection {
	constructor(public min: number, public max: number|null, public selectedQualifiers: SiEntryQualifier[]) {

	}

	get selectionSize(): number {
		return this.selectedQualifiers.length;
	}

	private findIndexOf(qualifier: SiEntryQualifier): number {
		return this.selectedQualifiers.findIndex((q) => q.equals(qualifier))
	}

	isQualifierSelected(qualifier: SiEntryQualifier): boolean {
		return -1 !== this.findIndexOf(qualifier);
	}

	unselectedQualifier(qualifier: SiEntryQualifier): void {
		const i = this.findIndexOf(qualifier)
		if (i > -1) {
			this.selectedQualifiers.splice(i, 1);
		}
	}

	selectedQualifier(qualifier: SiEntryQualifier): void {
		if (-1 === this.findIndexOf(qualifier)) {
			this.selectedQualifiers.push(qualifier);
		}
	}

	setSelectedQualifiers(selectedQualifiers: SiEntryQualifier[]): void {
		this.selectedQualifiers = selectedQualifiers
	}
}

export class SiObjectQualifierSelection implements SiEntryQualifierSelection {

	constructor(public min: number, public max: number|null, public selectedQualifiers: SiObjectQualifier[]) {

	}

	get selectionSize(): number {
		return this.selectedQualifiers.length;
	}

	private findIndexOf(qualifier: SiEntryQualifier): number {
		return this.selectedQualifiers.findIndex((q) => q.matchesObjectIdentifier(qualifier.identifier));
	}

	isQualifierSelected(qualifier: SiEntryQualifier): boolean {
		return -1 !== this.findIndexOf(qualifier);
	}

	setSelectedQualifiers(selectedQualifiers: SiEntryQualifier[]): void {
		this.selectedQualifiers = selectedQualifiers.map(q => q.toObjectQualifier());
	}

	unselectedQualifier(qualifier: SiEntryQualifier): void {
		const i = this.findIndexOf(qualifier)
		if (i > -1) {
			this.selectedQualifiers.splice(i, 1);
		}
	}

	selectedQualifier(qualifier: SiEntryQualifier): void {
		if (-1 === this.findIndexOf(qualifier)) {
			this.selectedQualifiers.push(qualifier.toObjectQualifier());
		}
	}

}

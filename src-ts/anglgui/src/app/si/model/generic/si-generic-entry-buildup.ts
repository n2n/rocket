import { SiEntryQualifier } from '../content/si-entry-qualifier';
import { SiGenericValue } from './si-generic-value';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiGenericEntry {

	constructor(public entryQualifier: SiEntryQualifier,
			public fieldValuesMap = new Map<string, SiGenericValue>()) {
	}

	get maskId(): string {
		return this.entryQualifier.identifier.maskIdentifier.id;
	}

	containsPropId(id: string): boolean {
		return this.fieldValuesMap.has(id);
	}

	getFieldValueById(id: string): SiGenericValue {
		if (this.containsPropId(id)) {
			return this.fieldValuesMap.get(id)!;
		}

		throw new IllegalSiStateError('Unkown field id ' + id);
	}

	getFieldValues() {
		return Array.from(this.fieldValuesMap.values());
	}
}

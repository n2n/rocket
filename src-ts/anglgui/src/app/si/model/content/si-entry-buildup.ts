
import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiField } from './si-field';
import { BehaviorSubject } from 'rxjs';
import { SiEntryQualifier } from './si-qualifier';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';
import { SiGenericEntry } from '../generic/si-generic-entry';
import { SiGenericEntryBuildup } from '../generic/si-generic-entry-buildup';
import { GenericMissmatchError } from '../generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';
import { SiGenericValue } from '../generic/si-generic-value';

export class SiEntryBuildup {
	public messages: Message[] = [];
	private fieldMap$: BehaviorSubject<Map<string, SiField>>;

	constructor(readonly entryQualifier: SiEntryQualifier,
			fieldMap = new Map<string, SiField>(), public controls = new Array<SiControl>()) {
		this.fieldMap$ = new BehaviorSubject(fieldMap);
	}

	getTypeId(): string {
		return this.entryQualifier.typeQualifier.id;
	}

	set fieldMap(fieldMap: Map<string, SiField>) {
		this.fieldMap$.next(fieldMap);
	}

	containsPropId(id: string) {
		return this.fieldMap$.getValue().has(id);
	}

	getFieldById(id: string): SiField {
		if (this.containsPropId(id)) {
			return this.fieldMap$.getValue().get(id);
		}

		throw new IllegalSiStateError('Unkown SiField id ' + id);
	}

	getFields() {
		return Array.from(this.fieldMap$.getValue().values());
	}

	getFieldMap(): Map<string, SiField> {
		return new Map(this.fieldMap$.getValue());
	}

	// copy(): SiEntryBuildup {
	// 	const copy = new SiEntryBuildup(this.entryQualifier);

	// 	const fieldMapCopy = new Map<string, SiField>();
	// 	for (const [key, value] of this.fieldMap$.getValue()) {
	// 		fieldMapCopy.set(key, value.copy(copy));
	// 	}
	// 	copy.fieldMap = fieldMapCopy;

	// 	const controlsCopy = new Array<SiControl>();
	// 	for (const value of this.controls) {
	// 		controlsCopy.push(value);
	// 	}

	// 	copy.controls = controlsCopy;
	// 	copy.messages = this.messages;

	// 	return copy;
	// }

	copy(): SiGenericEntryBuildup {
		const fieldValuesMap = new Map<string, SiGenericValue>();
		for (const [fieldId, field] of this.fieldMap$.getValue()) {
			fieldValuesMap.set(fieldId, field.copyValue());
		}

		return new SiGenericEntryBuildup(this.entryQualifier, fieldValuesMap);
	}

	paste(genericEntryBuildup: SiGenericEntryBuildup): Promise<void> {
		this.valGenericEntryBuildup(genericEntryBuildup);

		const promises = new Array<Promise<void>>();
		for (const [fieldId, genericValue] of genericEntryBuildup.fieldValuesMap) {
			if (this.containsPropId(fieldId)) {
				try {
					promises.push(this.getFieldById(fieldId).pasteValue(genericValue));
				} catch (e) {
					if ((e instanceof GenericMissmatchError)) {
						throw e;
					}
				}
			}
		}
		return Promise.all(promises).then(() => {});
	}

	createResetPoint(): SiGenericEntryBuildup {
		const fieldValuesMap = new Map<string, SiGenericValue>();
		for (const [fieldId, field] of this.fieldMap$.getValue()) {
			fieldValuesMap.set(fieldId, field.createResetPoint());
		}

		return new SiGenericEntryBuildup(this.entryQualifier, fieldValuesMap);
	}

	resetToPoint(genericEntryBuildup: SiGenericEntryBuildup): void {
		this.valGenericEntryBuildup(genericEntryBuildup);

		for (const [fieldId, genericValue] of genericEntryBuildup.fieldValuesMap) {
			if (this.containsPropId(fieldId)) {
				this.getFieldById(fieldId).resetToPoint(genericValue);
			}
		}
	}

	private valGenericEntryBuildup(genericEntryBuildup: SiGenericEntryBuildup) {
		if (!genericEntryBuildup.entryQualifier.equals(this.entryQualifier)) {
			throw new GenericMissmatchError('SiEntryBuildup missmatch: '
					+ genericEntryBuildup.entryQualifier.toString() + ' != ' + this.entryQualifier.toString());
		}
	}
}

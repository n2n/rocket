
import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiField } from './si-field';
import { BehaviorSubject } from 'rxjs';
import { SiEntryQualifier } from './si-entry-qualifier';
import { SiGenericEntry } from '../generic/si-generic-entry-buildup';
import { GenericMissmatchError } from '../generic/generic-missmatch-error';
import { SiGenericValue } from '../generic/si-generic-value';
import { UnknownSiElementError } from '../../util/unknown-si-element-error';
import { SiInputResetPoint } from './si-input-reset-point';
import { CallbackInputResetPoint } from './impl/common/model/callback-si-input-reset-point';

export class SiEntry {
	public messages: Message[] = [];
	private fieldMap$: BehaviorSubject<Map<string, SiField>>;

	constructor(readonly entryQualifier: SiEntryQualifier,
			fieldMap = new Map<string, SiField>(), public controls = new Array<SiControl>()) {
		this.fieldMap$ = new BehaviorSubject(fieldMap);
	}

	// get mask(): SiMask {
	// 	throw new Error();
	// }

	getMaskId(): string {
		return this.entryQualifier.identifier.maskIdentifier.id;
	}

	set fieldMap(fieldMap: Map<string, SiField>) {
		this.fieldMap$.next(fieldMap);
	}

	containsPropId(id: string) {
		return this.fieldMap$.getValue().has(id);
	}

	getFieldById(id: string): SiField {
		if (this.containsPropId(id)) {
			return this.fieldMap$.getValue().get(id)!;
		}

		throw new UnknownSiElementError('Unkown SiField id ' + id);
	}

	getFields() {
		return Array.from(this.fieldMap$.getValue().values());
	}

	getFieldMap(): Map<string, SiField> {
		return new Map(this.fieldMap$.getValue());
	}

	// copy(): SiEntry {
	// 	const copy = new SiEntry(this.entryQualifier);

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

	async copy(): Promise<SiGenericEntry> {
		const fieldValuesMap = new Map<string, SiGenericValue>();

		const promises: Promise<void>[] = [];
		for (const [fieldId, field] of this.fieldMap$.getValue()) {
			if (!field.copyValue) {
				continue;
			}

			promises.push(field.copyValue().then((genericValue) => {
				fieldValuesMap.set(fieldId, genericValue);
			}));
		}

		await Promise.all(promises);
		return new SiGenericEntry(this.entryQualifier, fieldValuesMap);
	}

	paste(genericEntry: SiGenericEntry): Promise<boolean> {
		this.valGenericEntry(genericEntry);

		const promises = new Array<Promise<boolean>>();
		for (const [fieldId, genericValue] of genericEntry.fieldValuesMap) {
			if (!this.containsPropId(fieldId)) {
				continue;
			}

			promises.push(this.getFieldById(fieldId).pasteValue!(genericValue));
		}

		return Promise.all(promises).then((results) => !!results.indexOf(true));
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		const rps = await Promise.all(Array
				.from(this.fieldMap$.getValue().values())
				.filter(field => field.hasInput())
				.map(field => field.createInputResetPoint()));

		return new CallbackInputResetPoint(rps, (resetPoints) => {
			resetPoints.forEach(rp => rp.rollbackTo());
		});
	}

	private valGenericEntry(genericEntry: SiGenericEntry): void {
		if (!genericEntry.entryQualifier.equals(this.entryQualifier)) {
			throw new GenericMissmatchError('SiEntry missmatch: '
					+ genericEntry.entryQualifier.toString() + ' != ' + this.entryQualifier.toString());
		}
	}

	// consume(entry: SiEntry) {
	// 	for (const [key, field] of this.fieldMap) {
	// 		this.fieldMap.set(key, field.consume(entry.getFieldById(key)));
	// 	}

	// 	this.controls = entry.controls;
	// }
}

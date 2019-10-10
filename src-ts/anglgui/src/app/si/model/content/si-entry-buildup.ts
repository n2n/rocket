
import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiField } from './si-field';
import { BehaviorSubject } from 'rxjs';
import { SiEntryQualifier } from './si-qualifier';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

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

	copy(): SiEntryBuildup {
		const copy = new SiEntryBuildup(this.entryQualifier);

		const fieldMapCopy = new Map<string, SiField>();
		for (const [key, value] of this.fieldMap) {
			fieldMapCopy.set(key, value.copy(copy));
		}
		copy.fieldMap = fieldMapCopy;

		const controlsCopy = new Array<SiControl>();
		for (const value of this.controls) {
			controlsCopy.push(value);
		}

		copy.controls = controlsCopy;
		copy.messages = this.messages;

		return copy;
	}
}

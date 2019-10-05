
import { SiField } from 'src/app/si/model/entity/si-field';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiEntryIdentifier, SiEntryQualifier } from 'src/app/si/model/entity/si-qualifier';
import { SiType } from 'src/app/si/model/entity/si-type';
import { Message } from 'src/app/util/i18n/message';

export class SiEntryBuildup {
	public messages: Message[] = [];

	constructor(public type: SiType, public idName: string|null,
			public fieldMap: Map<string, SiField> = new Map<string, SiField>(),
			public controlMap: Map<string, SiControl> = new Map<string, SiControl>()) {
	}

	createQualifier(identifier: SiEntryIdentifier): SiEntryQualifier {
		return new SiEntryQualifier(identifier.category, identifier.id, this.type, this.idName);
	}

	getBestName(): string {
		if (this.idName) {
			return this.idName;
		}

		return this.type.name;
	}

	getFieldById(id: string): SiField|null {
		return this.fieldMap.get(id) || null;
	}

	copy(): SiEntryBuildup {
		const fieldMapCopy = new Map<string, SiField>();
		for (const [key, value] of this.fieldMap) {
			fieldMapCopy.set(key, value.copy());
		}

		const controlMapCopy = new Map<string, SiControl>();
		for (const [key, value] of this.controlMap) {
			controlMapCopy.set(key, value);
		}

		const copy = new SiEntryBuildup(this.type, this.idName, fieldMapCopy, controlMapCopy);
		copy.messages = this.messages;
		return copy;
	}
}

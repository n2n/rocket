
import { SiField } from 'src/app/si/model/content/si-field';
import { SiControl } from 'src/app/si/model/control/si-control';
import { SiIdentifier, SiQualifier } from 'src/app/si/model/content/si-qualifier';
import { SiType } from "src/app/si/model/content/si-type";

export class SiTypeBuildup {
	public messages: string[] = [];

	constructor(public type: SiType, public idName: string|null,
			public fieldMap: Map<string, SiField> = new Map<string, SiField>(),
			public controlMap: Map<string, SiControl> = new Map<string, SiControl>()) {
	}

	createQualifier(identifier: SiIdentifier): SiQualifier {
		return new SiQualifier(identifier.category, identifier.id, this.type, this.idName);
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

	copy(): SiTypeBuildup {
		const fieldMapCopy = new Map<string, SiField>();
		for (const [key, value] of this.fieldMap) {
			fieldMapCopy.set(key, value.copy());
		}

		const controlMapCopy = new Map<string, SiControl>();
		for (const [key, value] of this.controlMap) {
			controlMapCopy.set(key, value);
		}

		const copy = new SiTypeBuildup(this.type, this.idName, fieldMapCopy, controlMapCopy);
		copy.messages = this.messages;
		return copy;

	}
}

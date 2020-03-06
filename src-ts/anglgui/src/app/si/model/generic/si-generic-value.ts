import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiGenericValue {

	constructor(public value: object|null) {
	}

	isNull(): boolean {
		return this.value === null;
	}

	isInstanceOf(type: new(...args: any[]) => any): boolean {
		return this.value instanceof type;
	}

	readInstance<T>(type: new(...args: any[]) => T): T {
		if (this.isInstanceOf(type)) {
			return this.value as unknown as T;
		}

		throw new IllegalSiStateError('Value is not instanceof ' + type.name);
	}

}

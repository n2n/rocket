export class ObjectMissmatchError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, ObjectMissmatchError.prototype);
	}
}

export class Extractor {
	private obj: object;

	constructor(obj: any) {
		if (typeof obj === 'object') {
			this.obj = obj;
			return;
		}

		throw new ObjectMissmatchError('Object required. Given: ' + typeof obj);
	}

	getKeys(): string[] {
		return Object.keys(this.obj);
	}

	contains(propName: string): boolean {
		return this.obj[propName as keyof object] !== undefined;
	}

	nullaString(propName: string): string|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqString(propName);
	}

	reqString(propName: string): string {
		if (typeof this.obj[propName as keyof object] === 'string') {
			return this.obj[propName as keyof object];
		}

		throw new ObjectMissmatchError('Property ' + propName + ' must be of type string. Given: '
				+ typeof this.obj[propName as keyof object]);
	}

	nullaBoolean(propName: string): boolean|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqBoolean(propName);
	}

	reqBoolean(propName: string): boolean {
		if (typeof this.obj[propName as keyof object] === 'boolean') {
			return this.obj[propName as keyof object];
		}

		throw new ObjectMissmatchError('Property ' + propName + ' must be of type boolean. Given: '
				+ typeof this.obj[propName as keyof object]);
	}

	nullaNumber(propName: string): number|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqNumber(propName);
	}

	reqNumber(propName: string): number {
		if (typeof this.obj[propName as keyof object] === 'number') {
			return this.obj[propName as keyof object];
		}

		throw new ObjectMissmatchError('Property ' + propName + ' must be of type number. Given: '
				+ typeof this.obj[propName as keyof object]);
	}

	nullaArray(propName: string): Array<any>|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqArray(propName);
	}

	reqArray(propName: string): Array<any> {
		if (Array.isArray(this.obj[propName as keyof object])) {
			return this.obj[propName as keyof object];
		}

		throw new ObjectMissmatchError('Property ' + propName + ' must be of type Array. Given: '
				+ typeof this.obj[propName as keyof object]);
	}

	nullaStringArray(propName: string): Array<string>|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqStringArray(propName);
	}

	reqStringArray(propName: string): Array<string> {
		const arr = this.reqArray(propName);
		for (const value of arr) {
			if (typeof value === 'string') {
				continue;
			}

			throw new ObjectMissmatchError('Property ' + propName + ' must be of type string[] but array contains non-string.');
		}
		return arr;
	}

	nullaObject(propName: string): object|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqObject(propName);
	}

	reqObject(propName: string): object {
		if (typeof this.obj[propName as keyof object] === 'object' && this.obj[propName as keyof object] !== null) {
			return this.obj[propName as keyof object];
		}

		throw new ObjectMissmatchError('Property ' + propName + ' must be of type object. Given: '
				+ typeof this.obj[propName as keyof object]);
	}

	nullaMap(propName: string): Map<string, any>|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqMap(propName);
	}

	reqMap(propName: string): Map<string, any> {
		const obj = this.reqObject(propName);

		const entries = Object.keys(obj).map(k => [k, obj[k as keyof object]]);
		return new Map(entries as any);
	}

	nullaArrayMap(propName: string): Map<string, Array<any>>|null {
		if (this.obj[propName as keyof object] === null) {
			return null;
		}

		return this.reqArrayMap(propName);
	}

	reqArrayMap(propName: string): Map<string, Array<any>> {
		const map = this.reqMap(propName);

		map.forEach((value, key) => {
			if (Array.isArray(value)) { return; }

			throw new ObjectMissmatchError('Property ' + propName + '[' + key + '] must be of type array. Given: '
					+ typeof value);
		});

		return map;
	}

	reqStringMap(propName: string, fieldNullable = false): Map<string, string> {
		const map = this.reqMap(propName);

		map.forEach((value, key) => {
			if (typeof value === 'string' || (fieldNullable && value === null)) {
				return;
			}

			throw new ObjectMissmatchError('Property ' + propName + '[' + key + '] must be of type string. Given: '
					+ typeof value);
		});

		return map;
	}

	reqExtractor(propName: string): Extractor {
		return new Extractor(this.reqObject(propName));
	}
}

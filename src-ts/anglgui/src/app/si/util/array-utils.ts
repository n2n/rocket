
export class ArrayUtils {
	static containsAny<T>(array: Array<T>, needlesArray: Array<T>): boolean {
		for (const v of array) {
			if (ArrayUtils.contains(needlesArray, v)) {
				return true;
			}
		}

		return false;
	}

	static contains<T>(array: Array<T>, needle: T) {
		return -1 !== array.indexOf(needle);
	}

	static uniqueAdd<T>(array: Array<T>, elem: T): void {
		if (-1 !== array.indexOf(elem)) {
			return;
		}

		array.push(elem);
	}

	static replace<T>(array: Array<T>, needle: T, replacement: T, strict = true): boolean {
		const i = array.indexOf(needle);
		if (i > -1) {
			array[i] = replacement;
			return true;
		}

		if (strict) {
			throw new Error(needle + ' not found in array.');
		}

		return false;
	}

	static remove<T>(array: Array<T>, needle: T, strict = true): boolean {
		const i = array.indexOf(needle);
		if (i > -1) {
			array.splice(i, 1);
			return true;
		}

		if (strict) {
			throw new Error(needle + ' not found in array.');
		}

		return false;
	}

	static removeAt<T>(array: Array<T>, index: number, strict = true): boolean {
		if (array.length > index) {
			array.splice(index, 1);
			return true;
		}

		if (strict) {
			throw new Error('Index ' + index + ' not found in array.');
		}

		return false;
	}

	static insertBefore<T>(array: Array<T>, before: T|undefined, insert: T, strict = true): boolean {
		if (before === undefined) {
			array.push(insert);
			return true;
		}

		const i = array.indexOf(before);
		if (i > -1) {
			array.splice(i, 0, insert);
			return true;
		}

		if (strict) {
			throw new Error(before + ' not found in array.');
		}

		return false;
	}

	static insertAt<T>(array: Array<T>, index: number, insert: T): void {
		if (array.length < index) {
			throw new Error('Index out of bound: ' + index);
		}

		const length = array.length;
		for (let i = length; i > index; i--) {
			array[i] = array[i - 1];
		}

		array[index] = insert;
	}

	static move<T>(array: Array<T>, fromIndex: number, toIndex: number): void {
		const orderItem = array[fromIndex];

		ArrayUtils.removeAt(array, fromIndex);
		ArrayUtils.insertAt(array, toIndex, orderItem);
	}

	static unique<T>(arr: Array<T>): Array<T> {
		return arr.filter((v: T, i: number, a: Array<T>) => a.indexOf(v) === i);
	}
}

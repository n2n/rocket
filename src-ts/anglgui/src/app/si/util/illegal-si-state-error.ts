export class IllegalSiStateError extends Error {
	constructor(m: string) {
		super(m);

		// Set the prototype explicitly.
		Object.setPrototypeOf(this, IllegalSiStateError.prototype);
	}
}

export class SiCommandError extends Error {
		constructor(m: string) {
				super(m);

				// Set the prototype explicitly.
				Object.setPrototypeOf(this, SiCommandError.prototype);
		}
}
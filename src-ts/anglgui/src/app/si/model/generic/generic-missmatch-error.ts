export class GenericMissmatchError extends Error {

	static assertTrue(arg: any, errMessage?: string) {
		if (arg !== true) {
			throw new GenericMissmatchError(errMessage);
		}
	}
}

import { SiInputResetPoint } from '../../../si-input-reset-point';

export class CallbackInputResetPoint<T> implements SiInputResetPoint {

	constructor(private value: T, private rollbackToCallback: (value: T) => void) {
	}

	rollbackTo(): Promise<void> {
		this.rollbackToCallback(this.value);
		return Promise.resolve();
	}
}

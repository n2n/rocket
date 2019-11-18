
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { SiProp } from './si-prop';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiType {
	private propMap = new Map<string, SiProp>();

	constructor(readonly qualifier: SiTypeQualifier) {
	}

	addProp(prop: SiProp) {
		this.propMap.set(prop.id, prop);
	}

	containsPropId(propId: string): boolean {
		return this.propMap.has(propId);
	}

	getPropById(propId: string): SiProp {
		if (this.containsPropId(propId)) {
			return this.propMap.get(propId);
		}

		throw new IllegalSiStateError('Unknown prop id: ' + propId);
	}

	getProps(): SiProp[] {
		return Array.from(this.propMap.values());
	}
}

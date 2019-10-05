
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { SiProp } from './si-prop';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';

export class SiType {
	public props = new Map<string, SiProp>();

	constructor(readonly qualifier: SiTypeQualifier) {
	}

	addProp(prop: SiProp) {
		this.props.set(prop.id, prop);
	}

	containsPropid(propId: string): boolean {
		return this.props.has(propId);
	}

	getPropById(propId: string): SiProp {
		if (this.containsPropid(propId)) {
			return this.props.get(propId);
		}

		throw new IllegalSiStateError('Unknown prop id: ' + propId);
	}
}

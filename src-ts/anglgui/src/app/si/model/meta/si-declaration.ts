
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiMask } from './si-type';

export class SiDeclaration {
	private masksMap = new Map<string, SiMask>();

	constructor(/*public style: SiStyle*/) {
	}

	addMask(mask: SiMask): void {
		this.masksMap.set(mask.qualifier.identifier.id, mask);
	}

	getBasicMask(): SiMask {
		// if (this.basicSiMask) {
		// 	return this.basicSiMask;
		// }

		const value = this.masksMap.values().next();
		if (value) {
			return value.value;
		}

		throw new IllegalSiStateError('SiDeclaration contains no SiMask.');
	}

	containsMaskId(maskId: string): boolean {
		return this.masksMap.has(maskId);
	}

	getMaskById(maskId: string): SiMask {
		if (this.masksMap.has(maskId)) {
			return this.masksMap.get(maskId)!;
		}

		throw new IllegalSiStateError('Unknown maskId: ' + maskId);
	}
}

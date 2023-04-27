
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiMaskDeclaration } from './si-mask-declaration';
import { SiStyle } from './si-view-mode';

export class SiDeclaration {
	private maskDeclarationMap = new Map<string, SiMaskDeclaration>();

	constructor(public style: SiStyle) {
	}



	addMaskDeclaration(maskDeclaration: SiMaskDeclaration): void {
		this.maskDeclarationMap.set(maskDeclaration.mask.qualifier.identifier.id, maskDeclaration);
	}

	getBasicMaskDeclaration(): SiMaskDeclaration {
		// if (this.basicSiMaskDeclaration) {
		// 	return this.basicSiMaskDeclaration;
		// }

		const value = this.maskDeclarationMap.values().next();
		if (value) {
			return value.value;
		}

		throw new IllegalSiStateError('SiDeclaration contains no SiMaskDeclaration.');
	}

	containsMaskId(typeId: string): boolean {
		return this.maskDeclarationMap.has(typeId);
	}

	getMaskDeclarationByMaskId(maskId: string): SiMaskDeclaration {
		if (this.maskDeclarationMap.has(maskId)) {
			return this.maskDeclarationMap.get(maskId)!;
		}

		throw new IllegalSiStateError('Unkown maskId: ' + maskId);
	}
}

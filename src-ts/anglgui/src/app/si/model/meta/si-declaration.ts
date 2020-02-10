
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiTypeDeclaration } from './si-type-declaration';

export class SiDeclaration {
	private typeDeclarationMap = new Map<string, SiTypeDeclaration>();

	constructor() {
	}

	constainsTypeId(typeId: string) {
		return this.typeDeclarationMap.has(typeId);
	}

	addTypeDeclaration(typeDeclaration: SiTypeDeclaration) {
		this.typeDeclarationMap.set(typeDeclaration.type.qualifier.id, typeDeclaration);
	}

	getBasicTypeDeclaration(): SiTypeDeclaration {
		// if (this.basicSiTypeDeclaration) {
		// 	return this.basicSiTypeDeclaration;
		// }

		const value = this.typeDeclarationMap.values().next();
		if (value) {
			return value.value;
		}

		throw new IllegalSiStateError('SiDeclaration contains no SiTypeDeclaration.');
	}

	containsTypeId(typeId: string): boolean {
		return this.typeDeclarationMap.has(typeId);
	}

	getTypeDeclarationByTypeId(typeId: string): SiTypeDeclaration {
		if (this.typeDeclarationMap.has(typeId)) {
			return this.typeDeclarationMap.get(typeId);
		}

		throw new IllegalSiStateError('Unkown typeId: ' + typeId);
	}
}

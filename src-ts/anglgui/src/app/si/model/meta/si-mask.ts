import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiProp } from './si-prop';
import { IllegalSiStateError } from '../../util/illegal-si-state-error';
import { SiStructureDeclaration } from './si-structure-declaration';
import { SiControl } from '../control/si-control';

export class SiMask {
	private propMap = new Map<string, SiProp>();
	public controls: SiControl[] = [];

	constructor(readonly qualifier: SiMaskQualifier, public structureDeclarations: Array<SiStructureDeclaration>|null) {
	}

	addProp(prop: SiProp) {
		this.propMap.set(prop.name, prop);
	}

	containsPropId(propId: string): boolean {
		return this.propMap.has(propId);
	}

	getPropById(propId: string): SiProp {
		if (this.containsPropId(propId)) {
			return this.propMap.get(propId)!;
		}

		throw new IllegalSiStateError('Unknown prop id: ' + propId);
	}

	getProps(): SiProp[] {
		return Array.from(this.propMap.values());
	}

	getDeclaredProps(): SiProp[] {
		// return this.type.getProps();
		return this.structureDeclarations!.filter(sd => !!sd.prop)
				.map(sd => sd.prop!);
	}
}

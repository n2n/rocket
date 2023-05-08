import { SiControlBoundry } from '../../si-control-bountry';
import { SiValueBoundary } from '../../../content/si-value-boundary';
import { SiDeclaration } from '../../../meta/si-declaration';

export class SimpleSiControlBoundry implements SiControlBoundry {

	constructor(public entries: SiValueBoundary[], public declaration: SiDeclaration) {
	}

	getBoundValueBoundaries(): SiValueBoundary[] {
		return this.entries;
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}
}

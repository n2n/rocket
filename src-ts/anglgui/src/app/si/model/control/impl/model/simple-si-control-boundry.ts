import { SiControlBoundry } from '../../si-control-boundry';
import { SiValueBoundary } from '../../../content/si-value-boundary';
import { SiDeclaration } from '../../../meta/si-declaration';

export class SimpleSiControlBoundry implements SiControlBoundry {

	constructor(public entries: SiValueBoundary[], public declaration: SiDeclaration, public apiUrl: string|null) {
	}

	getBoundValueBoundaries(): SiValueBoundary[] {
		return this.entries;
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}

	getBoundApiUrl(): string|null {
		return this.apiUrl;
	}
}

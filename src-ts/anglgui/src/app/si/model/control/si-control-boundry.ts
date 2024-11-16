import { SiValueBoundary } from '../content/si-value-boundary';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiControlBoundry {

	getBoundValueBoundaries(): SiValueBoundary[];

	getBoundDeclaration(): SiDeclaration;

	getBoundApiUrl(): string|null;

}

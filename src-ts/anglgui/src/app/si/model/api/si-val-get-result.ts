import { SiValueBoundary } from 'src/app/si/model/content/si-value-boundary';
import { SiDeclaration } from '../meta/si-declaration';

export interface SiValGetResult {

	declaration: SiDeclaration|null;

	valueBoundary: SiValueBoundary|null;
}

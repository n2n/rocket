import { SiValueBoundary } from 'src/app/si/model/content/si-value-boundary';
import { SiPartialContent } from 'src/app/si/model/content/si-partial-content';
import { SiDeclaration } from '../meta/si-declaration';
import { SiControl } from '../control/si-control';

export interface SiGetResult {

	declaration: SiDeclaration|null;

	generalControls: SiControl[]|null;

	valueBoundary: SiValueBoundary|null;

	partialContent: SiPartialContent|null;
}

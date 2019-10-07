import { SiDeclaration } from 'src/app/si/model/content/si-entry-declaration';
import { SiEntry } from 'src/app/si/model/content/si-entry';

export interface SiValGetResult {

	declaration: SiDeclaration|null;

	entry: SiEntry|null;
}

import { SiValueBoundary } from '../../../content/si-value-boundary';
import { StructureBranchModel } from 'src/app/ui/structure/comp/structure-branch-model';
import { SiDeclaration } from '../../../meta/si-declaration';

export interface BulkyEntryModel {

	getSiDeclaration(): SiDeclaration;

	getSiValueBoundary(): SiValueBoundary;

	getContentStructureBranchModel(): StructureBranchModel;


}

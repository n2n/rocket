import { SiValueBoundary } from '../../../content/si-value-boundary';
import { StructureBranchModel } from 'src/app/ui/structure/comp/structure-branch-model';

export interface BulkyEntryModel {

	getSiEntry(): SiValueBoundary;

	getContentStructureBranchModel(): StructureBranchModel;
}

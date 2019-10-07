import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';
import { SiField } from '../../../content/si-field';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { PlainContentComponent } from 'src/app/ui/structure/comp/plain-content/plain-content.component';

export class SiUiStructureModelFactory {
	static createBulkyField(field: SiField): UiStructureModel {
		const model = new SimpleUiStructureModel(
				new TypeUiContent(StructureBranchComponent, (ref, uiStructure) => {
					ref.instance.uiStructure = uiStructure;
					ref.instance.uiContent = field.createUiContent();
				}));
		model.messagesCallback = () => field.getMessages();
		model.disabledCallback = () => field.isDisabled();
		return model;
	}

	static createBulkyEmpty(): UiStructureModel {
		return new SimpleUiStructureModel(
				new TypeUiContent(StructureBranchComponent, (ref, uiStructure) => {
					ref.instance.uiStructure = uiStructure;
				}));
	}

	static createCompactField(field: SiField): UiStructureModel {
		const model = new SimpleUiStructureModel(
				new TypeUiContent(PlainContentComponent, (ref, uiStructure) => {
					ref.instance.uiStructure = uiStructure;
					ref.instance.uiContent = field.createUiContent();
				}));
		model.messagesCallback = () => field.getMessages();
		model.disabledCallback = () => field.isDisabled();
		return model;
	}
}

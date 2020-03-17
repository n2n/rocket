import { SiFieldAdapter } from './si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';

export abstract class SimpleSiFieldAdapter extends SiFieldAdapter {

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	createUiStructureModel(): UiStructureModel {
		const model = new SimpleUiStructureModel(null);
		model.initCallback = (uiStructure) => { model.content = this.createUiContent(uiStructure); };
		model.messagesCallback = () => this.getMessages();
		model.setDisabled$(this.disabledSubject);
		return model;
	}

	protected abstract createUiContent(uiStructure: UiStructure): UiContent;
}

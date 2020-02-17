import { SiPanel } from './si-panel';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { EmbeddedEntryPanelsInComponent } from '../comp/embedded-entry-panels-in/embedded-entry-panels-in.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiService } from 'src/app/si/manage/si.service';
import { EmbeddedEntryPanelInModel } from '../comp/embedded-entry-panels-in-model';
import { EmbeddedAddPasteObtainer } from './embedded-add-paste-obtainer';
import { AddPasteObtainer } from '../comp/add-paste-obtainer';

export class EmbeddedEntryPanelsInSiField extends InSiFieldAdapter implements EmbeddedEntryPanelInModel {
	constructor(public siService: SiService, public apiUrl: string, public panels: SiPanel[]) {
		super();
	}

	getObtainer(): AddPasteObtainer {
		return new EmbeddedAddPasteObtainer(this.siService, this.apiUrl, true);
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	readInput(): object {
		throw new Error('Not yet implemented.');
	}

	copy(): SiField {
		throw new Error('Not yet implemented.');
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(EmbeddedEntryPanelsInComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}
}

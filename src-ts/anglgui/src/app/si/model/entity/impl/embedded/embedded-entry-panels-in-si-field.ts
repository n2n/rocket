import { InSiFieldAdapter } from '../in-si-field-adapter';
import { EmbeddedEntryPanelModel } from 'src/app/ui/content/embedded/embedded-entry-panels-model';
import { SiField } from '../../si-field';
import { SiContent } from '../../../structure/si-content';
import { TypeSiContent } from '../../../structure/impl/type-si-content';
import { SiPanel } from './si-panel';
import { EmbeddedEntryPanelsInComponent } from 'src/app/ui/content/embedded/comp/embedded-entry-panels-in/embedded-entry-panels-in.component';

export class EmbeddedEntryPanelsInSiField extends InSiFieldAdapter implements EmbeddedEntryPanelModel {
	
	constructor(public apiUrl: string, public panels: SiPanel[]) {
		super();
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

	createContent(): SiContent {
		return new TypeSiContent(EmbeddedEntryPanelsInComponent, (ref, structure) => {
			ref.instance.model = this;
			ref.instance.siStructure = structure;
		});
	}
}

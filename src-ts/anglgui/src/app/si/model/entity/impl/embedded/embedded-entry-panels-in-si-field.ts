import { InSiFieldAdapter } from '../in-si-field-adapter';
import { SiType } from '../../si-type';
import { EmbeddedEntryPanelModel } from 'src/app/ui/content/embedded/embedded-entry-panels-model';
import { SiField } from '../../si-field';
import { SiContent } from '../../../structure/si-content';
import { TypeSiContent } from '../../../structure/impl/type-si-content';
import { SiPanel } from './si-panel';
import { EmbeddedEntryPanelsInComponent } from 'src/app/ui/content/embedded/comp/embedded-entry-panels-in/embedded-entry-panels-in.component';

export class EmbeddedEntryPanelsInSiField extends InSiFieldAdapter implements EmbeddedEntryPanelModel {
	public sortable = true;
	public panels: SiPanel[];
	public pastCategory: string|null = null;
	public allowedSiTypes: SiType[]|null = null;

	constructor(public apiUrl: string) {
		super();
	}

	getPanels(): SiPanel[] {
		return this.panels;
	}

	isSortable(): boolean {
		return this.sortable;
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getPastCategory(): string|null {
		return this.pastCategory;
	}

	readInput(): object {
		throw new Error('Not yet implemented.');
	}

	copy(): SiField {
		throw new Error('Not yet implemented.');
	}

	getContent(): SiContent {
		return new TypeSiContent(EmbeddedEntryPanelsInComponent, (ref, structure) => {
			ref.instance.model = this;
			ref.instance.siStructure = structure;
		});
	}
}

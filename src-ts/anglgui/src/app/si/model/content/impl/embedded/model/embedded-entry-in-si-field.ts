
import { EmbeddedEntriesInUiContent } from './embedded-entries-in-si-content';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiField } from '../../../si-field';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';

export class EmbeddedEntryInSiField extends InSiFieldAdapter	{

	config = new EmbeddedEntriesConfig();

	constructor(private siService: SiService, private apiUrl: string, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	readInput(): object {
		return { entryInputs: this.values.map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new EmbeddedEntriesInUiContent(this.siService, this.apiUrl, this.values, uiStructure, this.config);
	}

	copy(): SiField {
		throw new Error('not yet implemented');
	}

}


import { EmbeddedEntriesInUiContent } from './embedded-entries-in-si-content';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiField } from '../../../si-field';

export class EmbeddedEntryInSiField extends InSiFieldAdapter	{

	content: EmbeddedEntriesInUiContent;

	constructor(apiUrl: string, values: SiEmbeddedEntry[] = []) {
		super();
		this.content = new EmbeddedEntriesInUiContent(apiUrl, values);
	}

	readInput(): object {
		return { entryInputs: this.content.getValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiContent(): UiContent {
		return this.content;
	}

	copy(): SiField {
		throw new Error('not yet implemented');
	}
}

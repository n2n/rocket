
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { SiField } from '../../si-field';
import { EmbeddedEntriesInSiContent } from './embedded-entries-in-si-content';
import { SiEmbeddedEntry } from './si-embedded-entry';

export class EmbeddedEntryInSiField extends InSiFieldAdapter	{

	content: EmbeddedEntriesInSiContent;

	constructor(apiUrl: string, values: SiEmbeddedEntry[] = []) {
		super();
		this.content = new EmbeddedEntriesInSiContent(apiUrl, values);
	}

	readInput(): object {
		return { entryInputs: this.content.getValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createContent(): UiContent|null {
		return this.content;
	}

	copy(): SiField {
		throw new Error('not yet implemented');
	}
}

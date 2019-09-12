
import { InSiFieldAdapter } from 'src/app/si/model/content/impl/in-si-field-adapter';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiField } from '../si-field';
import { SiContent } from 'src/app/si/model/structure/si-content';
import { EmbeddedEntriesInSiContent } from 'src/app/si/model/structure/impl/embedded/embedded-entries-in-si-content';

export class EmbeddedEntryInSiField extends InSiFieldAdapter  {

	content: EmbeddedEntriesInSiContent;

	constructor(zone: SiZone, apiUrl: string, values: SiEmbeddedEntry[] = []) {
		super();
		this.content = new EmbeddedEntriesInSiContent(zone, apiUrl, values);
	}

	readInput(): object {
		console.log({ entryInputs: this.content.getValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) });
		return { entryInputs: this.content.getValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	getContent(): SiContent|null {
		return this.content;
	}

	copy(): SiField {
		throw new Error('not yet implemented');
	}
}

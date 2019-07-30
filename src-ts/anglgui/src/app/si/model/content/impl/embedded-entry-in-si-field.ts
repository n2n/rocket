
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { InSiFieldAdapter } from 'src/app/si/model/content/impl/in-si-field-adapter';
import { SiZone } from 'src/app/si/model/structure/si-zone';
import { SiStructure } from 'src/app/si/model/structure/si-structure';
import { SiEmbeddedEntry } from 'src/app/si/model/content/si-embedded-entry';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiField } from '../si-field';
import { SiType } from 'src/app/si/model/content/si-type';
import { SiContent } from "src/app/si/model/structure/si-content";
import { EmbeddedEntriesInSiContent } from "src/app/si/model/structure/impl/embedded/embedded-entries-in-si-content";

export class EmbeddedEntryInSiField extends InSiFieldAdapter  {
    
	content: EmbeddedEntriesInSiContent;
	
	constructor(zone: SiZone, apiUrl: string, values: SiEmbeddedEntry[] = []) {
		super();
		this.content = new EmbeddedEntriesInSiContent(zone, apiUrl, values);
	}

	readInput(): object {
		return { values: this.content.getValues() };
	}

	getContent(): SiContent|null {
		return this.content;
	}
	
	copy(): SiField {
        throw new Error('not yet implemented');
    }
}


import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { SiContent } from "src/app/si/model/structure/si-zone-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";

export interface EmbeddedEntryInModel {
	
	isNonNewRemovable(): boolean;
	
	getMax(): number;
	
	isReduced(): boolean;
	
	getValues(): SiEmbeddedEntry[];
	
	setValues(values: SiEmbeddedEntry[]);
}
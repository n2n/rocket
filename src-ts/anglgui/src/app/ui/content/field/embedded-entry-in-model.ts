
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { CompactEntrySiComp } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { BulkyEntrySiComp } from "src/app/si/model/structure/impl/bulky-entry-si-content";
import { SiComp } from "src/app/si/model/structure/si-zone-content";
import { SiStructure } from "src/app/si/model/structure/si-structure";
import { SiEmbeddedEntry } from "src/app/si/model/content/si-embedded-entry";
import { SiZone } from "src/app/si/model/structure/si-zone";

export interface EmbeddedEntryInModel {
	
	getSiZone(): SiZone;
	
	isNonNewRemovable(): boolean;
	
	getMax(): number;
	
	isReduced(): boolean;
	
	getValues(): SiEmbeddedEntry[];
	
	setValues(values: SiEmbeddedEntry[]);
	
	registerSiStructure(siStructure: SiStructure);
	
	unregisterSiStructure(siStructure: SiStructure);
}

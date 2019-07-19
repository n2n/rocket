
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiIdentifier } from "src/app/si/model/content/si-qualifier";
import { CompactEntrySiContent } from "src/app/si/model/structure/impl/compact-entry-si-content";
import { BulkyEntrySiContent } from "src/app/si/model/structure/impl/bulky-entry-si-content";

export interface EmbeddedEntryInModel {
	
	isNonNewRemovable(): boolean;
	
	getMax(): number;
	
	isReduced(): boolean;
	
	getValues(): BulkyEntrySiContent[];
	
	setValues(values: BulkyEntrySiContent[]);
	
	findSummarySiContent(siIdentifier: SiIdentifier): CompactEntrySiContent|null;
	
	addSummarySiContent(siContent: CompactEntrySiContent|null);
}
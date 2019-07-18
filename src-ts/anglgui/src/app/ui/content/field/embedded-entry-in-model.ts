
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiIdentifier } from "src/app/si/model/content/si-qualifier";

export interface EmbeddedEntryInModel {
	
	isNonNewRemovable(): boolean;
	
	getMax(): number;
	
	isReduced(): boolean;
	
	getValues(): SiEntry[];
	
	setValues(values: SiEntry[]);
	
	findSummarySiEntry(siIdentifier: SiIdentifier): SiEntry|null;
	
	addSummarySiEntry(siEntry: SiEntry|null);
}
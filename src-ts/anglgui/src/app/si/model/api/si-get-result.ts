import { SiBulkyDeclaration } from "src/app/si/model/structure/si-bulky-declaration";
import { SiCompactDeclaration } from "src/app/si/model/structure/si-compact-declaration";
import { SiEntry } from "src/app/si/model/content/si-entry";
import { SiPartialContent } from "src/app/si/model/content/si-partial-content";

export interface SiGetResult {
	
	compactDeclaration: SiCompactDeclaration|null;

	bulkyDeclaration: SiBulkyDeclaration|null;

	entry: SiEntry|null;

	partialContent: SiPartialContent|null;

}
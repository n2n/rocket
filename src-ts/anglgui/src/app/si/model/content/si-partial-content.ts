
import { SiEntry } from "src/app/si/model/content/si-entry";

export interface SiPartialContent {
	entries: SiEntry[];
	count: number;
	offset: number;
}
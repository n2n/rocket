
import { SiEntry } from "src/app/si/model/entity/si-entry";

export interface SiPartialContent {
	entries: SiEntry[];
	count: number;
	offset: number;
}
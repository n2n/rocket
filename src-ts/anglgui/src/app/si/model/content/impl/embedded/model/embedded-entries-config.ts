import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';

export class EmbeddedEntriesConfig {
	public min = 0;
	public max: number|null = null;
	public reduced = false;
	public nonNewRemovable = true;
	public sortable = false;
	public pasteCategory: string|null = null;
	public allowedSiTypeQualifiers: SiTypeQualifier[]|null = null;
}

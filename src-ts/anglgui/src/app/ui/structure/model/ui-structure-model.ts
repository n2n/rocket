
import { UiContent } from './ui-content';
import { Observable } from 'rxjs';
import { UiStructure } from './ui-structure';
import { UiZoneError } from './ui-zone-error';

export interface UiStructureModel {

	bind(uiStructure: UiStructure): void;

	unbind(): void;

	getContent(): UiContent|null;

	getAsideContents(): UiContent[];

	getZoneErrors(): UiZoneError[];

	getDisabled$(): Observable<boolean>;

	getMode(): UiStructureModelMode;
}

export enum UiStructureModelMode {
	NONE = 0,
	ITEM_COLLECTION = 1
}

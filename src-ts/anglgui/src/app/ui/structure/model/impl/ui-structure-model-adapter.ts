import { UiStructureModel, UiStructureModelMode } from '../ui-structure-model';
import { UiContent } from '../ui-content';
import { Observable, of } from 'rxjs';
import { UiStructure } from '../ui-structure';
import { UiZoneError } from '../ui-zone-error';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureError } from '../ui-structure-error';

export abstract class UiStructureModelAdapter implements UiStructureModel {
	protected boundUiStructure: UiStructure|null = null;
	protected uiContent: UiContent|null = null;
	protected mainControlUiContents: UiContent[] = [];
	protected asideUiContents: UiContent[] = [];
	protected disabled$: Observable<boolean>;

	bind(uiStructure: UiStructure): void {
		IllegalStateError.assertTrue(!this.boundUiStructure, 'UiStructureModel already bound. ');
		this.boundUiStructure = uiStructure;
	}

	unbind(): void {
		IllegalStateError.assertTrue(!!this.boundUiStructure, 'UiStructureModel not bound.');
		this.boundUiStructure = null;
	}

	protected reqBoundUiStructure(): UiStructure {
		IllegalStateError.assertTrue(!!this.boundUiStructure, 'UiStructureModel not bound.');
		return this.boundUiStructure;
	}

	getContent(): UiContent|null {
		return this.uiContent;
	}

	getMainControlContents(): UiContent[] {
		return this.mainControlUiContents;
	}

	getAsideContents(): UiContent[] {
		return this.asideUiContents;
	}

	abstract getStructureErrors(): UiStructureError[];

	abstract getStructureErrors$(): Observable<UiStructureError[]>;

	getDisabled$(): Observable<boolean> {
		if (!this.disabled$) {
			this.disabled$ = of(false);
		}

		return this.disabled$;
	}

	getMode(): UiStructureModelMode {
		return UiStructureModelMode.NONE;
	}
}

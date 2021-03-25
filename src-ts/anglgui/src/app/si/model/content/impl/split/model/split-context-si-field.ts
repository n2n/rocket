import { SplitOption } from './split-option';
import { SiEntry } from '../../../si-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGetRequest } from 'src/app/si/model/api/si-get-request';
import { SiGetInstruction } from 'src/app/si/model/api/si-get-instruction';
import { SiGetResponse } from 'src/app/si/model/api/si-get-response';
import { map } from 'rxjs/operators';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiControlBoundry } from 'src/app/si/model/control/si-control-bountry';
import { SimpleSiFieldAdapter } from '../../common/model/simple-si-field-adapter';
import { Observable } from 'rxjs';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiGenericEntry } from 'src/app/si/model/generic/si-generic-entry';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiStyle } from 'src/app/si/model/meta/si-view-mode';
import {OutSiFieldAdapter} from '../../common/model/out-si-field-adapter';
import {SplitContextInSiField} from './split-context-in-si-field';
import {SplitContent} from './split-content-collection';
import {SiInputResetPoint} from '../../../si-input-reset-point';

export abstract class SplitContext extends OutSiFieldAdapter {
	public style: SplitStyle = { iconClass: null, tooltip: null };


	constructor() {
		super();
	}

	abstract isKeyActive(key: string): boolean;

	abstract activateKey(key: string): void;

	abstract deactivateKey(key: string): void;

	abstract get activeKeys$(): Observable<string[]>;

	abstract hasInput(): boolean;

	abstract readInput(): object;

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	protected abstract createUiContent(): UiContent;

	copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(SplitContextCopy.fromMap(this.splitContentMap));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		genericValue.readInstance(SplitContextCopy).applyToMap(this.splitContentMap);
		return Promise.resolve();
	}

	createResetPoint(): SiGenericValue {
		return new SiGenericValue(SplitContextResetPoint.create(this.splitContentMap, this));
	}

	resetToPoint(genericValue: SiGenericValue): void {
		genericValue.readInstance(SplitContextResetPoint).apply(this.splitContentMap, this);
	}
}


class SplitContextResetPoint implements SiInputResetPoint {
	private activeKeys = new Array<string>();
	private genericEntryMap = new Map<string, SiGenericEntry>();

	static async create(map: Map<string, SplitContent>, splitContext: SplitContext): Promise<SplitContextResetPoint> {
		const scrp = new SplitContextResetPoint();

		for (const [key, splitContent] of map) {
			if (splitContext.isKeyActive(key)) {
				continue;
			}

			scrp.activeKeys.push(key);

			const entry = splitContent.getLoadedSiEntry$().then((entry) => {
			  if (entry) {
          scrp.genericEntryMap.set(key, entry.createInputResetPoint());
        }
      });
		}

		return scrp;
	}

	private containsActiveKey(key: string): boolean {
		return -1 !== this.activeKeys.indexOf(key);
	}

	apply(splitContentMap: Map<string, SplitContent>, splitContext: SplitContextSiField): void {
		for (const [key, splitContent] of splitContentMap) {
			if (this.containsActiveKey(key)) {
				splitContext.activateKey(key);
			} else {
				splitContext.deactivateKey(key);
			}

			if (this.genericEntryMap.has(key)) {
				splitContent.getLoadedSiEntry().resetToPoint(this.genericEntryMap.get(key));
			} else {
				splitContent.resetLazyLoad();
			}
		}
	}
}




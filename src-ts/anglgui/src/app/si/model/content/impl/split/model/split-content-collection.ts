import {SplitContent} from './split-context-si-field';
import {SplitOption} from './split-option';
import {SiEntry} from '../../../si-entry';
import {SiGenericValue} from '../../../../generic/si-generic-value';
import {SiGenericEntry} from '../../../../generic/si-generic-entry';
import {SiStyle} from '../../../../meta/si-view-mode';
import {SiService} from '../../../../../manage/si.service';
import {SiControlBoundry} from '../../../../control/si-control-bountry';
import {SiGetInstruction} from '../../../../api/si-get-instruction';
import {SiGetRequest} from '../../../../api/si-get-request';
import {map} from 'rxjs/operators';
import {SiGetResponse} from '../../../../api/si-get-response';

export class SplitContentCollection {
  protected splitContentMap = new Map<string, SplitContent>();

  putSplitContent(splitContent: SplitContent) {
    this.splitContentMap.set(splitContent.key, splitContent);
  }

  getSplitOptions(): SplitOption[] {
    return Array.from(this.splitContentMap.values());
  }

  getEntry$(key: string): Promise<SiEntry> {
    if (this.splitContentMap.has(key)) {
      return this.splitContentMap.get(key).getSiEntry$();
    }

    throw new Error('Unknown key.');
  }

  copy(): Promise<SplitContextCopy> {
    return SplitContextCopy.fromMap(this.splitContentMap);
  }

  past(splitContextCopy: SplitContextCopy): Promise<void> {
    return splitContextCopy.applyToMap(this.splitContentMap);
  }
}

export class SplitContent implements SplitOption {
  private entry$: Promise<SiEntry>|null = null;
  private lazyDef: LazyDef|null = null;
  private loadedEntry: SiEntry|null = null;

  constructor(readonly key: string, public label: string, public shortLabel: string) {
  }

  static createUnavaialble(key: string, label: string, shortLabel: string): SplitContent {
    const splitContent = new SplitContent(key, label, shortLabel);
    splitContent.entry$ = Promise.resolve(null);
    return splitContent;
  }

  static createLazy(key: string, label: string, shortLabel: string, lazyDef: LazyDef): SplitContent {
    const splitContent = new SplitContent(key, label, shortLabel);
    splitContent.lazyDef = lazyDef;
    return splitContent;
  }

  static createEntry(key: string, label: string, shortLabel: string, entry: SiEntry): SplitContent {
    const splitContent = new SplitContent(key, label, shortLabel);
    splitContent.entry$ = Promise.resolve(entry);
    splitContent.loadedEntry = entry;
    return splitContent;
  }

  resetLazyLoad(): void {

    // todo
    // if (this.lazyDef) {
    // 	this.entry$ = null;
    // 	this.loadedEntry = null;
    // 	return;
    // }

    // throw new IllegalSiStateError('SplitContent was not lazy loaded!');
  }

  getLoadedSiEntry(): SiEntry|null {
    return this.loadedEntry;
  }

  getLoadedSiEntry$(): Promise<SiEntry|null>|null {
    if (this.loadedEntry) {
      return Promise.resolve(this.loadedEntry);
    }

    if (this.entry$) {
      return this.entry$;
    }

    return null;
  }

  getSiEntry$(): Promise<SiEntry|null> {
    if (this.entry$) {
      return this.entry$;
    }

    let instruction: SiGetInstruction|null = null;
    if (this.lazyDef.entryId) {
      instruction = SiGetInstruction.entry(this.lazyDef.style, this.lazyDef.entryId);
    } else {
      instruction = SiGetInstruction.newEntry(this.lazyDef.style);
    }
    instruction.setPropIds(this.lazyDef.propIds);

    return this.entry$ = this.lazyDef.siService.apiGet(this.lazyDef.apiGetUrl, new SiGetRequest(instruction))
      .pipe(map((response: SiGetResponse) => {
        return this.loadedEntry = response.results[0].entry;
      }))
      .toPromise();
  }
}

export interface LazyDef {
  apiGetUrl: string;
  entryId: string|null;
  propIds: string[]|null;
  style: SiStyle;
  siService: SiService;
  siControlBoundy: SiControlBoundry;
}

export interface SplitStyle {
  iconClass: string|null;
  tooltip: string|null;
}


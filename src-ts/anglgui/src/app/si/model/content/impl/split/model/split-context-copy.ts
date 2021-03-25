import {SiGenericEntry} from '../../../../generic/si-generic-entry';
import {SplitContent} from './split-context-si-field';

export class SplitContextCopy {
  private genericMap = new Map<string, SiGenericEntry>();

  static async fromMap(map: Map<string, SplitContent>): Promise<SplitContextCopy> {
    const gsc = new SplitContextCopy();

    const promises = new Array<Promise<void>>();
    for (const [key, value] of map) {
      const entryPromise = value.getLoadedSiEntry$();
      if (!entryPromise) {
        continue;
      }

      promises.push(entryPromise.then(entry => {
        if (!entry) {
          return;
        }

        return entry.copy().then(genericEntry => {
          gsc.genericMap.set(key, genericEntry);
        });
      }));
    }

    await Promise.all(promises);

    return gsc;
  }

  async applyToMap(splitContentMap: Map<string, SplitContent>): Promise<void> {
    const promises = new Array<Promise<void>>();

    for (const [key, genericEntry] of this.genericMap) {
      const siEntry = splitContentMap.get(key)?.getLoadedSiEntry();
      if (siEntry) {
        promises.push(siEntry.paste(genericEntry));
      }
    }

    await Promise.all(promises);
  }
}

import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntry } from '../../../si-entry';
import { SplitContextSiField } from './split-context';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitManagerComponent } from '../comp/split-manager/split-manager.component';

export class SplitContextInSiField extends SplitContextSiField {

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		const entryInputObj = {};
		for (const [, splitContent] of this.splitContentMap) {
			let entry: SiEntry;
			if (entry = splitContent.getLoadedSiEntry()) {
				entryInputObj[splitContent.key] = entry.readInput();
			}
		}
		return entryInputObj;
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	protected createUiContent(): UiContent {
		throw new TypeUiContent(SplitManagerComponent, (ref) => {

		});
	}
}

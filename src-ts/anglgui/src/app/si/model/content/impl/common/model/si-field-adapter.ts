import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiField } from '../../../si-field';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable, BehaviorSubject } from 'rxjs';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { BehaviorCollection } from 'src/app/util/collection/behavior-collection';

export abstract class SiFieldAdapter implements SiField/*, MessageFieldModel*/ {
	protected messagesCollection = new BehaviorCollection<Message>([]);
	private disabledSubject = new BehaviorSubject<boolean>(false);

	abstract hasInput(): boolean;

	abstract readInput(): object;

	isDisplayable(): boolean {
		return true;
	}

	isDisabled(): boolean {
		return this.disabledSubject.getValue();
	}

	setDisabled(disabled: boolean) {
		this.disabledSubject.next(disabled);
	}

	getDisabled$(): Observable<boolean> {
		return this.disabledSubject.asObservable();
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	isGeneric(): boolean {
		return false;
	}

	abstract createUiStructureModel(compactMode: boolean): UiStructureModel;

	getMessages$(): Observable<Message[]> {
		return this.messagesCollection.get$();
	}

	getMessages(): Message[] {
		return this.messagesCollection.get();
	}

	handleError(error: SiFieldError): void {
		this.addMessage(...error.getAllMessages());
	}

	protected addMessage(...newMessages: Message[]) {
		this.messagesCollection.push(...newMessages);
	}

	resetError(): void {
		this.messagesCollection.clear();
	}

	abstract copyValue(): SiGenericValue;

	abstract pasteValue(genericValue: SiGenericValue): Promise<void>;

	createResetPoint(): SiGenericValue {
		return this.copyValue();
	}

	resetToPoint(genericValue: SiGenericValue): void {
		this.pasteValue(genericValue);
	}

	consume(consumeableSiField): SiField {
		return consumeableSiField;
	}
}

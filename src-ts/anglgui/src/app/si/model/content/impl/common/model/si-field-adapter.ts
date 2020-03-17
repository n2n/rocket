import { SiFieldError } from 'src/app/si/model/input/si-field-error';
import { Message } from 'src/app/util/i18n/message';
import { SiField } from '../../../si-field';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { Observable, BehaviorSubject } from 'rxjs';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';

export abstract class SiFieldAdapter implements SiField/*, MessageFieldModel*/ {
	protected messages = new Array<Message>();
	protected disabledSubject = new BehaviorSubject<boolean>(false);

	abstract hasInput(): boolean;

	abstract readInput(): object;

	isDisabled(): boolean {
		return this.disabledSubject.getValue();
	}

	setDisabled(disabled: boolean) {
		this.disabledSubject.next(disabled);
	}

	getDisabled$(): Observable<boolean> {
		return this.disabledSubject;
	}

	// abstract copy(entryBuildUp: SiEntryBuildup): SiField;

	isGeneric(): boolean {
		return false;
	}

	abstract createUiStructureModel(): UiStructureModel;

	getMessages(): Message[] {
		return this.messages;
	}

	handleError(error: SiFieldError): void {
		this.messages.push(...error.getAllMessages());
	}

	resetError(): void {
		this.messages = [];
	}

	abstract copyValue(): SiGenericValue;

	abstract pasteValue(genericValue: SiGenericValue): Promise<void>;

	createResetPoint(): SiGenericValue {
		return this.copyValue();
	}

	resetToPoint(genericValue: SiGenericValue): void {
		this.pasteValue(genericValue);
	}
}

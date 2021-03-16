import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { Message } from 'src/app/util/i18n/message';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { PasswordInModel } from '../comp/password-in-model';
import { PasswordInComponent } from '../comp/password-in/password-in.component';

export class PasswordInSiField extends InSiFieldAdapter implements PasswordInModel {
	public mandatory = false;
	public minlength: number|null = null;
	public maxlength: number|null = null;
	public passwordSet = false;
	public rawPassword: string|null = null;
	public tmpRawPassword: string|null = null;

	constructor(public label: string) {
		super();
		this.validate();
	}

	private validate(): void {
		this.resetError();

		if (this.mandatory && !this.passwordSet && this.rawPassword === null) {
			this.addMessage(Message.createCode('mandatory_err', new Map([['{field}', this.label]])));
		}

		if (this.minlength && this.rawPassword && this.rawPassword.length < this.minlength) {
			this.addMessage(Message.createCode('minlength_err', new Map([['{field}', this.label], ['{minlength}', this.minlength.toString()]])));
		}

		if (this.maxlength && this.rawPassword && this.rawPassword.length > this.maxlength) {
			this.addMessage(Message.createCode('maxlength_err', new Map([['{field}', this.label], ['{maxlength}', this.maxlength.toString()]])));
		}
	}

	isGeneric(): boolean {
		return true;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.rawPassword === null ? null : new String(this.rawPassword));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.rawPassword = null;
			return Promise.resolve();
		}

		if (genericValue.isInstanceOf(String)) {
			this.rawPassword = genericValue.readInstance(String).valueOf();
			return Promise.resolve();
		}

		throw new GenericMissmatchError('String expected.');
	}

	getMaxlength(): number|null {
		return this.maxlength;
	}

	getMinlength(): number|null {
		return this.minlength;
	}

	isPasswordSet(): boolean {
		return this.passwordSet;
	}

	setRawPassword(rawPassword: string): void {
		this.tmpRawPassword = rawPassword;
	}

	onBlur(): void {
		this.rawPassword = this.tmpRawPassword;
		this.validate();
	}

	onFocus(): void {
		this.messagesCollection.clear();
	}

	createUiContent(): UiContent {
		return new TypeUiContent(PasswordInComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	readInput(): object {
		return { rawPassword: this.rawPassword };
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(InputInFieldComponent);
//
// 		const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 		const component = componentRef.instance;
// 		component.model = this;
//
// 		return componentRef;
// 	}
}

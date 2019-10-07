import { LinkOutModel } from '../alphanum/comp/link-field-model';
import { OutSiFieldAdapter } from '../common/model/out-si-field-adapter';
import { LinkOutFieldComponent } from '../alphanum/comp/link-out-field/link-out-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/impl/type-si-content';
import { SiField } from '../../si-field';

export class LinkOutSiField extends OutSiFieldAdapter implements LinkOutModel {


	constructor(private href: boolean, private ref: string, private label: string) {
		super();
	}

	createUiContent(): UiContent|null {
		return new TypeUiContent(LinkOutFieldComponent, (ref) => {
			ref.instance.model = this;
		});
	}

// 	initComponent(viewContainerRef: ViewContainerRef,
// 			componentFactoryResolver: ComponentFactoryResolver,
// 			commanderService: SiUiService): ComponentRef<any> {
// 		const componentFactory = componentFactoryResolver.resolveComponentFactory(LinkOutFieldComponent);
//
// 			const componentRef = viewContainerRef.createComponent(componentFactory);
//
// 			componentRef.instance.model = this;
//
// 			return componentRef;
// 	}

	isHref(): boolean {
		return this.href;
	}

	getRef(): string {
		return this.ref;
	}

	getLabel(): string {
		return this.label;
	}

	copy(): SiField {
		return new LinkOutSiField(this.href, this.ref, this.label);
	}
}

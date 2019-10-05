
import { OutSiFieldAdapter } from 'src/app/si/model/entity/impl/out-si-field-adapter';
import { LinkOutModel } from 'src/app/ui/content/field/link-field-model';
import { LinkOutFieldComponent } from 'src/app/ui/content/field/comp/link-out-field/link-out-field.component';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { TypeSiContent } from 'src/app/si/model/structure/impl/type-si-content';

export class LinkOutSiField extends OutSiFieldAdapter implements LinkOutModel {


	constructor(private href: boolean, private ref: string, private label: string) {
		super();
	}

	createContent(): UiContent|null {
		return new TypeSiContent(LinkOutFieldComponent, (ref) => {
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
}

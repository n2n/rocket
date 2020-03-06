import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { LinkOutModel } from '../comp/link-field-model';
import { LinkOutFieldComponent } from '../comp/link-out-field/link-out-field.component';
import { SiField } from '../../../si-field';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';

export class LinkOutSiField extends OutSiFieldAdapter implements LinkOutModel {


	constructor(private navPoint: UiNavPoint, private label: string) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(LinkOutFieldComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiZone = uiStructure.getZone();
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

	getUiNavPoint(): UiNavPoint {
		return this.navPoint;
	}

	getLabel(): string {
		return this.label;
	}

	copy(): SiField {
		return new LinkOutSiField(this.navPoint, this.label);
	}

	readGenericValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		throw new Error('Not yet implemented');
	}
}

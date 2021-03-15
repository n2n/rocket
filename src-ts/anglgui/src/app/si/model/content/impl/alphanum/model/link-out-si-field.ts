import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { LinkOutModel } from '../comp/link-field-model';
import { LinkOutFieldComponent } from '../comp/link-out-field/link-out-field.component';
import { SiField } from '../../../si-field';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { SiNavPoint } from 'src/app/si/model/control/si-nav-point';
import { Injector } from '@angular/core';

export class LinkOutSiField extends OutSiFieldAdapter {
	
	public lytebox = false;

	constructor(public navPoint: SiNavPoint, public label: string, private injector: Injector) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(LinkOutFieldComponent, (ref) => {
			ref.instance.model = this.createLinkOutModel(uiStructure.getZone());
		});
	}

	private createLinkOutModel(uiZone: UiZone): LinkOutModel {
		return {
			getLabel: () => this.label,
			getMessages: () => this.getMessages(),
			getUiNavPoint: () => {
				return this.navPoint.toUiNavPoint(this.injector, uiZone.layer);
			},
			isLytebox: () => this.lytebox
		};
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

	getLabel(): string {
		return this.label;
	}

	copy(): SiField {
		return new LinkOutSiField(this.navPoint, this.label, this.injector);
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.navPoint ? this.navPoint.copy() : null);
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.navPoint = null;
		} else {
			this.navPoint = genericValue.readInstance(SiNavPoint).copy();
		}

		return Promise.resolve();
	}
}

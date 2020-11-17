import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { OutSiFieldAdapter } from '../../common/model/out-si-field-adapter';
import { LinkOutModel } from '../comp/link-field-model';
import { LinkOutFieldComponent } from '../comp/link-out-field/link-out-field.component';
import { SiField } from '../../../si-field';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';

export class LinkOutSiField extends OutSiFieldAdapter implements LinkOutModel {

	constructor(private navPoint: UiNavPoint, private label: string, private siUiService: SiUiService) {
		super();
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		if (this.navPoint.callback) {
			throw new IllegalSiStateError('UiContent forLinkOutSiField already defined.');
		}

		return new TypeUiContent(LinkOutFieldComponent, (ref) => {
			ref.instance.model = this.createLinkOutModel(uiStructure.getZone());
		});
	}

	private createLinkOutModel(uiZone: UiZone): LinkOutModel {
		return {
			getLabel: () => this.label,
			getMessages: () => this.getMessages(),
			getUiNavPoint: () => {
				return {
					url: this.navPoint.url,
					siref: this.navPoint.siref,
					callback: () => {
						if (uiZone.layer.main || !this.navPoint.siref) {
							return true;
						}

						this.siUiService.navigateByUrl(this.navPoint.url, uiZone.layer);
						return false;
					}
				};
			}
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

	getUiNavPoint(): UiNavPoint {
		return this.navPoint;
	}

	getLabel(): string {
		return this.label;
	}

	copy(): SiField {
		return new LinkOutSiField(this.navPoint, this.label, this.siUiService);
	}

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented');
	}
}

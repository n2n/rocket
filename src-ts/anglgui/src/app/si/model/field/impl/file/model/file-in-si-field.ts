
import { SiField } from 'src/app/si/model/entity/si-field';
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { FileInFieldComponent } from 'src/app/ui/content/field/comp/file-in-field/file-in-field.component';
import { FileInFieldModel } from 'src/app/ui/content/field/file-in-field-model';
import { InSiFieldAdapter } from 'src/app/si/model/entity/impl/in-si-field-adapter';
import { SiUiService } from 'src/app/si/manage/si-ui.service';
import { UiContent } from 'src/app/si/model/structure/ui-content';
import { UiZone } from '../../../structure/ui-zone';
import { UiStructure } from 'src/app/si/model/structure/ui-structure';

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel, UiContent {

	public maxSize: number;
	public mandatory = false;
	public acceptedMimeTypes: string[] = [];
	public acceptedExtensions: string[] = [];

	constructor(public apiUrl: string, public apiCallId: object, public value: SiFile|null) {
		super();
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		return {
			valueId: (this.value ? this.value.id : null)
		};
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getApiCallId(): object {
		return this.apiCallId;
	}

	getSiFile(): SiFile|null {
		return this.value;
	}

	setSiFile(value: SiFile|null): void {
		this.value = value;
	}

	getAcceptedExtensions(): string[] {
		return this.acceptedExtensions;
	}

	getAcceptedMimeTypes(): string[] {
		return this.acceptedMimeTypes;
	}

	removeFile(): void {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	createContent(): UiContent|null {
		return this;
	}

	getMaxSize(): number {
		return this.maxSize;
	}

	initComponent(viewContainerRef: ViewContainerRef,
			componentFactoryResolver: ComponentFactoryResolver,
			siStructure: UiStructure): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FileInFieldComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		const component = componentRef.instance;
		component.model = this;
		component.siStructure = siStructure;

		return componentRef;
	}
}

export interface SiFile {
	id: object;
	name: string;
	url: string|null;
	thumbUrl: string|null;
	imageDimensions: SiImageDimension[];
}

export interface SiImageDimension {
	id: string;
	name: string;
	width: number;
	height: number;
}


import { SiField } from 'src/app/si/model/content/si-field';
import { ComponentRef, ComponentFactoryResolver, ViewContainerRef } from '@angular/core';
import { FileInFieldComponent } from 'src/app/ui/content/field/comp/file-in-field/file-in-field.component';
import { FileInFieldModel } from 'src/app/ui/content/field/file-in-field-model';
import { InSiFieldAdapter } from 'src/app/si/model/content/impl/in-si-field-adapter';
import { SiCommanderService } from 'src/app/si/model/si-commander.service';
import { SiContent } from 'src/app/si/model/structure/si-content';

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel, SiContent {

	private uploadedFile: File|null = null;
	public mandatory = false;
	public mimeTypes: string[] = [];
	public extensions: string[] = [];

	constructor(public apiUrl: string, public apiCallId: object, public value: SiFile|null) {
		super();
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		return {
			keep: !!this.value,
			file: this.uploadedFile
		};
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getApiCallId(): object {
		return this.apiCallId;
	}

	getValue(): SiFile {
		return this.value;
	}

	setSiFile(value: SiFile): void {
		this.value = value;
	}

	getMimeTypes(): string[] {
		throw new Error('Method not implemented.');
	}

	removeFile(): void {
		throw new Error('Method not implemented.');
	}


	getSiFile(): SiFile|null {
		throw new Error('Method not implemented.');
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	getContent(): SiContent|null {
		return this;
	}

	initComponent(viewContainerRef: ViewContainerRef,
			componentFactoryResolver: ComponentFactoryResolver,
			commanderService: SiCommanderService): ComponentRef<any> {
		const componentFactory = componentFactoryResolver.resolveComponentFactory(FileInFieldComponent);

		const componentRef = viewContainerRef.createComponent(componentFactory);

		const component = componentRef.instance;
		component.model = this;
		component.mandatory = this.mandatory;
		component.mimeTypes = this.mimeTypes;

		return componentRef;
	}
}

export interface SiFile {
	valid: boolean;
	name: string;
	url: string|null;
	thumbUrl: string|null;
}

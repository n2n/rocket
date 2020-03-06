
import { SiField } from 'src/app/si/model/content/si-field';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { FileInFieldModel } from '../comp/file-in-field-model';
import { FileInFieldComponent } from '../comp/file-in-field/file-in-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { Fresult } from 'src/app/util/err/fresult';

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel {

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

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(FileInFieldComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	getMaxSize(): number {
		return this.maxSize;
	}

	readGenericValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		throw new Error('Not yet implemented');
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

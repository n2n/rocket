
import { SiField } from 'src/app/si/model/content/si-field';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { FileInFieldModel } from '../comp/file-in-field-model';
import { FileInFieldComponent } from '../comp/file-in-field/file-in-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel {

	public maxSize: number;
	public mandatory = false;
	public acceptedMimeTypes: string[] = [];
	public acceptedExtensions: string[] = [];

	constructor(public apiFieldUrl: string, public apiCallId: object, public value: SiFile|null) {
		super();
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		let imageCuts: { [id: string]: SiImageCut };

		if (this.value && this.value.imageDimensions.length > 0) {
			imageCuts = {};
			for (const imgDim of this.value.imageDimensions) {
				imageCuts[imgDim.id] = imgDim.imageCut;
			}
		}

		return {
			valueId: (this.value ? this.value.id : null),
			imageCuts
		};
	}

	getApiFieldUrl(): string {
		return this.apiFieldUrl;
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

	// removeFile(): void {
	// 	throw new Error('Method not implemented.');
	// }

	// copy(): SiField {
	// 	throw new Error('Method not implemented.');
	// }

	createUiContent(uiStructure: UiStructure): UiContent {
		return new TypeUiContent(FileInFieldComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	getMaxSize(): number {
		return this.maxSize;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(this.value ? this.value.copy() : null);
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		if (genericValue.isNull()) {
			this.value = null;
			return;
		}

		this.value = genericValue.readInstance(SiFile).copy();
	}
}

export class SiFile {
	thumbUrl: string|null;
	mimeType: string|null;
	imageDimensions: SiImageDimension[] = [];

	constructor(public id: object, public name: string, public url: string|null) {
	}

	copy(): SiFile {
		const siFile = new SiFile(this.id, this.name, this.url);
		siFile.thumbUrl = this.thumbUrl;
		siFile.mimeType = this.mimeType;
		siFile.imageDimensions = this.imageDimensions.map(id => {
			return {
				id: id.id,
				name: id.name,
				width: id.width,
				height: id.height,
				imageCut: id.imageCut.copy(),
				ratioFixed: id.ratioFixed
			}
		});
		return siFile;
	}
}

export interface SiImageDimension {
	id: string;
	name: string;
	width: number;
	height: number;
	imageCut: SiImageCut;
	ratioFixed: boolean;
}

export class SiImageCut {
	constructor(public x: number, public y: number, public width: number, public height: number, public exists: boolean) {
	}

	copy(): SiImageCut {
		return new SiImageCut(this.x, this.y, this.width, this.height, this.exists);
	}

	equals(obj: any): boolean {
		return obj instanceof SiImageCut && obj.x === this.x && obj.y === this.y && obj.height === this.height
				&& obj.exists === this.exists;
	}
}

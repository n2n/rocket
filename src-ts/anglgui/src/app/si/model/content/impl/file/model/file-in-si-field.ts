import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { FileInFieldModel } from '../comp/file-in-field-model';
import { FileInFieldComponent } from '../comp/file-in-field/file-in-field.component';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiFile, SiImageCut } from './file';
import { SiInputResetPoint } from '../../../si-input-reset-point';
import { CallbackInputResetPoint } from '../../common/model/callback-si-input-reset-point';

export class FileInSiField extends InSiFieldAdapter implements FileInFieldModel {

	public maxSize!: number;
	public mandatory = false;
	public acceptedMimeTypes: string[] = [];
	public acceptedExtensions: string[] = [];

	constructor(public apiUrl: string, public maskId: string, public entryId: string|null, public fieldName: string,
			public value: SiFile|null) {
		super();
	}

	override hasInput(): boolean {
		return true;
	}

	readInput(): object {
		let imageCuts: { [id: string]: SiImageCut }|undefined = undefined;

		if (this.value && this.value.imageDimensions.length > 0) {
			imageCuts = {};
			for (const imgDim of this.value.imageDimensions) {
				imageCuts[imgDim.id] = imgDim.imageCut;
			}
		}

		return {
			valueId: (this.value ? this.value.id : null),
			imageCuts: imageCuts
		};
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getMaskId(): string {
		return this.maskId;
	}

	getEntryId(): string|null {
		return this.entryId;
	}

	getFieldName(): string {
		return this.fieldName;
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

	async copyValue(): Promise<SiGenericValue> {
		return new SiGenericValue(this.value?.copy() || null);
	}

	async pasteValue(genericValue: SiGenericValue): Promise<boolean> {
		if (!genericValue.isInstanceOf(SiFile) && !genericValue.isNull()) {
			return false;
		}

		if (genericValue.isNull()) {
			this.value = null;
			return true;
		}

		this.value = genericValue.readInstance(SiFile).copy();
		return true;
	}

	async createInputResetPoint(): Promise<SiInputResetPoint> {
		return new CallbackInputResetPoint(this.value?.copy(), (value) => {
			this.value = value?.copy() || null;
		});
	}
}

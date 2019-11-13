import { SiComp } from '../model/comp/si-comp';
import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiControl } from '../model/control/si-control';
import { ApiCallSiControl } from '../model/control/impl/model/api-call-si-control';
import { RefSiControl } from '../model/control/impl/model/ref-si-control';
import { SiButton, SiConfirm } from '../model/control/impl/model/si-button';

enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call'
}

export class SiCompEssentialsFactory {

	constructor(private comp: SiComp) {
	}


	createControls(dataArr: any[]): SiControl[] {
		const controls = new Array<SiControl>();

		for (const controlData of dataArr) {
			controls.push(this.createControl(controlData));
		}
		return controls;
	}

	createControl(data: any): SiControl {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');

		switch (extr.reqString('type')) {
			case SiControlType.REF:
				return new RefSiControl(
						dataExtr.reqString('url'),
						this.createButton(dataExtr.reqObject('button')));
			case SiControlType.API_CALL:
				const apiControl = new ApiCallSiControl(
						dataExtr.reqString('apiUrl'),
						dataExtr.reqObject('apiCallId'),
						this.createButton(dataExtr.reqObject('button')),
						this.comp);
				apiControl.inputSent = dataExtr.reqBoolean('inputHandled');
				return apiControl;
			default:
				throw new ObjectMissmatchError('Invalid si field type: ' + data.type);
		}
	}

	private createButton(data: any): SiButton {
		const extr = new Extractor(data);
		const btn = new SiButton(extr.reqString('name'), extr.reqString('btnClass'), extr.reqString('iconClass'));

		btn.tooltip = extr.nullaString('tooltip');
		btn.important = extr.reqBoolean('important');
		btn.iconImportant = extr.reqBoolean('iconImportant');
		btn.labelImportant = extr.reqBoolean('labelImportant');

		const confirmData = extr.nullaObject('confirm');
		if (confirmData) {
			btn.confirm = this.createConfirm(confirmData);
		}
		return btn;
	}

	private createConfirm(data: any): SiConfirm {
		const extr = new Extractor(data);

		return {
			message: extr.nullaString('message'),
			okLabel: extr.nullaString('okLabel'),
			cancelLabel: extr.nullaString('cancelLabel')
		};
	}
}
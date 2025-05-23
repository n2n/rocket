import { Extractor, ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { SiControl } from '../model/control/si-control';
import { ApiCallSiControl } from '../model/control/impl/model/api-call-si-control';
import { RefSiControl } from '../model/control/impl/model/ref-si-control';
import { SiButton, SiConfirm } from '../model/control/impl/model/si-button';
import { Injector } from '@angular/core';
import { SiControlBoundary } from '../model/control/si-control-boundary';
import { GroupSiControl } from '../model/control/impl/model/group-si-control';
import { SimpleSiControl } from '../model/control/impl/model/simple-si-control';
import { SiUiService } from '../manage/si-ui.service';

enum SiControlType {
	REF = 'ref',
	API_CALL = 'api-call',
	GROUP = 'group',
	DEACTIVATED = 'deactivated'
}

export class SiControlFactory {

	constructor(private controlBoundary: SiControlBoundary, private injector: Injector) {
	}

	createControls(maskId: string|null, entryId: string|null, dataArr: Map<string, any>): SiControl[] {
		const controls = new Array<SiControl>();
		for (const [name, controlData] of dataArr) {
			controls.push(this.createControl(maskId, entryId, name, controlData));
		}
		return controls;
	}

	createControl(maskId: string|null, entryId: string|null, controlName: string, data: any): SiControl {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');

		switch (extr.reqString('type')) {
			case SiControlType.REF:
				return new RefSiControl(
						this.injector.get(SiUiService),
						dataExtr.reqString('url'),
						dataExtr.reqBoolean('newWindow'),
						this.createButton(dataExtr.reqObject('button')),
						this.controlBoundary);
			case SiControlType.API_CALL:
				const apiControl = new ApiCallSiControl(
						this.injector.get(SiUiService),
						maskId, entryId, controlName,
						this.createButton(dataExtr.reqObject('button')),
						this.controlBoundary);
				apiControl.inputSent = dataExtr.reqBoolean('inputHandled');
				return apiControl;
			case SiControlType.GROUP:
				return new GroupSiControl(
						this.createButton(dataExtr.reqObject('button')),
						this.createControls(maskId, entryId, dataExtr.reqMap('controls')));
			case SiControlType.DEACTIVATED:
				const deactivatedControl = new SimpleSiControl(this.createButton(dataExtr.reqObject('button')), () => {});
				deactivatedControl.disabled = true;
				return deactivatedControl;
			default:
				throw new ObjectMissmatchError('Invalid si control type: ' + data.type);
		}
	}

	private createButton(data: any): SiButton {
		const extr = new Extractor(data);
		const btn = new SiButton(extr.reqString('name'), extr.reqString('btnClass'), extr.reqString('iconClass'));

		btn.tooltip = extr.nullaString('tooltip');
		btn.important = extr.reqBoolean('important');
		btn.iconImportant = extr.reqBoolean('iconImportant');
		btn.iconAlways = extr.reqBoolean('iconAlways');
		btn.labelAlways = extr.reqBoolean('labelAlways');
		btn.href = extr.nullaString('href');

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
			cancelLabel: extr.nullaString('cancelLabel'),
			danger: extr.reqBoolean('danger')
		};
	}
}

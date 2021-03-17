import {Component, OnInit, Input, HostBinding, Inject} from '@angular/core';
import { ButtonControlModel } from '../button-control-model';
import { SiButton } from '../../model/si-button';
import { UiZone } from 'src/app/ui/structure/model/ui-zone';
import { filter } from 'rxjs/operators';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import {PlatformService} from '../../../../../../util/nav/platform.service';
import {IllegalStateError} from '../../../../../../util/err/illegal-state-error';

@Component({
	selector: 'rocket-button-control',
	templateUrl: './button-control.component.html',
	styleUrls: ['./button-control.component.css'],
	host: {class: 'rocket-button-control'},
  providers: [PlatformService]
})
export class ButtonControlComponent implements OnInit {

	@Input()
	model: ButtonControlModel;

	private _subVisible = false;

	constructor(public platformService: PlatformService) {
	}

	ngOnInit() {
	}

	@HostBinding('class.rocket-fixed-width')
	get fixedWidth(): boolean {
		return !!this.model.getSubTooltip && !!this.model.getSubTooltip();
	}

	get siButton(): SiButton {
		return this.model.getSiButton();
	}

	get loading(): boolean {
		return this.model.isLoading();
	}

	get disabled() {
		return this.model.isDisabled() || this.loading;
	}

  get href(): string|undefined {
	  if (this.model.getAconfig && !this.model.getAconfig()?.routerLinked) {
	    return this.model.getAconfig().url;
    }
	  return undefined;
  }

  get routerLink(): string|undefined {
    if (this.model.getAconfig && this.model.getAconfig()?.routerLinked) {
      return '/' + this.platformService.routerUrl(this.model.getAconfig().url);
    }
    return undefined;
  }

  get rel(): string|undefined {
    if (this.model.getAconfig && !this.model.getAconfig()?.newWindow) {
      return 'noopener';
    }
    return undefined;
  }

  get target(): string|undefined {
    if (this.model.getAconfig && !this.model.getAconfig()?.newWindow) {
      return '_blank';
    }
    return undefined;
  }

	hasSubUiContents(): boolean {
		return !!this.model.getSubUiContents && this.model.getSubUiContents().length > 0;
	}

	hasSubSiButtons() {
		return !!this.model.getSubSiButtonMap && this.model.getSubSiButtonMap().size > 0;
	}

	get subUiContents(): UiContent[] {
		return this.model.getSubUiContents ? this.model.getSubUiContents() : [];
	}

	get subSiButtonMap() {
		return this.model.getSubSiButtonMap ? this.model.getSubSiButtonMap() : [];
	}

	get subVisible(): boolean {
		return this._subVisible && !this.disabled && (this.hasSubSiButtons() || this.hasSubUiContents());
	}

  exec(event: MouseEvent) {
    if (this.hasSubSiButtons() || this.hasSubUiContents()) {
			this._subVisible = !this._subVisible;
			return;
		}

		const siConfirm = this.model.getSiButton().confirm;

		if (!siConfirm) {
      this.execStopPropagationOnTrue(event);
			return;
		}

		const cd = this.model.getUiZone().createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel);
		cd.danger = siConfirm.danger;
		cd.confirmed$.pipe(filter(confirmed => confirmed)).subscribe(() => {
        this.execStopPropagationOnTrue(event);
      });
	}

	subExec(key: string) {
		this._subVisible = false;

		const siConfirm = this.model.getSubSiButtonMap().get(key).confirm;

		if (!siConfirm) {
			this.model.exec(key);
			return;
		}

		const cd = this.model.getUiZone().createConfirmDialog(siConfirm.message, siConfirm.okLabel, siConfirm.cancelLabel);
		cd.danger = siConfirm.danger;
    cd.confirmed$.pipe(filter(confirmed => confirmed)).subscribe(() => {
      this.model.exec(key);
    });
	}

  /**
   * when this.model returns true, event propagation/default omitted
   */
	private execStopPropagationOnTrue(event: MouseEvent) {
    if (this.model.exec(null)) {
      event.preventDefault();
    }
  }
}

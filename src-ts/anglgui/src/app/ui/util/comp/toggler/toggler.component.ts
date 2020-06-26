import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';

@Component({
	selector: 'rocket-ui-toggler',
	templateUrl: './toggler.component.html',
	styleUrls: ['./toggler.component.css']
})
export class TogglerComponent implements OnInit {

	private _enabled = false;

	@Input() labeled = true;
	@Output() enabledChange = new EventEmitter<boolean>();

	@Input() enabledIconClass = 'fa fa-toggle-on';
	@Input() disabledIconClass = 'fa fa-toggle-off';

	@Input() enabledTextCode: string|null = 'enabled_txt';
	@Input() disabledTextCode: string|null = 'disabled_txt';

	ngOnInit(): void {
	}

	@Input()
	set enabled(enabled: boolean) {
		if (enabled === this._enabled) {
			return;
		}

		this._enabled = enabled;
		this.enabledChange.emit(enabled);
	}

	get enabled(): boolean {
		return this._enabled;
	}

	toggle() {
		this.enabled = !this._enabled;
	}

}

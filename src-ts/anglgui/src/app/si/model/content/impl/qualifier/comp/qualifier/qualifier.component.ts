import { Component, OnInit, Input } from '@angular/core';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiEntryQualifier } from '../../../../si-entry-qualifier';

@Component({
	selector: '[rocketSiQualifier]',
	templateUrl: './qualifier.component.html',
	styleUrls: ['./qualifier.component.css']
})
export class QualifierComponent implements OnInit {

	@Input()
	siEntryQualifier: SiEntryQualifier|null = null;
	@Input()
	siMaskQualifier: SiMaskQualifier|null = null;
	@Input()
	showIcon = true;
	@Input()
	showName = true;
	@Input()
	iconImportant = false;

	constructor() { }

	ngOnInit() {
	}

	get iconClass(): string|null {
		if (!this.showIcon) {
			return null;
		}

		if (this.siMaskQualifier) {
			return this.siMaskQualifier.iconClass + (this.iconImportant ? ' rocket-important' : '');
		}

		return null;
	}

	get name(): string|null {
		if (!this.showName) {
			return null;
		}

		if (this.siEntryQualifier && this.siEntryQualifier.idName) {
			return this.siEntryQualifier.idName;
		}

		if (this.siMaskQualifier) {
			return this.siMaskQualifier.name;
		}

		return null;
	}

}

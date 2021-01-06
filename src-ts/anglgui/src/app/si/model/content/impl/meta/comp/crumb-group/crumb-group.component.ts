import { Component, OnInit, Input } from '@angular/core';
import { SiCrumbGroup, SiCrumb } from '../../model/si-crumb';

@Component({
	selector: 'rocket-crumb-group',
	templateUrl: './crumb-group.component.html',
	styleUrls: ['./crumb-group.component.css']
})
export class CrumbGroupComponent implements OnInit {

	@Input()
	siCrumbGroup: SiCrumbGroup;

	constructor() { }

	ngOnInit() {
	}

	isIcon(siCrumb: SiCrumb) {
		return siCrumb.type === SiCrumb.Type.ICON;
	}

	isLabel(siCrumb: SiCrumb) {
		return siCrumb.type === SiCrumb.Type.LABEL;
	}

	isInactive(siCrumb: SiCrumb) {
		return siCrumb.severity === SiCrumb.Severity.INACTIVE;
	}
}

import { Component, OnInit, Input } from '@angular/core';
import { SiCrumbGroup, SiCrumb } from 'src/app/si/model/entity/impl/meta/si-crumb';

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
}

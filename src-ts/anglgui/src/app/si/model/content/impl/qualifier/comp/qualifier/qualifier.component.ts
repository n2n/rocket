import { Component, OnInit, Input } from '@angular/core';
import { SiEntryQualifier } from "src/app/si/model/content/si-qualifier";

@Component({
	selector: 'rocket-ui-qualifier',
	templateUrl: './qualifier.component.html',
	styleUrls: ['./qualifier.component.css']
})
export class QualifierComponent implements OnInit {

	@Input()
	siQualifier: SiEntryQualifier;
	@Input()
	showIcon = true;
	@Input()
	showName = true;
	@Input()
	showIdName = true;

	constructor() { }

	ngOnInit() {
	}

}

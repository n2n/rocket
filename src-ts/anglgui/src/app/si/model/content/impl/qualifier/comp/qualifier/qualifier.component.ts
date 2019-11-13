import { Component, OnInit, Input } from '@angular/core';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { SiEntryQualifier } from '../../../../si-qualifier';

@Component({
	selector: 'rocket-si-qualifier',
	templateUrl: './qualifier.component.html',
	styleUrls: ['./qualifier.component.css']
})
export class QualifierComponent implements OnInit {

	@Input()
	siEntryQualifier: SiEntryQualifier|null = null;
	@Input()
	siTypeQualifier: SiTypeQualifier|null = null;
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
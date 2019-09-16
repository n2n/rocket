import { Component, OnInit, Input } from '@angular/core';
import { SiQualifier } from "src/app/si/model/entity/si-qualifier";

@Component({
  selector: 'rocket-ui-qualifier',
  templateUrl: './qualifier.component.html',
  styleUrls: ['./qualifier.component.css']
})
export class QualifierComponent implements OnInit {

	@Input()
	siQualifier: SiQualifier;
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
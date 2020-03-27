import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { SiTypeQualifier } from 'src/app/si/model/meta/si-type-qualifier';
import { SiEntryQualifier } from '../../../../si-qualifier';

@Component({
	selector: 'rocket-qualifier-tiling',
	templateUrl: './qualifier-tiling.component.html',
	styleUrls: ['./qualifier-tiling.component.css']
})
export class QualifierTilingComponent implements OnInit {

	@Input()
	siTypeQualifiers: Array<SiTypeQualifier> = [];
	@Input()
	siEntryQualifiers: Array<SiEntryQualifier> = [];
	@Input()
	disabled = false;
	@Output()
	sTypeSelected: EventEmitter<SiTypeQualifier>;
	@Output()
	sEntrySelected: EventEmitter<SiEntryQualifier>;

	constructor() { }

	ngOnInit() {
	}

	chooseSiType(siTypeQualifier: SiTypeQualifier) {
		this.sTypeSelected.emit(siTypeQualifier);
	}

	chooseSiEntry(siEntryQualifier: SiEntryQualifier) {
		this.sEntrySelected.emit(siEntryQualifier);
	}
}

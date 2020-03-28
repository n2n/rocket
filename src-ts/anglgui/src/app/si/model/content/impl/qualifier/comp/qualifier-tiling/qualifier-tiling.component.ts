import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { SiMaskQualifier } from 'src/app/si/model/meta/si-mask-qualifier';
import { SiEntryQualifier } from '../../../../si-qualifier';

@Component({
	selector: 'rocket-qualifier-tiling',
	templateUrl: './qualifier-tiling.component.html',
	styleUrls: ['./qualifier-tiling.component.css']
})
export class QualifierTilingComponent implements OnInit {

	@Input()
	siMaskQualifiers: Array<SiMaskQualifier> = [];
	@Input()
	siEntryQualifiers: Array<SiEntryQualifier> = [];
	@Input()
	disabled = false;
	@Output()
	sTypeSelected = new EventEmitter<SiMaskQualifier>();
	@Output()
	sEntrySelected = new EventEmitter<SiEntryQualifier>();

	constructor() { }

	ngOnInit() {
	}

	chooseSiType(siMaskQualifier: SiMaskQualifier) {
		this.sTypeSelected.emit(siMaskQualifier);
	}

	chooseSiEntry(siEntryQualifier: SiEntryQualifier) {
		this.sEntrySelected.emit(siEntryQualifier);
	}
}

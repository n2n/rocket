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
	siTypeSelected = new EventEmitter<SiMaskQualifier>();
	@Output()
	siEntrySelected = new EventEmitter<SiEntryQualifier>();

	constructor() { }

	ngOnInit() {
	}

	chooseSiType(siMaskQualifier: SiMaskQualifier) {
		this.siTypeSelected.emit(siMaskQualifier);
	}

	chooseSiEntry(siEntryQualifier: SiEntryQualifier) {
		this.siEntrySelected.emit(siEntryQualifier);
	}
}

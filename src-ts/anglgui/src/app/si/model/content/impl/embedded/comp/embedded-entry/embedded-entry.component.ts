import { Component, Input } from '@angular/core';
import { Embe } from '../../model/embe';

@Component({
	selector: 'rocket-embedded-entry',
	templateUrl: './embedded-entry.component.html',
	styleUrls: ['./embedded-entry.component.css']
})
export class EmbeddedEntryComponent /*implements OnInit */{

	@Input()
	embe: Embe;

	// @Input()
	// copyPool: CopyPool;

	// private typeSelected: boolean|null = null;
	// uiStructure: UiStructure;

	// copy = new EventEmitter<void>();
	// delete = new EventEmitter<void>();

	// constructor(private translationService: TranslationService) { }

	// ngOnInit() {
	// 	this.copyControl = new SimpleSiControl(
	// 				new SiButton(this.translationService.translate('common_copy_label'), 'btn btn-success rocket-important', 'fa fa-copy'),
	// 				() => {
	// 					this.copy.emit();
	// 				});
	// 	this.delteControl = new SimpleSiControl(
	// 			new SiButton(this.translationService.translate('common_delete_label'), 'btn btn-danger', 'fa fa-trash'),
	// 			() => {
	// 				this.delete.emit();
	// 			})
	// }

	// ngDoCheck() {
	// 	if (this.typeSelected === this.embe.isTypeSelected()) {
	// 		return;
	// 	}

	// 	if (!this.embe.isTypeSelected()) {
	// 		this.embe.uiStructure.createToolbarChild()
	// 	}
	// }

	// private createSiControls(comp: Embe) {
	// 	return [
	// 	];
	// }

}

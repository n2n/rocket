
import { Component, OnInit } from '@angular/core';
// import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import * as Editor from '../../../../../../../../../../ckeditor5/build/ckeditor';
import { CkeInModel } from '../cke-in-model';
import { CKEditor5, ChangeEvent } from '@ckeditor/ckeditor5-angular';
import { CkeMode } from '../../model/cke-in-si-field';

@Component({
	selector: 'rocket-cke-in-field',
	templateUrl: './cke-in-field.component.html',
	styleUrls: ['./cke-in-field.component.css']
})
export class CkeInFieldComponent implements OnInit {
	public Editor = Editor;

	model: CkeInModel;

	private simpleItems: string[] = [
		'undo',
		'redo',
		'|',
		'bold',
		'italic',
		'underline',
		'strikethrough',
		'subscript',
		'superscript',
		'removeFormat'
	];

	private normalItems: string[] = [
		'heading',
		'|',
		'undo',
		'redo',
		'|',
		'bold',
		'italic',
		'underline',
		'strikethrough',
		'subscript',
		'superscript',
		'removeFormat',
		'|',
		'bulletedList',
		'numberedList',
		'indent',
		'outdent',
		'|',
		'alignment',
		'|',
		'link',
		'blockQuote',
		'insertTable',
		'horizontalLine',
		'mediaEmbed',
		'codeBlock',
		'code',
		'|',
		'specialCharacters'
	];

	constructor() {

	}

	ngOnInit() {

	}

	get config(): CKEditor5.Config {
		const items = (CkeMode.SIMPLE !== this.model.getCkeMode() ? this.simpleItems : this.normalItems);

		return {
			toolbar: {
				items
			},
			language: 'en',
			table: {
				contentToolbar: [
					'tableColumn',
					'tableRow',
					'mergeTableCells',
					'tableCellProperties',
					'tableProperties'
				]
			},
			heading: {
				options: [
					{ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
					{ model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
					{ model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
					{ model: 'wut', view: { name: 'span', classes: [ 'huii',  'holeradio2' ] }, title: 'Tit', class: 'huii' }
				]
			}
		};
	}

	public onChange( { editor }: ChangeEvent ) {
		this.model.setValue(editor.getData());
	}

}

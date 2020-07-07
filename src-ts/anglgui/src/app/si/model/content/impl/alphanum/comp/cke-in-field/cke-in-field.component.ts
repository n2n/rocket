import { Component, OnInit } from '@angular/core';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { CkeInModel } from '../cke-in-model';
import { ChangeEvent, CKEditor5 } from '@ckeditor/ckeditor5-angular';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline';
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import Code from '@ckeditor/ckeditor5-basic-styles/src/code';
import Subscript from '@ckeditor/ckeditor5-basic-styles/src/subscript';
import Superscript from '@ckeditor/ckeditor5-basic-styles/src/superscript';

@Component({
	selector: 'rocket-cke-in-field',
	templateUrl: './cke-in-field.component.html',
	styleUrls: ['./cke-in-field.component.css']
})
export class CkeInFieldComponent implements OnInit {
	public Editor = ClassicEditor;

	model: CkeInModel;

	constructor() {

	}

	ngOnInit() {

	}

	get config(): CKEditor5.Config {
		return {
			toolbar: [
				'heading', '|', 'undo', 'redo' , '|',
				'bold', 'italic', 'underline', 'strikethrough', 'code', 'subscript', 'superscript', '|',
				'outdent', 'indent', 'bulletedList', 'numberedList', '|',
				'link', 'blockQuote'
			],
			heading: {
				options: [
					{ model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
					{ model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
					{ model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
				]
			}
		};
	}

	public onChange( { editor }: ChangeEvent ) {
		this.model.setValue(editor.getData());
	}

}

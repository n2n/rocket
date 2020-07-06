import { Component, OnInit } from '@angular/core';
import * as ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { CkeInModel } from '../cke-in-model';

@Component({
    selector: 'rocket-cke-in-field',
    templateUrl: './cke-in-field.component.html',
    styleUrls: ['./cke-in-field.component.css']
})
export class CkeInFieldComponent implements OnInit {
    public Editor = ClassicEditor;

    model: CkeInModel;

    constructor() { }

    ngOnInit() {
    }

}

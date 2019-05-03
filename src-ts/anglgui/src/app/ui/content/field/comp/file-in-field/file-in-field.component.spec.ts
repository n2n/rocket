import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FileInFieldComponent } from './file-in-field.component';

describe('FileInFieldComponent', () => {
  let component: FileInFieldComponent;
  let fixture: ComponentFixture<FileInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FileInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FileInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

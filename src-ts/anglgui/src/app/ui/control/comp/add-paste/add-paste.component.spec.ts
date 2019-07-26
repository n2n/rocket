import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddPasteComponent } from './add-paste.component';

describe('AddPasteComponent', () => {
  let component: AddPasteComponent;
  let fixture: ComponentFixture<AddPasteComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddPasteComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddPasteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

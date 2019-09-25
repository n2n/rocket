import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SelectInFieldComponent } from './select-in-field.component';

describe('SelectInFieldComponent', () => {
  let component: SelectInFieldComponent;
  let fixture: ComponentFixture<SelectInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ SelectInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelectInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

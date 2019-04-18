import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InputInFieldComponent } from './input-in-field.component';

describe('InputInFieldComponent', () => {
  let component: InputInFieldComponent;
  let fixture: ComponentFixture<InputInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InputInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InputInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

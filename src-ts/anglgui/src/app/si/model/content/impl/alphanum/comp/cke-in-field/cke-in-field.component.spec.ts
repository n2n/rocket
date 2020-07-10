import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CkeInFieldComponent } from './cke-in-field.component';

describe('CkeInFieldComponent', () => {
  let component: CkeInFieldComponent;
  let fixture: ComponentFixture<CkeInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CkeInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CkeInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

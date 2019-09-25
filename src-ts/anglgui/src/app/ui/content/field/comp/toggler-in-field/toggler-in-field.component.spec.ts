import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { TogglerInFieldComponent } from './toggler-in-field.component';

describe('TogglerInFieldComponent', () => {
  let component: TogglerInFieldComponent;
  let fixture: ComponentFixture<TogglerInFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ TogglerInFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TogglerInFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

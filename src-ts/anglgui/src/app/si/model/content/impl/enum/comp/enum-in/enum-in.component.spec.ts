import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EnumInComponent } from './enum-in.component';

describe('EnumInComponent', () => {
  let component: EnumInComponent;
  let fixture: ComponentFixture<EnumInComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EnumInComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EnumInComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FieldStructureComponent } from './field-structure.component';

describe('FieldStructureComponent', () => {
  let component: FieldStructureComponent;
  let fixture: ComponentFixture<FieldStructureComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FieldStructureComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FieldStructureComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

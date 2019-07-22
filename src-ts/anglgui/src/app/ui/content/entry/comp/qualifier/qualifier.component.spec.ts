import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { QualifierComponent } from './qualifier.component';

describe('QualifierComponent', () => {
  let component: QualifierComponent;
  let fixture: ComponentFixture<QualifierComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ QualifierComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(QualifierComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { QualifierTilingComponent } from './qualifier-tiling.component';

describe('QualifierTilingComponent', () => {
  let component: QualifierTilingComponent;
  let fixture: ComponentFixture<QualifierTilingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ QualifierTilingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(QualifierTilingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

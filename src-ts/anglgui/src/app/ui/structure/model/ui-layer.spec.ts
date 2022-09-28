import { UiContainer } from './ui-container';


describe('UiLayer', () => {
	let uiContainer: UiContainer;

	beforeEach(() => {
		uiContainer = new UiContainer(undefined as any);
	});

	it('containsRouteId', () => {
		const mainLayer = uiContainer.getMainLayer();
		mainLayer.pushRoute(2, 'holeradio');

		expect(mainLayer.containsRouteId(2, 'holeradio')).toBeTrue();
		expect(mainLayer.containsRouteId(2, '')).toBeFalse();
		expect(mainLayer.containsRouteId(3, 'holeradio')).toBeFalse();
		expect(mainLayer.containsRouteId(2, )).toBeTrue();
	});
});

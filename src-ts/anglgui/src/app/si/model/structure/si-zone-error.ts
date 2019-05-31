
export interface SiZoneError {
	
	getTitle(): string;
	
	getMessages(): string[];
	
	setHighlighted(highlighted): void;
	
	focus(): void;
}
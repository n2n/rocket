
export class Message {
	public args: Map<string, string>|null = null;

	constructor(readonly content: string, readonly translated: boolean) {
	}

	static createText(text: string): Message {
		return new Message(text, true);
	}

	static createTexts(texts: string[]): Message[] {
		return texts.map(text => new Message(text, true));
	}

	static createCode(code: string, args: Map<string, string>|null = null): Message {
		const msg = new Message(code, false);
		msg.args = args;
		return msg;
	}

	static createCodes(codes: string[]): Message[] {
		return codes.map(code => new Message(code, false));
	}
}

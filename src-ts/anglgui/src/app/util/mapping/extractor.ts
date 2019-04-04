export class Extractor {
    private obj: object
    
    constructor(obj: any) {
        if (typeof obj === 'object') {
            this.obj = obj;
            return;
        }
        
        throw new ObjectMissmatchError('Object required. Given: ' + typeof obj);
    }
    
    reqString(propName: string, nullable: boolean = false): string|null {
        if (typeof this.obj[propName] === 'string' || (nullable && this.obj[propName] === null)) {
            return this.obj[propName];
        }
        
        throw new ObjectMissmatchError('Property ' + propName + ' must be of type string. Given: ' 
                + typeof this.obj[propName]);
    }
}

export class ObjectMissmatchError extends Error {
    constructor(m: string) {
        super(m);

        // Set the prototype explicitly.
        Object.setPrototypeOf(this, ObjectMissmatchError.prototype);
    }
}

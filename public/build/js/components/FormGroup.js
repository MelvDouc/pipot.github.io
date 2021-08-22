export default class FormGroup extends HTMLElement {
    constructor() {
        var _a, _b;
        super();
        this.labelText = this.getAttribute("label-text");
        this.removeAttribute("label-text");
        this.forNameId = this.getAttribute("for-name-id");
        this.removeAttribute("for-name-id");
        this.inputType = this.getAttribute("input-type");
        this.removeAttribute("input-type");
        this.maxLength = (_a = this.getAttribute("max-length")) !== null && _a !== void 0 ? _a : null;
        this.removeAttribute("max-length");
        this.isRequired = (_b = this.getAttribute("is-required")) !== null && _b !== void 0 ? _b : null;
        this.removeAttribute("is-required");
        this.innerHTML = this.createFormGroup();
        if (this.maxLength) {
            const input = this.querySelector("input");
            if (input)
                input.maxLength = +this.maxLength;
        }
    }
    createLabel() {
        return `<label for="${this.forNameId}">${this.labelText}</label>`;
    }
    createInput() {
        const mainAttributes = [
            `id="${this.forNameId}"`,
            `name="${this.forNameId}"`,
        ];
        if (this.isRequired)
            mainAttributes.push(`required`);
        if (this.inputType === "textarea")
            return `<textarea ${mainAttributes.join(" ")}"></textarea>`;
        return `<input type="${this.inputType}" ${mainAttributes.join(" ")} />`;
    }
    createFormGroup() {
        return this.createLabel() + this.createInput();
    }
}

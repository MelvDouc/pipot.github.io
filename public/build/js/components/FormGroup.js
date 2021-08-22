export default class FormGroup extends HTMLElement {
    constructor() {
        var _a;
        super();
        this.labelText = this.getAttribute("label-text");
        this.removeAttribute("label-text");
        this.forNameId = this.getAttribute("for-name-id");
        this.removeAttribute("for-name-id");
        this.inputType = this.getAttribute("input-type");
        this.removeAttribute("input-type");
        this.maxLength = (_a = this.getAttribute("max-length")) !== null && _a !== void 0 ? _a : null;
        this.removeAttribute("max-length");
        this.isRequired = Boolean(this.getAttribute("is-required"));
        this.removeAttribute("is-required");
        this.innerHTML = this.createFormGroup();
        if (this.maxLength) {
            const input = this.querySelector("input");
            if (input)
                input.maxLength = +this.maxLength;
        }
    }
    createFormGroup() {
        return `
      <label for="${this.forNameId}">${this.labelText}</label>
      <input id="${this.forNameId}" name="${this.forNameId}" type="${this.inputType}" required="${this.isRequired}" />
    `;
    }
}

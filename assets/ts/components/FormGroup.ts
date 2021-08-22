export default class FormGroup extends HTMLElement {
  labelText: string | null;
  forNameId: string | null;
  inputType: string | null;
  maxLength: string | null;
  isRequired: boolean;

  constructor() {
    super();
    this.labelText = this.getAttribute("label-text");
    this.removeAttribute("label-text");
    this.forNameId = this.getAttribute("for-name-id");
    this.removeAttribute("for-name-id");
    this.inputType = this.getAttribute("input-type");
    this.removeAttribute("input-type");
    this.maxLength = this.getAttribute("max-length") ?? null;
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
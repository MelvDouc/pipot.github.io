export default class FormGroup extends HTMLElement {
  labelText: string | null;
  forNameId: string | null;
  inputType: string | null;
  maxLength: string | null;
  isRequired: string | null;

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
    this.isRequired = this.getAttribute("is-required") ?? null;
    this.removeAttribute("is-required");
    this.innerHTML = this.createFormGroup();
    if (this.maxLength) {
      const input = this.querySelector("input");
      if (input)
        input.maxLength = +this.maxLength;
    }
  }

  createLabel(): string {
    return `<label for="${this.forNameId}">${this.labelText}</label>`;
  }

  createInput(): string {
    const mainAttributes: string[] = [
      `id="${this.forNameId}"`,
      `name="${this.forNameId}"`,
    ];
    if (this.isRequired)
      mainAttributes.push(`required`);

    if (this.inputType === "textarea")
      return `<textarea ${mainAttributes.join(" ")}"></textarea>`;
    return `<input type="${this.inputType}" ${mainAttributes.join(" ")} />`;
  }

  createFormGroup(): string {
    return this.createLabel() + this.createInput();
  }
}
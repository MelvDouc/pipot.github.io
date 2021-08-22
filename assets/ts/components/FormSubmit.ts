export default class FormSubmit extends HTMLElement {
  text: string;
  button: HTMLButtonElement;

  constructor() {
    super();
    this.text = this.getAttribute("text") ?? "Valider";
    this.removeAttribute("text");
    this.button = document.createElement("button");
    this.button.textContent = this.text;
    this.button.type = "submit";
    this.button.classList.add("button");
    this.append(this.button);
  }
}
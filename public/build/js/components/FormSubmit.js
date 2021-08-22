export default class FormSubmit extends HTMLElement {
    constructor() {
        var _a;
        super();
        this.text = (_a = this.getAttribute("text")) !== null && _a !== void 0 ? _a : "Valider";
        this.removeAttribute("text");
        this.button = document.createElement("button");
        this.button.textContent = this.text;
        this.button.type = "submit";
        this.button.classList.add("button");
        this.append(this.button);
    }
}

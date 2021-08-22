import ProductCard from "./components/ProductCard.js";
import FormGroup from "./components/FormGroup.js";
import { getHeaderHeight } from "./functions.js";
import FormSubmit from "./components/FormSubmit.js";
window.customElements.define("product-card", ProductCard);
window.customElements.define("form-group", FormGroup);
window.customElements.define("form-submit", FormSubmit);
getHeaderHeight();

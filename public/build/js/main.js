import ProductCard from "./components/ProductCard.js";
import FormGroup from "./components/FormGroup.js";
import { getHeaderHeight } from "./functions.js";
window.customElements.define("product-card", ProductCard);
window.customElements.define("form-group", FormGroup);
getHeaderHeight();

export default class ProductCard extends HTMLElement {
  image: string | null;
  product_name: string | null;
  product_profile: string | null;
  description: string | null;
  price: number;
  seller: string | null;
  seller_profile: string | null;
  created_at: string | null;

  constructor() {
    super();
    this.image = this.getAttribute("image");
    this.product_name = this.getAttribute("product-name");
    this.product_profile = this.getAttribute("product-profile");
    this.description = this.getAttribute("description");
    this.price = Number(this.getAttribute("price"));
    this.seller = this.getAttribute("seller");
    this.seller_profile = this.getAttribute("seller-profile");
    this.created_at = this.getAttribute("created-at");
    this.innerHTML = this.createCard();
  }

  createCard() {
    return `
    <div class="left">
      <img src="/img/products/${this.image}" alt="${this.product_name}">
    </div>
    <div class="right">
      <h3>${this.product_name}</h3>
      <div class="detail">
        <div class="row">
          <div>Description</div>
          <div>${this.description}</div>
        </div>
        <div class="row">
          <div>Prix</div>
          <div>${this.price} Pipot${this.price > 1 ? "s" : ""}</div>
        </div>
        <div class="row">
          <div>Vendu par</div>
          <div><a href="${this.seller_profile}">${this.seller}</a></div>
        </div>
        <div class="row">
          <div>Publié le</div>
          <div>${this.created_at}</div>
        </div>
      </div>
      <div class="link">
        <a class="button" href="${this.product_profile}">Voir la fiche produit</a>
      </div>         
    </div>`;
  }
}
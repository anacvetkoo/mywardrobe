console.log("APP.JSX LOADED");

const rootElement = document.getElementById("product-grid");
console.log("ROOT ELEMENT:", rootElement);

let productsData = null;
if (typeof products !== "undefined") {
    // index.php scenarij
    productsData = products;
} else if (window.products) {
    // kategorija.php scenarij
    productsData = window.products;
} else {
    console.warn("Products not available");
}

let colClass = "col-6 col-md-3 col-lg-2";
if (rootElement.dataset.layout === "wishlist") {
    colClass = "col-6 col-md-6 col-lg-6"; // wishlist na profilu
}

if (rootElement && Array.isArray(productsData)) {
    const root = ReactDOM.createRoot(rootElement);
    root.render(<ProductGrid
            products={productsData}
            colClass={colClass}
        />);
}
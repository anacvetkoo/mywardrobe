const ProductCard = ({ product, colClass }) => {

    const cenaText =
        product.cena === null || product.cena === ""
            ? "Po dogovoru"
            : `${parseFloat(product.cena).toFixed(2)} €`;

    return (
        <div className={colClass}>
            <a
                href={`/podstrani/produkt.php?id=${product.id_produkt}`}
                className="text-decoration-none text-dark"
            >
                <div className="card h-100 shadow-sm produkt-kartica">

                    <div className="position-relative slika-ovoj">
                        <img
                            src={`/${product.slika ?? "slike/default-product.png"}`}
                            className="card-img-top"
                            alt={product.naziv}
                        />

                        <button
                            className="btn btn-light position-absolute top-0 end-0 m-2 p-1 rounded-circle wishlist-btn"
                            type="button"
                        >
                            <i className="bi bi-heart"></i>
                        </button>
                    </div>

                    <div className="card-body text-center">
                        <h5 className="card-title" style={{ fontSize: "1rem" }}>
                            {product.naziv}
                        </h5>

                        <p className="cena mb-0 text-muted">
                            {cenaText}
                        </p>
                    </div>

                </div>
            </a>
        </div>
    );
};
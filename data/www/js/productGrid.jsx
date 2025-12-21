const ProductGrid = ({ products, colClass }) => {
    return (
        <div className="row g-4">
            {products.map(p => (
                <ProductCard
                    key={p.id_produkt}
                    product={p}
                    colClass={colClass}
                />
            ))}
        </div>
    );
};
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart Promo GraphQL Playground</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #111827; color: #f9fafb; }
        main { max-width: 1100px; margin: 0 auto; padding: 28px 20px; }
        h1 { margin: 0 0 18px; font-size: 28px; }
        .examples { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .example { border: 1px solid #374151; background: #1f2937; color: #f9fafb; }
        textarea, pre { width: 100%; box-sizing: border-box; border: 1px solid #374151; border-radius: 6px; background: #030712; color: #e5e7eb; font: 15px/1.5 Consolas, Monaco, monospace; }
        textarea { min-height: 260px; padding: 14px; resize: vertical; }
        button { border: 0; border-radius: 6px; background: #2563eb; color: white; padding: 10px 14px; font-weight: 700; cursor: pointer; }
        button:disabled { opacity: .65; cursor: wait; }
        #run { margin: 16px 0; padding-inline: 20px; }
        pre { min-height: 240px; overflow: auto; padding: 14px; white-space: pre-wrap; }
    </style>
</head>
<body>
<main>
    <h1>Cart Promo GraphQL Playground</h1>
    <div class="examples">
        <button class="example" data-query="hello">Hello</button>
        <button class="example" data-query="products">Products</button>
        <button class="example" data-query="createCart">Create Cart</button>
        <button class="example" data-query="cartDetail">Cart Detail</button>
        <button class="example" data-query="deleteCart">Delete Cart</button>
        <button class="example" data-query="promos">Promos</button>
        <button class="example" data-query="promoDetail">Promo Detail</button>
        <button class="example" data-query="applyPromo">Apply Promo</button>
    </div>
    <textarea id="query"></textarea>
    <button id="run">Run Query</button>
    <pre id="result">Result will appear here.</pre>
</main>
<script>
    const examples = {
        hello: `{\n  hello\n}`,
        products: `{\n  products {\n    status\n    message\n    data {\n      id\n      name\n      price\n    }\n  }\n}`,
        createCart: `mutation {\n  createCart(user_id: 1, product_id: 101, quantity: 2, price: 50000) {\n    status\n    message\n    data {\n      id\n      user_id\n      product_id\n      quantity\n      price\n      created_at\n    }\n  }\n}`,
        cartDetail: `{\n  cart(id: 1) {\n    status\n    message\n    data {\n      id\n      user_id\n      product_id\n      quantity\n      price\n    }\n  }\n}`,
        deleteCart: `mutation {\n  deleteCart(id: 1) {\n    status\n    message\n  }\n}`,
        promos: `{\n  promos {\n    status\n    message\n    data {\n      id\n      code\n      discount_percent\n      minimum_transaction\n      max_usage\n      used\n      expired_at\n    }\n  }\n}`,
        promoDetail: `{\n  promo(id: 1) {\n    status\n    message\n    data {\n      id\n      code\n      discount_percent\n      minimum_transaction\n      max_usage\n      used\n      expired_at\n    }\n  }\n}`,
        applyPromo: `mutation {\n  applyPromo(code: "PROMO10", total_price: 100000) {\n    status\n    message\n    promo_code\n    discount\n    final_total\n  }\n}`
    };

    const button = document.getElementById('run');
    const query = document.getElementById('query');
    const result = document.getElementById('result');

    query.value = examples.products;

    document.querySelectorAll('.example').forEach((example) => {
        example.addEventListener('click', () => {
            query.value = examples[example.dataset.query];
            result.textContent = 'Result will appear here.';
        });
    });

    button.addEventListener('click', async () => {
        button.disabled = true;
        result.textContent = 'Loading...';

        try {
            const response = await fetch('/graphql', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ query: query.value })
            });

            const data = await response.json();
            result.textContent = JSON.stringify(data, null, 2);
        } catch (error) {
            result.textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });
</script>
</body>
</html>
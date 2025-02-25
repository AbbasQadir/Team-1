<style>
body {
    background-color: #E0E1DD;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    color: #0D1B2A;
}

.container {
    background-color: inherit;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    flex-wrap: nowrap;
    justify-content: space-between;
    max-width: 1200px;
}

.order-summary {
    flex: 0.8;
    margin-right: 20px;
    padding: 20px;
    background-color: #E0E1DD;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.order-summary h2 {
    color: #1B263B;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.item-card {
    display: flex;
    background-color: #E0E1DD;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
}

.item-card img {
    width: 150px;
    height: auto;
    object-fit: cover;
}

.product-details {
    padding: 15px;
    flex: 1;
}

.product-details h3 {
    margin: 0;
    font-size: 18px;
}

.product-details p {
    margin: 8px 0;
    font-size: 16px;
}

.shipping-method,
.shipping-address,
.payment_card {
    flex: 2.5;
    background-color: #E0E1DD;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.shipping-method h3,
.shipping-address h2,
.payment_card h2 {
    background-color: #1B263B;
    color: #E0E1DD;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
}

fieldset {
    border: none;
    margin: 0;
    padding: 0;
}

label {
    font-size: 16px;
    margin-bottom: 5px;
    color: #0D1B2A;
    display: block;
}

input[type="text"],
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 16px;
}

.btn {
    background-color: #415A77;
    color: #E0E1DD;
    padding: 10px 20px;
    text-align: center;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    width: 100%;
}

.btn:hover {
    background-color: #778DA9;
    color: #0D1B2A;
    box-shadow: 0px 0px 9px 0px rgba(0,0,0,0.1);
}

.shipping-method div {
    margin-bottom: 10px;
}

.shipping-method input[type="radio"] {
    margin-right: 5px;
}

.shipping-method label {
    display: inline;
    vertical-align: middle;
}
</style>

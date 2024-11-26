USE mind_and_motion;
insert into users (username, first_name, last_name, number, email, password)
VALUES
('josh360', 'Josh', 'Moore', '07888888888', 'josh@gmail.com', 'password1' ),
('marie_345', 'Marie', 'Lepen', '07899999999', 'marie@gmail.com', 'password2'),
('test', 'ali', 'ali', '07891111111', 'ali@gmail.fr', 'password3');

insert into country (country_name)
VALUES
('France'),
('United Kingdom'),
('United States'),
('India');

insert into shipping_method (method_name, shipping_price)
VALUES
('fast delivery', 4.99),
('regular delivery', 1.99),
('international delivery', 9.99);

insert into order_status(status)
VALUES
('shipped'),
('delivered'),
('cancelled'),
('pending'),
('refunded');

insert into product_category (category_name)
VALUES
('self help books'),
('supplements'),
('gym wear'),
('gym equipment'),
('tech for wellness');

insert into address(postal_code, street, line_1, line_2, city, country_id, region)
VALUES
('B4 7UJ', 'aston street', 'mary sturge', 'flat 1', 'Birmingham', 2, 'west midland'),
('B14 AK7', 'elsewhere road', 'onyx', 'appartment 5', 'Birmingham', 2, 'west midland'),
('SW1A 1BB', 'High street', '136 high street', 'flat 4', 'London', 2, 'Great london'),
('57323', 'Rue de la republique', 'building 3', '475 rue de la republique', 'Paris', 1, 'Provence');

insert into users_address (address_id, user_id)
VALUES
(1, 1),
(2, 2),
(3, 3);

insert into payment_type(type)
VALUES
('paypal'),
('credit card'),
('bank transfer');

insert into payment_method(type_id,user_id)
VALUES
(1,1),
(2,2),
(3,3);


insert into cart (user_id)
VALUES
(1),
(2),
(3);

insert into product(product_name, product_discription, product_image, product_category_id)
VALUES
('book', 'book', 'book.jpg', 1),
('supplement', 'supplement', 'supplement.jpg', 2),
('gym wear', 'gym wear', 'gym wear.jpg',3);

insert into variation(variation_name, product_category_id)
VALUES
('size', 3),
('colour', 3),
('type', 2);

insert into variation_option(variation_value)
VALUES
('small'),
('large'),
('red'),
('bleu');

insert into product_item(product_id, price, quantity)
VALUES
(1, 29.99, 100),
(1, 18.99, 130),
(3, 13.99, 155),
(3, 32.99, 100);

insert into product_configuration(variation_option_id, product_item_id)
VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

insert into cart_item(product_item_id, quantity, cart_id)
VALUES
(1, 2, 1),
(3, 1, 1),
(4, 3, 2);

insert into orders(user_id, address_id, order_status_id, shipping_method_id, payment_method_id, order_price, order_date)
VALUES
(1, 1, 2, 1, 3, 69.95, '2024-11-12 10:00:00'),
(2, 2, 1, 2, 2, 29.95, '2024-11-12 11:24:00');

insert into order_prod( orders_id, product_item_id, quantity, orders_prod_price)
VALUES
(1, 1, 2, 59.98),
(1, 2, 1, 18.99),
(2, 3, 2, 27.98);

insert into users_review(user_id, order_prod_id, comment, rating)
VALUES
(1, 1, 'good product', 5),
(2, 2, null, 3);

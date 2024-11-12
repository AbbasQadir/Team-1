CREATE DATABASE mind_and_motion;

USE mind_and_motion;

-- Create tables
create table user (
    user_id int AUTO_INCREMENT primary key,
    number int,
    email varchar(222) not null
);

create table country(
    country_id int AUTO_INCREMENT primary key,
    country_name varchar(222)
);

create table address(
    address_id int AUTO_INCREMENT primary key,
    postal_code varchar(222) not null,
    street varchar(222),
    line_1 varchar(222),
    line_2 varchar(222),
    city varchar(222),
    country_id int, 
    region varchar(222),
    foreign key (country_id) references country(country_id)
);

create table user_address (
    user_address_id int AUTO_INCREMENT primary key,
    address_id int ,
    user_id int,
    foreign key (address_id) references address(address_id),
    foreign key (user_id) references user(user_id)
);

create table cart (
    cart_id int AUTO_INCREMENT primary key,
    user_id int,
    foreign key (user_id) references user(user_id)
);

create table product_category (
    product_category_id int AUTO_INCREMENT primary key,
    category_name varchar(222) not null
);

create table product(
    product_id int AUTO_INCREMENT primary key,
    product_name varchar(222) not null,
    product_discription varchar(222),
    product_image varchar(222),
    product_category_id int,
    foreign key (product_category_id) references product_category(product_category_id)
);

create table variation(
    variation_id int AUTO_INCREMENT primary key,
    variation_name varchar(222) not null,
    product_category_id int,
    foreign key (product_category_id ) references product_category(product_category_id)
);

create table variation_option(
    variation_option_id int AUTO_INCREMENT primary key,
    variation_value varchar(222)
);

create table product_item(
    product_item_id int AUTO_INCREMENT primary key,
    product_id int,
    price float not null,
    quantity int,
    foreign key (product_id) references product(product_id)
);

create table product_configuration(
    product_category_id int AUTO_INCREMENT primary key,
    variation_option_id int,
    product_item_id int,
    foreign key (product_item_id) references product_item(product_item_id),
    foreign key (variation_option_id) references variation_option(variation_option_id)
);

create table cart_item(
    cart_item_id int AUTO_INCREMENT primary key,
    product_item_id int,
    quantity int not null,
    cart_id int,
    foreign key (product_item_id) references product_item(product_item_id),
    foreign key (cart_id) references cart(cart_id)
);

create table payment_type(
    type_id int AUTO_INCREMENT primary key,
    type varchar(222)
);

create table payment_method(
    payment_method_id int AUTO_INCREMENT primary key,
    type_id int,
    user_id int,
    foreign key (type_id) references payment_type(type_id),
    foreign key (user_id) references user(user_id)
);

create table shipping_method(
    shipping_method_id int AUTO_INCREMENT primary key,
    method_name varchar(222),
    shipping_price float
);

create table order_status(
    order_status_id int AUTO_INCREMENT primary key,
    status varchar(222)
);

create table orders(
    orders_id int AUTO_INCREMENT primary key,
    user_id int,
    address_id int,
    order_status_id int,
    shipping_method_id int,
    payment_method_id int,
    order_price float,
    order_date datetime,
    foreign key (user_id) references user(user_id),
    foreign key (address_id) references address(address_id),
    foreign key (shipping_method_id) references shipping_method(shipping_method_id),
    foreign key (order_status_id) references order_status(order_status_id),
    foreign key (payment_method_id) references payment_method(payment_method_id)
);

create table order_prod(
    order_prod_id int AUTO_INCREMENT primary key,
    orders_id int,
    product_item_id int,
    quantity int,
    foreign key (orders_id) references orders(orders_id),
    foreign key (product_item_id) references product_item(product_item_id)
);


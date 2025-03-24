
# Team-1 E-Commerce Platform

This is a simple e-commerce website built by Team-1. The platform is designed to help users achieve their health and fitness goals, while also catering to their hobbies. It offers a variety of products and includes all core functionalities of a typical e-commerce site.

## 🛍️ Project Overview

The website serves as an online store where users can browse, search, and purchase products related to:

- Fitness equipment
- Gym wear
- Health and wellness
- Hobby-related books and items


The project is built using HTML, CSS, JavaScript (Frontend), PHP (Backend), and MySQL (Database), with version control via GitHub and team coordination through Trello.

Core Functionalities
User-Side Features
Login page:
•	Has link for registration or admin login
User Registration page and login page:
•	secure authentication with password hashing
•	Session management to persist login across pages
Profile Management:
•	View and update profile details
•	Change password with secure validation and database update
•	Link to order history with order status and product details
Previous orders:
•	User can view their orders and their status 
•	They can return items they have previously purchased if they scroll down the previous orders page
Product Browsing & Search:
•	Browse by categories with stock status indicators
•	Search bar with real-time filtering
Product pages include:
•	Multiple images (carousel)
•	Stock levels (In Stock / Low Stock / Out of Stock)
•	Product variations (size, colour)
•	Product review can be added once a user has purchased 
•	Can add products to cart

Shopping Basket:
•	Shows  the products that are in cart 
•	Allows product quantities increase
•	Shows total based on what was added
•	Allows checkout
Checkout:
•	Asks for address, shipping method, payment method and allows order review
•	Updates price based on shipping method selected
•	If country selected is not UK or United Kingdom asks the person, the select international delivery
•	Allows going back to cart to edit it
•	Asks for 16 digits a future date and 3 digits for payment method
•	Stores orders in database with randomized order ID
•	Validates order and asks user to leave a review
Web reviews:
•	Once order placed asks user to leave review
•	Has many parameters.
•	Reviews are displayed in a carousel in home page 
•	Star rating, live character counter, modals for feedback
Supplement Quiz:
•	Multi-step quiz for supplement recommendations
•	Quiz results fetch relevant products from the database
•	Users can add recommended products directly to basket
Responsive design with hamburger menu for mobile
Dark/Light mode toggle across the platform
Dynamic popups and form feedback messages

Admin-Side Features
Admin Login & Session Management
Admin-only access secured via session handling
Admin accounts managed separately in database
Dashboard & Account Management
-	Overview of site metrics (e.g., revenue, customer stats)
Admin settings page:
-	to create/edit/delete admin accounts
-	Role-based permissions
-	change passwords for admin accounts if super admin

add product:
-	can add product with 3 images and other data
edit product: 
-	allows to select what product you want to edit 
-	all details are editable as long as they are valid
Delete/Deactivate products:
-	Allows product removal for products with no orders
-	Can disactivate a product and it won’t be shown on the website but kept in website(ideal so that users can see the product in their previous orders if purchased in the past)
Product & Stock Management:
-	Displays stock levels with warnings
-	Can increase stock by adding the new stock purchased it will do the math for you
Order Management:
-	View, filter, and search orders by user/order ID
-	Update order status (e.g., Pending, Shipped, Delivered)
-	Delete orders if needed
-	Displays order details and shipping address 
-	Displays if an item was returned and why.
Customer Management:
-	View list of all registered users
-	Can add users
-	Can edit user details
-	Admin-side account support when users can't self-update
Support & Messaging
-	Admin-facing message & support management page



Tech Stack
Area	Technology
Frontend	HTML, CSS, JavaScript
Backend	PHP
Database	MySQL
Version Control	Git, GitHub
Project Management	Trello

Additional Notes:
Database designed for scalability, supporting product variations, reviews, stock levels, and user roles
Security-focused, with session protection, password hashing, and input validation
Collaborative workflow using GitHub for version control and Trello for task tracking and team coordination



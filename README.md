
# 🛒 Team-1 E-Commerce Platform

A simple e-commerce website built by **Team-1**. This platform helps users achieve their **health and fitness goals** while supporting their **hobbies**, offering a wide range of products with all core functionalities of a typical e-commerce site.

---

## 📦 Project Overview

The website serves as an online store where users can browse, search, and purchase products related to:

- Fitness equipment  
- Gym wear  
- Health and wellness  
- Hobby-related books and items  

**Tech Stack:**  
- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  
- **Version Control:** Git, GitHub  
- **Project Management:** Trello  

---

## 👥 Core Functionalities

### 🔐 User-Side Features

**Authentication:**
- Login page with links for registration and admin login  
- Secure registration and login with password hashing  
- Session management to persist login across pages  

**Profile Management:**
- View and update profile details  
- Change password with secure validation  
- Link to view order history  

**Previous Orders:**
- View past orders and their status  
- Option to return items directly from the orders page  

**Product Browsing & Search:**
- Category-based browsing with stock indicators  
- Real-time search bar filtering  
- Product pages include:  
  - Image carousel  
  - Stock status (In Stock / Low Stock / Out of Stock)  
  - Product variations (size, colour)  
  - Review option post-purchase  
  - Add to cart functionality  

**Shopping Basket:**
- Displays selected products and quantities  
- Auto-updated totals  
- Quantity adjustments  
- Checkout redirection  

**Checkout Process:**
- Enter address, select shipping and payment method  
- Auto-price update based on shipping  
- International delivery prompt if outside the UK  
- Payment field validation (card number, expiry, CVV)  
- Order stored in DB with randomized ID  
- Prompts user to leave a review  

**Reviews:**
- Prompted after purchase  
- Star ratings, character counter, modal feedback  
- Displayed on homepage carousel  

**Supplement Quiz:**
- Multi-step quiz for product recommendations  
- Fetches relevant products from DB  
- Add suggested items directly to cart  

**Design & UX:**
- Fully responsive with hamburger menu on mobile  
- Dark/Light mode toggle  
- Dynamic popups and form feedback  

---

### 🛠️ Admin-Side Features

**Admin Access:**
- Secure login with session management  
- Admin accounts stored separately  
- Role-based permissions  
- Super admin can manage other admin accounts  

**Dashboard & Settings:**
- Overview of site metrics (e.g. revenue, users)  
- Admin settings to:  
  - Add/edit/delete admin accounts  
  - Change admin passwords  

**Product Management:**
- Add new products (supports 3 images per product)  
- Edit any product details  
- Deactivate products (still viewable in user order history)  
- Delete products if they have no orders  

**Stock Management:**
- View and manage stock levels  
- Warning for low stock  
- Add new stock (auto-calculates updated quantity)  

**Order Management:**
- View/filter/search orders by user/order ID  
- Update status (Pending, Shipped, Delivered)  
- Delete orders  
- View shipping info and return reasons  

**Customer Management:**
- View all users  
- Add or edit user details  
- Admin-side support for account issues  

**Support & Messaging:**
- Admin-facing support and messaging interface  

---

## 💻 Tech Stack

| Area           | Technology         |
|----------------|--------------------|
| **Frontend**   | HTML, CSS, JavaScript |
| **Backend**    | PHP                |
| **Database**   | MySQL              |
| **Version Control** | Git, GitHub   |
| **Project Management** | Trello     |

---

## 📝 Additional Notes

- **Scalable database** supports product variations, reviews, stock levels, and user roles  
- **Security-focused** with session protection, password hashing, and input validation  
- **Collaborative development** using GitHub and Trello  

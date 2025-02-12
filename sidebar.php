<div id="sidebar-toggle">☰</div>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

<div class="sidebar" id="sidebar">
    <h2>Mind & Motion</h2>
    <ul>
        <li><a href="admin-dashboard.php">Dashboard</a></li>
        
        <div class="dropdown">
            <button onclick="toggleSubMenu(this)" class="drop-btn"> Product Management <span class="material-icons">keyboard_arrow_down</span></button>
            <ul class="sub-menu">
                	<li><a href="admin_dash.php">Add Product</a></li>
                	<li><a href="admin_dash1.php">Remove Product</a></li>
            </ul>

        </div>

        <li><a href="#">Stock Management</a></li>
        <li><a href="#">Order Management</a></li>
        <li><a href="customermanagement.php">Customer Management</a></li>
        <li><a href="#">Messages and Support</a></li>
        <li><a href="#">Settings</a></li>
    </ul>
    <div class="sidebar-bottom">
        <a href="index.php" class="sidebar-btn"> <span class="material-icons">home</span>Index</a>
            
        <a href="admin_log.php" class="sidebar-btn">
           	<span class="material-icons">logout</span> Logout</a>

    </div>
</div>

<script>
    function toggleSubMenu(button) {
		let subMenu = button.nextElementSibling;
		subMenu.classList.toggle("show");
		button.classList.toggle("rotate");
	}    
    	
    document.addEventListener("DOMContentLoaded", function(){
        const sidebar = document.getElementById("sidebar");
        const sidebarToggle = document.getElementById("sidebar-toggle");

        sidebarToggle.addEventListener("click", function(){
            if (sidebar.classList.contains("open")){
                sidebar.classList.remove("open");
                sidebarToggle.innerHTML = "☰";
                document.body.classList.remove("shifted");
                sidebarToggle.style.left = "10px";

            }
            else {
                sidebar.classList.add("open");
                sidebarToggle.innerHTML  = "✖";
                document.body.classList.add("shifted");
                sidebarToggle.style.left = "260px";
            }
        })
    });
        
</script>

<style>
    *{
        margin: 0;
        padding:0;
        box-sizing:  border-box;
        list-style:none;
        text-decoration:none;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
		font-size: 18px;
    }
    body{
        
        transition: margin-left 0.3s ease;
    }
    #sidebar-toggle {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 9999;
        background: #1B263B;
        color: white;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        font-size: 18px;
        border-radius: 5px;
        transition: left 0.3s ease;

    }

    .sidebar {
        position: fixed;
        left: -250px;
        top: 0;
        width: 250px;
        height: 100vh;
        background: #1B263B;
        padding-top: 20px;
        transition: left 0.3s ease;
        overflow-y: auto;
        z-index: 9998;

    }
    .sidebar.open {
        left:0;
    }
    body.shifted {
        margin-left: 250px;
    }
    
    .sidebar h2{
        color: #E0E1DD;
        text-transform: uppercase;
        text-align: center;
        margin-bottom: 30px;
    }
    .sidebar ul li{
        padding: 15px;
        color: #E0E1DD;
        cursor: pointer;
    }
	.sidebar ul li a{
        color: #E0E1DD;
        cursor: pointer;
    }
    .sidebar ul li:hover{
        background: #0D1B2A;
        color: #fff;
    	font-weight: bolder;

    }




    .drop-btn {
        display: flex;
    	align-items:center;
    	background:#1B263B;
    	width: 100%;
   		color: #E0E1DD;
        cursor: pointer;
    	border: none;
    	font:inherit;
    	text-align: left;
    	justify-content: space-between;
    	margin:0;
    	padding:15px;
    
    }
	.drop-btn:hover {
    	background:#0d1b2a;
    }

	.sub-menu{
        max-height:0;
    	overflow: hidden;
    	transition: max-height 300ms easse-in-out;
    	background: #1b263b;
    }
	.sub-menu.show {
    	max-height: 200px;
    }

	.sub-menu li{
    	padding: 10px 15px;
    	display:block;
    }
	.sub-menu li:hover {
        background: #0d1b2a;
        cursor: pointer;
    }
	.sub-menu > div {
    	overflow:hidden;
    }
    .sub-menu a{
    	padding-left: 0;
    }

	.rotate .material-icons {
    	transform: rotate(180deg);
    	transition: transform 200ms ease-in-out;
    }



    .sidebar-bottom {
    	position: absolute;
    	width:100%;
    	bottom:0;
        padding: 15px;
        background: #1B263B;
        display: flex;
        flex-direction: column;
    }
    .sidebar-btn {
        display:flex;
        align-items: center;
        padding: 10px;
        color: #E0E1DD;
        background: #415A77;
        border-radius: 5px;
        text-align: center;
        margin-bottom: 10px;
        font-size: 16px;
    	justify-content: space-evenly;

    }
    .sidebar-btn .icon {
        margin-right: 10px;
    }
    .sidebar-btn:hover {
        background: #778DA9;
    }
</style>

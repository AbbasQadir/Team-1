<div id="sidebar-toggle">☰</div>
<div class="sidebar" id="sidebar">
    <h2>Mind & Motion</h2>
    <ul>
        <li><a href="admin-dashboard.php">Dashboard</a></li>
        
        <li class="dropdown">
            <a href="#" class="drop-btn">Product Management ▼</a>
            <ul class="dropdown-content">
                <li><a href="admin_dash.php">Add Product</a></li>
                <li><a href="admin_dash1.php">Remove Product</a></li>

            </ul>

        </li>
        <li><a href="#"></a>Stock Management</li>
        <li><a href="customermanagement.php"></a>Customer Management</li>
        <li><a href="#"></a>Messages and Support</li>
        <li><a href="#"></a>Settings</li>
    </ul>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        const dropBtn = document.querySelector(".drop-btn");
        const dropContent = document.querySelector(".dropdown-content");

        dropBtn.addEventListener("click", function(event) {
            event.preventDefault();
            dropContent.classList.toggle("show");
        });
    
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
        font-family:'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
    }
    body{
        
        transition: margin-left 0.3s ease;
    }
    #sidebar-toggle {
        position: fixed;
        top: 10px;
        left: 10px;
        z-index: 9999;
        background: #0d1b2a;
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
        height: 100%;
        background: #0d1b2a;
        padding-top: 20px;
        transition: left 0.3s ease;
        overflow-y:auto;
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
        text-transform: upperfcase;
        text-align: center;
        margin-bottom: 30px;
    }
    .sidebar ul li{
        padding: 15px;
        color: #E0E1DD;
        cursor: pointer;
    }
	.sidebar ul li a{
        //padding: 15px;
        color: #E0E1DD;
        cursor: pointer;
    }
    .sidebar ul li:hover{
        background: #1B263B;
        color: #fff;

    }
    .dropdown .drop-btn {
        display: block;
        color: rgb(222, 219, 215);
        cursor: pointer;
    }
    .dropdown-content {
        display: none;
        font-weight: bold;
        padding-left: 0;
    }

    .dropdown-content li{
        padding: 0;
        border-bottom: none;
        border-top: none;
        font-size: 14px;
    }
    .dropdown-content li:hover {
        background: #446494;
        cursor: pointer;
    }

    .dropdown-content.show{
        display:block;
    }
</style>

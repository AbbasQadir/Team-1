<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php include_once("navbar.php"); ?>

    <h1 style="padding:50px 0px 0px 50px;">Books</h1>

    <a href="/product">
        <div class="item-container">
            <img src="book.jpg" class="itemImage" height="125px">
            <h2  class="itemTitle">title</h2>
            <p class="itemPrice">£5</p>
        </div>
    </a>
    <a href="/product">
        <div class="item-container">
            <img src="book.jpg" class="itemImage" height="125px">
            <h2  class="itemTitle">title</h2>
            <p class="itemPrice">£5</p>
        </div>
    </a>
   
    <a href="/product">
        <div class="item-container">
            <img src="book.jpg" class="itemImage" height="125px">
            <h2 class="itemTitle">title</h2>
            <p class="itemPrice">£5</p>
           
        </div>
    </a>

    <a href="/product">
        <div class="item-container">
            <img src="book.jpg" class="itemImage" height="125px">
            <h2  class="itemTitle">title</h2>
            <p class="itemPrice">£5</p>
        </div>
    </a>
   
    <a href="">
        <div class="item-container">
            <img src="book.jpg" class="itemImage" height="125px">
            <h2 class="itemTitle">title</h2>
            <p class="itemPrice">£5</p>
           
        </div>
    </a>

<style>

    body{
        padding: 0px 0 150px 0;
    }

    a{
        color: inherit;
    }

    a:hover{
        color: inherit;
    }

    a:visited{
        color: inherit;
    }

    .item-container{
        background-color: rgb(216, 216, 216);
        height: auto;
        width: 30%;
        margin: 25px 0 25px 25px;
        display: inline-block;
        padding: 20px;
        border-radius: 25px;
    }

    @media (max-width: 900px) {
        .item-container{
        background-color: rgb(216, 216, 216);
        height: auto;
        width: 40%;
        margin-top: 25px;
        margin-left: 5%;
        margin-right: 0px;
        margin-bottom: 0;
        display: inline-block;
        padding: 20px;
        border-radius: 25px;
        }
    }

 

    .item-container:hover{
        background-color: gray;
        cursor: pointer;
    }

    .itemTitle{
        border-radius: 25px;
        margin-top: 25px;
    }

    .itemPrice{
        border-radius: 25px;
        display: inline;
    }

    .itemImage{
        width: 90%;
        height: auto;
        border-radius: 25px;
        display: inline;
    }


</style>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php require_once("navbar.php") ?>

    <div id="mainInfoContainer"> 
        <img src="book.jpg" id="mainImage" width="250px">
        
        <h1 id="mainTitle">title</h1>
        <p id="mainAuthor">author </p>
        <h5 id="mainPrice">£5</h5>
        <button id="addToBasket">add to basket</button>
    </div>

    <div id="detailedInfoContainer">
        <h3 style="font-weight:Bold;" >Description</h3>
        <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Illum obcaecati praesentium cumque harum reiciendis similique maxime suscipit molestiae iure tempora, est ipsam repudiandae hic porro. Ipsum sit quos nobis reiciendis?</p>
        <p style="font-weight:Bold;">Catagory</p>
        <p>idk</p>
        <p style="font-weight:Bold;">ISBN</p>
        <p>545334</p>
    </div>

    <div id="similarProductsContainer">
        <h1 id="similarProductsHeader">Similar Products</h1>

        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>
        
        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>

        <a href="">
            <div class="similarProductItem">
                <img class="similarProductImg" src="book.jpg">
                <p class="similarProductTitle">Title</p>
                <p class="similarProductPrice">price</p>
            </div>
        </a>
        
   
    </div>
    

<style>

    body{
        margin-bottom: 550px;
    }

    a{
        color: inherit;
    }

    a:visited{
        color: inherit;
    }

    a:hover{
        color: inherit;

    }

    .similarProductItem:hover{
        background-color: gray;
    }


    #detailedInfoContainer{
        padding: 50px 25px 50px 25px;
    }

    #mainInfoContainer{
        background-color: rgb(216, 216, 216);
        height: 400px;

    }

    #mainImage{
        display: inline;
        border-radius: 15px;
        margin-left: 50px;
        margin-top: 50px;
        max-height: 300px

    }

    #mainTitle {
        display: inline;
        margin-left: 50px;
        position: relative;
        bottom: 45px;
    }

    #mainAuthor {
        display: inline;
        position: relative;
        top: 0px;
        right: 80px;
    }

    #mainPrice {
        display: inline;
        position: relative;
        top: 50px;
        right: 135px;
    }

    #mainTitle {
        display: inline;
    }

    #addToBasket {
        display: inline;
        left: 100px;
        bottom: 50px;
        background-color: #2a4d69;
        position: relative;
        top: 115px;
        left: -170px;

        color: white;
        border: none;
        border-radius: 15px;
        padding: 10px;

    }

    #similarProductsContainer{

        width: 100%;
        display: block;
        background-color: rgb(216, 216, 216);
        padding-top: 25px;
        padding-bottom: 25px;
        
    }

    #detailedInfoContainer > p{
        margin-bottom: 25px;
    }

    .similarProductItem{

        background-color: white;
    
        width: 30%;
        margin-left: 2%;

        padding-bottom: 25px;


        display: inline-block;
        border-radius: 25px;
    }

    .similarProductImg{
        border-radius: 5px;
        margin-top: 20px;
        margin-left: 5%;
        width: 90%;
    }

    .similarProductTitle{
        margin-top: 25px;
        margin-left: 25px;
        margin-bottom: 0;
        font-weight: bold;
    }

    .similarProductDesc{
        margin-top: 5px;
        margin-left: 25px;
        margin-bottom: 0;
    }
    .similarProductPrice{
        margin-top: 5px;
        margin-left: 25px;
        margin-bottom: 0;
        font-size: large;
    }


    #similarProductsHeader{
        margin-top: 50px;
        margin-bottom: 50px;
        text-align: center;
        display: block;
        width: 100%;
    }

</style>

</body>
</html>
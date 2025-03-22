      
        :root {
            --bg-color: #0d1b2a;
            --text-color: #e0e1dd;
            --secondary-text: #778da9;
            --card-bg: #1b263b;
            --icon-bg: #415a77;
            --border-color: #415a77;
            --shadow: rgba(0, 0, 0, 0.3);
        }

        
        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color, #e0e1dd); 
            color: var(--text-color, #1b263b); 
            font-family: 'Poppins', Arial, sans-serif;
            text-align: center;
            line-height: 1.6;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        h1, h2, h3 {
            font-weight: bold;
        }

    
        .title {
            font-size: 50px;
            font-weight: bold;
            margin-top: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

       
        .top-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

      
        #intro-text {
            font-size: 18px;
            font-weight: 500;
            max-width: 900px;
            margin: 20px auto;
            line-height: 1.8;
        }

        
        .details-section {
            padding: 40px 20px;
            margin: 40px auto;
            max-width: 1200px;
        }

      
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

     
        .details-grid div {
            background: var(--card-bg, #ffffff);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px var(--shadow, rgba(0, 0, 0, 0.1)); 
            text-align: center;
        }

       
        .details-grid div ul {
            padding-left: 20px;
            text-align: left;
        }

        .details-grid div ul li {
            margin-bottom: 10px;
        }

        
        .footer {
            text-align: center;
            background-color:#0056b3;
            color: white;
            padding: 20px 0;
            font-size: 16px;
        }

       
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

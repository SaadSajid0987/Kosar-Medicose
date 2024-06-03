<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tesseract.js/4.1.1/tesseract.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCR</title>
    <style>
        body {
            padding: 0 100px; /* Corrected padding shorthand */
            font-family: sans-serif;
        }

        .container {
            margin-top: 50px;
        }

        button {
            padding: 15px 45px;
            text-align: center;
            font-size: 14px;
            color: #000000;
            border: 1px solid;
            background-color: #fff;
            border-radius: 10px;
            cursor: pointer; /* Added pointer cursor */
        }

        button:hover {
            background: linear-gradient(120deg, #fab2ff 40%, #663399);
            color: #fff;
        }

        .upper div {
            display: inline;
            margin-left: 100px;
            white-space: pre;
        }

        .bottom {
            margin-top: 30px;
            display: flex;
        }

        .bottom div {
            flex: 1;
            border: 1px solid rgba(118, 118, 118);
            margin: 10px;
        }

        .bottom div img {
            max-width: 100%; /* Simplified max-width */
            max-height: 100%; /* Simplified max-height */
            margin: 10px;
            box-sizing: border-box; /* Added box-sizing */
        }

        .bottom div textarea {
            resize: none;
            width: 100%; /* Simplified width */
            height: calc(100% - 21px);
            padding: 10px;
            font-size: 20px;
            outline: none;
            border: none;
            box-sizing: border-box; /* Added box-sizing */
        }

        .bottom div:first-child {
            margin-left: 0;
            display: flex; /* Removed redundant -webkit-box-align properties */
            align-items: center; /* Center align items vertically */
        }

        .bottom div:last-child {
            margin-right: 0;
        }
    </style>
    

</head>

<body>
    <div class="container">
        <div class="upper">
            <input type="file" id="fileInput">
            <button>START</button>
            <div class="progress"></div>
        </div>
        <div class="bottom">
            <div>
                <img src="" alt="Uploaded Image">
            </div>
            <div>
                <textarea placeholder="text"></textarea>
            </div>
        </div>
    </div>
    <script src="js/prescription.js"></script>
</body>

</html>

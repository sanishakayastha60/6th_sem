<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Registration Form</title>
<style>
body {
font-family: Arial, sans-serif;
background-color: #f4f6f9;
display: flex;
justify-content: center;
align-items: center;
height: 100vh;
margin: 0;
}
form {
background-color: #fff;
padding: 30px 40px;
border-radius: 8px;
box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
width: 100%;
max-width: 400px;

}
form div {
margin-bottom: 15px;
}
label {
display: block;
font-weight: bold;
margin-bottom: 6px;
}
input[type="text"],
input[type="email"],
input[type="password"] {
width: 100%;
padding: 10px;
border: 1px solid #ccc;
border-radius: 5px;
box-sizing: border-box;
}
button {
width: 100%;
background-color: #007bff;
color: white;
padding: 10px;
border: none;
border-radius: 5px;
font-size: 16px;
cursor: pointer;
}

button:hover {
background-color: #0056b3;
}
</style>
</head>
<body>
<form action="display.php" method="POST">
<div>
<label for="fullname">Full Name</label>
<input type="text" name="fullname" id="fullname" required>
</div>
<div>
<label for="email">Email</label>
<input type="email" name="email" id="email" required>
</div>
<div>
<label for="password">Password</label>
<input type="password" name="password" id="password" required>
</div>
<div>
<button type="submit">Submit</button>
</div>
</form>
</body>
</html>